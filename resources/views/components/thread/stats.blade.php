<div class="flex order-1 md:order-3 justify-end">
    <div class="inline-flex items-center gap-1 md:gap-2 px-2 md:px-3 py-0.5 md:py-1 bg-steel-900/70 rounded-full border border-steel-700/50">
        <svg class="w-5 h-5 text-steel-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zm-4 0H9v2h2V9z" clip-rule="evenodd"/>
        </svg>
        <span class="font-bold text-steel-100">{{ number_format($thread->replies_count) }}</span>
        <span class="hidden md:inline text-sm text-steel-400">{{ Str::plural('reply', $thread->replies_count) }}</span>
    </div>
</div>
