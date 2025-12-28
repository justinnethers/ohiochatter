<div class="flex gap-2 md:ml-2 order-2">
    @php
        $repliesPerPage = auth()->check() ? auth()->user()->repliesPerPage() : config('forum.replies_per_page');
        $pages = $thread->replies_count > 0 ? ceil($thread->replies_count / $repliesPerPage) : 1;
    @endphp
    @if ($pages > 1)
        <a
            href="{{ $thread->path() }}"
            class="flex items-center justify-center bg-steel-800 border border-steel-700/50 py-1.5 rounded-lg text-steel-300 hover:bg-steel-700 hover:text-white hover:border-steel-600 leading-none transition-all duration-200 w-8"
        >
            1
        </a>
    @endif
    <a
        href="{{ $thread->path() }}/?newestpost=true"
        class="flex items-center justify-center bg-accent-500/15 border border-steel-700/50 py-1.5 px-3 rounded-lg text-accent-400 hover:bg-accent-500/25 hover:text-accent-300 leading-none transition-all duration-200"
    >
        Latest &raquo;
    </a>
</div>
