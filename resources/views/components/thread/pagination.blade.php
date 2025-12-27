<div class="flex gap-2 md:ml-2 order-2">
    @php
        $repliesPerPage = auth()->check() ? auth()->user()->repliesPerPage() : config('forum.replies_per_page')
    @endphp
    @if ($thread->replies_count > $repliesPerPage)
        @php
            $pages = ceil($thread->replies_count / $repliesPerPage)
        @endphp
        @if ($pages > 1)
            @for ($i = 1; $i <= $pages; $i++)
                @if ($i <= 2 || $i == $pages)
                    @if ($i == $pages && $pages > 3)
                        <span class="text-steel-500">...</span>
                    @endif
                    <a
                        href="{{ $thread->path() }}?page={{ $i }}"
                        class="flex items-center justify-center bg-steel-800 border border-steel-700/50 p-2 px-3 md:p-1.5 md:px-2.5 rounded-lg text-steel-300 hover:bg-steel-700 hover:text-white hover:border-steel-600 leading-none transition-all duration-200"
                    >
                        {{ $i }}
                    </a>
                @endif
            @endfor
        @endif
    @endif
    <a
        href="{{ $thread->path() }}/?newestpost=true"
        class="flex items-center justify-center bg-accent-500/15 border border-steel-700/50 p-2 px-3 md:p-1.5 md:px-2.5 rounded-lg text-accent-400 hover:bg-accent-500/25 hover:text-accent-300 leading-none transition-all duration-200"
    >
        Latest &raquo;
    </a>
</div>
