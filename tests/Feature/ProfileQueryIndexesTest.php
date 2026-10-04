<?php

use App\Models\Forum;
use App\Models\Neg;
use App\Models\Rep;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use App\Services\ReplyPaginationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function seedRepliesForExplain(): User
{
    $users = User::factory()->count(3)->create();
    $forum = Forum::factory()->create();
    $threads = Thread::factory()
        ->count(8)
        ->create(['user_id' => $users->first()->id, 'forum_id' => $forum->id]);

    $now = now();
    $rows = [];

    for ($i = 0; $i < 300; $i++) {
        $rows[] = [
            'thread_id' => $threads[$i % $threads->count()]->id,
            'user_id' => $users[$i % $users->count()]->id,
            'body' => "Seeded reply body {$i}",
            'created_at' => $now->copy()->subMinutes(300 - $i),
            'updated_at' => $now->copy()->subMinutes(300 - $i),
        ];
    }

    foreach (array_chunk($rows, 100) as $chunk) {
        Reply::query()->insert($chunk);
    }

    return $users->first();
}

function explainQuery($query): array
{
    return DB::select('EXPLAIN FORMAT=TRADITIONAL '.$query->toSql(), $query->getBindings());
}

function possibleKeysFor(array $plan, string $table): string
{
    $row = collect($plan)->first(fn ($r) => $r->table === $table);

    expect($row)->not->toBeNull("EXPLAIN had no row for table {$table}");

    return (string) $row->possible_keys;
}

it('has the index on the schema', function (string $table, string $index) {
    expect(Schema::hasIndex($table, $index))->toBeTrue();
})->with([
    'replies by user, deleted_at, created_at' => ['replies', 'replies_user_id_deleted_at_created_at_index'],
    'replies by thread, deleted_at, id' => ['replies', 'replies_thread_id_deleted_at_id_index'],
    'threads by user, created_at' => ['threads', 'threads_user_id_created_at_index'],
    'reps by morph target' => ['reps', 'reps_repped_type_repped_id_index'],
    'negs by morph target' => ['negs', 'negs_negged_type_negged_id_index'],
]);

it('makes the profile recent replies query eligible for the new reply indexes', function () {
    $user = seedRepliesForExplain();

    $query = $user->replies()
        ->select('replies.*')
        ->selectSub(ReplyPaginationService::positionSubquery(), 'position')
        ->latest()
        ->take(10);

    $plan = explainQuery($query);

    expect(possibleKeysFor($plan, 'replies'))->toContain('replies_user_id_deleted_at_created_at_index');
    expect(possibleKeysFor($plan, 'r2'))->toContain('replies_thread_id_deleted_at_id_index');
})->skip(fn () => DB::getDriverName() !== 'mysql', 'MySQL EXPLAIN only');

it('makes the profile reps-on-replies count eligible for the morph target index', function () {
    $user = seedRepliesForExplain();

    $query = Rep::where('repped_type', Reply::class)
        ->whereIn('repped_id', $user->replies()->select('id'));

    $plan = explainQuery($query);

    expect(possibleKeysFor($plan, 'reps'))->toContain('reps_repped_type_repped_id_index');
})->skip(fn () => DB::getDriverName() !== 'mysql', 'MySQL EXPLAIN only');

it('shows reply positions and received rep and neg totals on the public profile page', function () {
    $user = User::factory()->create();
    $others = User::factory()->count(3)->create();
    $thread = Thread::factory()->create([
        'user_id' => $user->id,
        'title' => 'Distinctive Profile Thread Title',
    ]);

    $replies = Reply::factory()->count(3)->create([
        'thread_id' => $thread->id,
        'user_id' => $user->id,
    ]);

    Rep::query()->create([
        'user_id' => $others[0]->id,
        'repped_id' => $replies->first()->id,
        'repped_type' => Reply::class,
    ]);
    Neg::query()->create([
        'user_id' => $others[1]->id,
        'negged_id' => $replies->first()->id,
        'negged_type' => Reply::class,
    ]);
    Rep::query()->create([
        'user_id' => $others[2]->id,
        'repped_id' => $thread->id,
        'repped_type' => Thread::class,
    ]);

    $this->withoutVite()
        ->get(route('profile.show', $user->username))
        ->assertOk()
        ->assertSee('Distinctive Profile Thread Title')
        ->assertViewHas('totalReps', 2)
        ->assertViewHas('totalNegs', 1)
        ->assertViewHas('recentPosts', function ($posts) {
            return $posts->count() === 3
                && is_int((int) $posts->first()->position)
                && (int) $posts->first()->position > 0;
        });
});
