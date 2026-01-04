<?php

use App\Models\User;
use App\Modules\Pickem\Models\Pickem;
use App\Modules\Pickem\Models\PickemComment;
use App\Modules\Pickem\Models\PickemGroup;
use App\Modules\Pickem\Models\PickemMatchup;
use App\Modules\Pickem\Models\PickemPick;
use App\Modules\Pickem\Services\PickemScoringService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->admin = User::factory()->create(['is_admin' => true]);
});

describe('model relationships', function () {
    it('creates a pickem group with pickems', function () {
        $group = PickemGroup::factory()->create();
        $pickem = Pickem::factory()->for($group, 'group')->create();

        expect($pickem->group->id)->toBe($group->id);
        expect($group->pickems)->toHaveCount(1);
    });

    it('creates a pickem with matchups', function () {
        $pickem = Pickem::factory()->create();
        $matchups = PickemMatchup::factory()->count(3)->for($pickem)->create();

        expect($pickem->matchups)->toHaveCount(3);
        expect($matchups->first()->pickem->id)->toBe($pickem->id);
    });

    it('creates a matchup with picks', function () {
        $matchup = PickemMatchup::factory()->create();
        $pick = PickemPick::factory()->for($matchup, 'matchup')->for($this->user)->create();

        expect($matchup->picks)->toHaveCount(1);
        expect($pick->matchup->id)->toBe($matchup->id);
        expect($pick->user->id)->toBe($this->user->id);
    });

    it('creates a pickem with comments', function () {
        $pickem = Pickem::factory()->create();
        $comment = PickemComment::factory()->for($pickem)->for($this->user, 'owner')->create();

        expect($pickem->comments)->toHaveCount(1);
        expect($comment->pickem->id)->toBe($pickem->id);
        expect($comment->owner->id)->toBe($this->user->id);
    });

    it('cascades deletes from pickem to matchups and comments', function () {
        $pickem = Pickem::factory()->create();
        $matchup = PickemMatchup::factory()->for($pickem)->create();
        $comment = PickemComment::factory()->for($pickem)->create();

        $pickem->delete();

        expect(PickemMatchup::find($matchup->id))->toBeNull();
        expect(PickemComment::withTrashed()->find($comment->id)->deleted_at)->not->toBeNull();
    });

    it('cascades deletes from matchup to picks', function () {
        $matchup = PickemMatchup::factory()->create();
        $pick = PickemPick::factory()->for($matchup, 'matchup')->create();

        $matchup->delete();

        expect(PickemPick::find($pick->id))->toBeNull();
    });
});

describe('pickem locking', function () {
    it('is locked when picks_lock_at is in the past', function () {
        $pickem = Pickem::factory()->create(['picks_lock_at' => now()->subHour()]);

        expect($pickem->isLocked())->toBeTrue();
        expect($pickem->isActive())->toBeFalse();
    });

    it('is not locked when picks_lock_at is in the future', function () {
        $pickem = Pickem::factory()->create(['picks_lock_at' => now()->addHour()]);

        expect($pickem->isLocked())->toBeFalse();
        expect($pickem->isActive())->toBeTrue();
    });

    it('is not locked when picks_lock_at is null', function () {
        $pickem = Pickem::factory()->create(['picks_lock_at' => null]);

        expect($pickem->isLocked())->toBeFalse();
        expect($pickem->isActive())->toBeTrue();
    });
});

describe('scoring - simple mode', function () {
    it('gives 1 point per correct pick', function () {
        $pickem = Pickem::factory()->simple()->create();
        $matchup1 = PickemMatchup::factory()->for($pickem)->withWinner('a')->create();
        $matchup2 = PickemMatchup::factory()->for($pickem)->withWinner('b')->create();
        $matchup3 = PickemMatchup::factory()->for($pickem)->withWinner('a')->create();

        // User picks: a (correct), a (wrong), a (correct)
        PickemPick::factory()->for($matchup1, 'matchup')->for($this->user)->pickA()->create();
        PickemPick::factory()->for($matchup2, 'matchup')->for($this->user)->pickA()->create();
        PickemPick::factory()->for($matchup3, 'matchup')->for($this->user)->pickA()->create();

        $score = $pickem->fresh()->getUserScore($this->user);

        expect($score)->toBe(2);
    });

    it('returns max possible score for simple mode', function () {
        $pickem = Pickem::factory()->simple()->create();
        PickemMatchup::factory()->count(5)->for($pickem)->create();

        expect($pickem->fresh()->getMaxPossibleScore())->toBe(5);
    });
});

describe('scoring - weighted mode', function () {
    it('gives points based on matchup point value', function () {
        $pickem = Pickem::factory()->weighted()->create();
        $matchup1 = PickemMatchup::factory()->for($pickem)->withWinner('a')->withPoints(3)->create();
        $matchup2 = PickemMatchup::factory()->for($pickem)->withWinner('b')->withPoints(2)->create();
        $matchup3 = PickemMatchup::factory()->for($pickem)->withWinner('a')->withPoints(5)->create();

        // User picks: a (correct, 3pts), a (wrong, 0pts), a (correct, 5pts)
        PickemPick::factory()->for($matchup1, 'matchup')->for($this->user)->pickA()->create();
        PickemPick::factory()->for($matchup2, 'matchup')->for($this->user)->pickA()->create();
        PickemPick::factory()->for($matchup3, 'matchup')->for($this->user)->pickA()->create();

        $score = $pickem->fresh()->getUserScore($this->user);

        expect($score)->toBe(8); // 3 + 0 + 5
    });

    it('returns max possible score for weighted mode', function () {
        $pickem = Pickem::factory()->weighted()->create();
        PickemMatchup::factory()->for($pickem)->withPoints(3)->create();
        PickemMatchup::factory()->for($pickem)->withPoints(5)->create();
        PickemMatchup::factory()->for($pickem)->withPoints(2)->create();

        expect($pickem->fresh()->getMaxPossibleScore())->toBe(10);
    });
});

describe('scoring - confidence mode', function () {
    it('gives points based on user-assigned confidence', function () {
        $pickem = Pickem::factory()->confidence()->create();
        $matchup1 = PickemMatchup::factory()->for($pickem)->withWinner('a')->create();
        $matchup2 = PickemMatchup::factory()->for($pickem)->withWinner('b')->create();
        $matchup3 = PickemMatchup::factory()->for($pickem)->withWinner('a')->create();

        // User picks: a with 3 conf (correct), a with 2 conf (wrong), a with 1 conf (correct)
        PickemPick::factory()->for($matchup1, 'matchup')->for($this->user)->pickA()->withConfidence(3)->create();
        PickemPick::factory()->for($matchup2, 'matchup')->for($this->user)->pickA()->withConfidence(2)->create();
        PickemPick::factory()->for($matchup3, 'matchup')->for($this->user)->pickA()->withConfidence(1)->create();

        $score = $pickem->fresh()->getUserScore($this->user);

        expect($score)->toBe(4); // 3 + 0 + 1
    });

    it('returns max possible score for confidence mode (sum of 1 to N)', function () {
        $pickem = Pickem::factory()->confidence()->create();
        PickemMatchup::factory()->count(5)->for($pickem)->create();

        // Max score = 1 + 2 + 3 + 4 + 5 = 15
        expect($pickem->fresh()->getMaxPossibleScore())->toBe(15);
    });
});

describe('scoring - push handling', function () {
    it('treats push as correct pick in simple mode', function () {
        $pickem = Pickem::factory()->simple()->create();
        $matchup = PickemMatchup::factory()->for($pickem)->push()->create();

        PickemPick::factory()->for($matchup, 'matchup')->for($this->user)->pickA()->create();

        $score = $pickem->fresh()->getUserScore($this->user);

        expect($score)->toBe(1);
    });
});

describe('leaderboard - single pickem', function () {
    it('ranks users by score descending', function () {
        $pickem = Pickem::factory()->simple()->create();
        $matchup1 = PickemMatchup::factory()->for($pickem)->withWinner('a')->create();
        $matchup2 = PickemMatchup::factory()->for($pickem)->withWinner('a')->create();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // User1: 2 correct picks
        PickemPick::factory()->for($matchup1, 'matchup')->for($user1)->pickA()->create();
        PickemPick::factory()->for($matchup2, 'matchup')->for($user1)->pickA()->create();

        // User2: 1 correct pick
        PickemPick::factory()->for($matchup1, 'matchup')->for($user2)->pickA()->create();
        PickemPick::factory()->for($matchup2, 'matchup')->for($user2)->pickB()->create();

        // User3: 0 correct picks
        PickemPick::factory()->for($matchup1, 'matchup')->for($user3)->pickB()->create();
        PickemPick::factory()->for($matchup2, 'matchup')->for($user3)->pickB()->create();

        $pickem = $pickem->fresh();
        $leaderboard = collect([
            ['user' => $user1, 'score' => $pickem->getUserScore($user1)],
            ['user' => $user2, 'score' => $pickem->getUserScore($user2)],
            ['user' => $user3, 'score' => $pickem->getUserScore($user3)],
        ])->sortByDesc('score')->values();

        expect($leaderboard[0]['user']->id)->toBe($user1->id);
        expect($leaderboard[0]['score'])->toBe(2);
        expect($leaderboard[1]['user']->id)->toBe($user2->id);
        expect($leaderboard[1]['score'])->toBe(1);
        expect($leaderboard[2]['user']->id)->toBe($user3->id);
        expect($leaderboard[2]['score'])->toBe(0);
    });
});

describe('leaderboard - group cumulative', function () {
    it('sums scores across all pickems in a group', function () {
        $group = PickemGroup::factory()->create();

        $pickem1 = Pickem::factory()->simple()->for($group, 'group')->create();
        $matchup1 = PickemMatchup::factory()->for($pickem1)->withWinner('a')->create();

        $pickem2 = Pickem::factory()->simple()->for($group, 'group')->create();
        $matchup2 = PickemMatchup::factory()->for($pickem2)->withWinner('a')->create();

        // User gets 1 point in each pickem
        PickemPick::factory()->for($matchup1, 'matchup')->for($this->user)->pickA()->create();
        PickemPick::factory()->for($matchup2, 'matchup')->for($this->user)->pickA()->create();

        $leaderboard = $group->getLeaderboard();

        expect($leaderboard)->toHaveCount(1);
        expect((int) $leaderboard->first()->total_points)->toBe(2);
    });
});

describe('pick constraints', function () {
    it('enforces one pick per user per matchup', function () {
        $matchup = PickemMatchup::factory()->create();

        PickemPick::factory()->for($matchup, 'matchup')->for($this->user)->pickA()->create();

        expect(fn () => PickemPick::factory()->for($matchup, 'matchup')->for($this->user)->pickB()->create())
            ->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('allows different users to pick on same matchup', function () {
        $matchup = PickemMatchup::factory()->create();
        $user2 = User::factory()->create();

        PickemPick::factory()->for($matchup, 'matchup')->for($this->user)->pickA()->create();
        PickemPick::factory()->for($matchup, 'matchup')->for($user2)->pickB()->create();

        expect($matchup->picks()->count())->toBe(2);
    });
});

describe('pick correctness', function () {
    it('returns true when pick matches winner', function () {
        $matchup = PickemMatchup::factory()->withWinner('a')->create();
        $pick = PickemPick::factory()->for($matchup, 'matchup')->pickA()->create();

        expect($pick->isCorrect())->toBeTrue();
    });

    it('returns false when pick does not match winner', function () {
        $matchup = PickemMatchup::factory()->withWinner('a')->create();
        $pick = PickemPick::factory()->for($matchup, 'matchup')->pickB()->create();

        expect($pick->isCorrect())->toBeFalse();
    });

    it('returns null when winner is not set', function () {
        $matchup = PickemMatchup::factory()->create(['winner' => null]);
        $pick = PickemPick::factory()->for($matchup, 'matchup')->create();

        expect($pick->isCorrect())->toBeNull();
    });

    it('returns true for any pick when result is push', function () {
        $matchup = PickemMatchup::factory()->push()->create();
        $pickA = PickemPick::factory()->for($matchup, 'matchup')->pickA()->create();

        expect($pickA->isCorrect())->toBeTrue();
    });
});

describe('comments', function () {
    it('can create a comment on a pickem', function () {
        $pickem = Pickem::factory()->create();

        $comment = PickemComment::factory()->for($pickem)->for($this->user, 'owner')->create([
            'body' => 'Great picks everyone!',
        ]);

        expect($comment->body)->toBe('Great picks everyone!');
        expect($comment->owner->id)->toBe($this->user->id);
        expect($comment->pickem->id)->toBe($pickem->id);
    });

    it('soft deletes comments', function () {
        $comment = PickemComment::factory()->create();
        $id = $comment->id;

        $comment->delete();

        expect(PickemComment::find($id))->toBeNull();
        expect(PickemComment::withTrashed()->find($id))->not->toBeNull();
    });
});

describe('matchup winner label', function () {
    it('returns option_a name when winner is a', function () {
        $matchup = PickemMatchup::factory()->create([
            'option_a' => 'Bengals',
            'option_b' => 'Browns',
            'winner' => 'a',
        ]);

        expect($matchup->getWinnerLabel())->toBe('Bengals');
    });

    it('returns option_b name when winner is b', function () {
        $matchup = PickemMatchup::factory()->create([
            'option_a' => 'Bengals',
            'option_b' => 'Browns',
            'winner' => 'b',
        ]);

        expect($matchup->getWinnerLabel())->toBe('Browns');
    });

    it('returns Push label when winner is push', function () {
        $matchup = PickemMatchup::factory()->push()->create();

        expect($matchup->getWinnerLabel())->toBe('Push (Tie)');
    });

    it('returns null when winner is not set', function () {
        $matchup = PickemMatchup::factory()->create(['winner' => null]);

        expect($matchup->getWinnerLabel())->toBeNull();
    });
});
