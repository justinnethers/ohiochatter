<div class="flex md:w-1/3 order-1 md:order-3 justify-end">
    <span class="flex items-center space-x-1 text-gray-400">
        <span class="font-bold text-2xl">{{ number_format($thread->replyCount()) }}</span>
        <span class="inline-block">{{ Str::plural('reply', $thread->replyCount()) }}</span>
    </span>
</div>
