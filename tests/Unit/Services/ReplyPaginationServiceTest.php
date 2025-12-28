<?php

namespace Tests\Unit\Services;

use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use App\Services\ReplyPaginationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReplyPaginationServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReplyPaginationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ReplyPaginationService();
    }

    /** @test */
    public function it_calculates_position_for_first_reply(): void
    {
        $thread = Thread::factory()->create();
        $reply = Reply::factory()->create(['thread_id' => $thread->id]);

        $position = $this->service->getReplyPosition($reply);

        $this->assertEquals(1, $position);
    }

    /** @test */
    public function it_calculates_position_based_on_id_order(): void
    {
        $thread = Thread::factory()->create();
        $reply1 = Reply::factory()->create(['thread_id' => $thread->id]);
        $reply2 = Reply::factory()->create(['thread_id' => $thread->id]);
        $reply3 = Reply::factory()->create(['thread_id' => $thread->id]);

        $this->assertEquals(1, $this->service->getReplyPosition($reply1));
        $this->assertEquals(2, $this->service->getReplyPosition($reply2));
        $this->assertEquals(3, $this->service->getReplyPosition($reply3));
    }

    /** @test */
    public function it_excludes_soft_deleted_replies_from_position(): void
    {
        $thread = Thread::factory()->create();
        $reply1 = Reply::factory()->create(['thread_id' => $thread->id]);
        $reply2 = Reply::factory()->create(['thread_id' => $thread->id]);
        $reply3 = Reply::factory()->create(['thread_id' => $thread->id]);

        // Soft delete the second reply
        $reply2->delete();

        // Reply 3 should now be at position 2 (not 3)
        $this->assertEquals(1, $this->service->getReplyPosition($reply1));
        $this->assertEquals(2, $this->service->getReplyPosition($reply3));
    }

    /** @test */
    public function it_only_counts_replies_in_same_thread(): void
    {
        $thread1 = Thread::factory()->create();
        $thread2 = Thread::factory()->create();

        // Create 5 replies in thread 1
        for ($i = 0; $i < 5; $i++) {
            Reply::factory()->create(['thread_id' => $thread1->id]);
        }

        // Create 1 reply in thread 2
        $replyInThread2 = Reply::factory()->create(['thread_id' => $thread2->id]);

        // The reply in thread 2 should be at position 1, not 6
        $this->assertEquals(1, $this->service->getReplyPosition($replyInThread2));
    }

    /** @test */
    public function it_calculates_page_one_for_first_replies(): void
    {
        $thread = Thread::factory()->create();
        $reply = Reply::factory()->create(['thread_id' => $thread->id]);

        $page = $this->service->getPageForReply($reply, 20);

        $this->assertEquals(1, $page);
    }

    /** @test */
    public function it_calculates_page_two_when_position_exceeds_per_page(): void
    {
        $thread = Thread::factory()->create();

        // Create 25 replies
        for ($i = 0; $i < 25; $i++) {
            $reply = Reply::factory()->create(['thread_id' => $thread->id]);
        }

        // The 21st reply should be on page 2 with 20 per page
        $reply21 = Reply::where('thread_id', $thread->id)->skip(20)->first();
        $page = $this->service->getPageForReply($reply21, 20);

        $this->assertEquals(2, $page);
    }

    /** @test */
    public function it_calculates_correct_page_with_soft_deleted_replies(): void
    {
        $thread = Thread::factory()->create();

        // Create 25 replies
        $replies = [];
        for ($i = 0; $i < 25; $i++) {
            $replies[] = Reply::factory()->create(['thread_id' => $thread->id]);
        }

        // Delete 5 replies from the first 20
        for ($i = 0; $i < 5; $i++) {
            $replies[$i]->delete();
        }

        // The 21st reply (index 20) should now be on page 1 since only 15 non-deleted replies come before it
        $page = $this->service->getPageForReply($replies[20], 20);
        $this->assertEquals(1, $page);

        // The 25th reply (index 24) should be on page 1 too (position 20)
        $page = $this->service->getPageForReply($replies[24], 20);
        $this->assertEquals(1, $page);
    }

    /** @test */
    public function it_calculates_page_for_reply_at_boundary(): void
    {
        $thread = Thread::factory()->create();

        // Create exactly 20 replies
        for ($i = 0; $i < 20; $i++) {
            $reply = Reply::factory()->create(['thread_id' => $thread->id]);
        }

        // The 20th reply should be on page 1
        $reply20 = Reply::where('thread_id', $thread->id)->orderBy('id')->skip(19)->first();
        $page = $this->service->getPageForReply($reply20, 20);

        $this->assertEquals(1, $page);

        // Add one more reply - it should be on page 2
        $reply21 = Reply::factory()->create(['thread_id' => $thread->id]);
        $page = $this->service->getPageForReply($reply21, 20);

        $this->assertEquals(2, $page);
    }
}