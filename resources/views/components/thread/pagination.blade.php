<div class="flex space-x-1 md:ml-2 order-2">
    @php
        $repliesPerPage = auth()->check() ? auth()->user()->repliesPerPage() : config('forum.replies_per_page')
    @endphp
    @if ($thread->replies_count > $repliesPerPage)
        @php
            $pages = intval($thread->replies_count / $repliesPerPage) + 1
        @endphp
        @if ($pages > 1)
            @for ($i = 1; $i <= $pages; $i++)
                @if ($i <= 2 || $i == $pages)
                    @if ($i == $pages && $pages > 3)
                        <span class="" style="">...</span>
                    @endif
                    <a
                        href="{{ $thread->path() }}?page={{ $i }}"
                        class="flex items-center justify-items bg-gray-800 p-2 px-3 md:p-1 md:px-2 rounded text-gray-200 hover:shadow-lg leading-none"
                    >
                        {{ $i }}
                    </a>
                @endif
            @endfor
        @endif
    @endif
    <a
        href="{{ $thread->path() }}/?newestpost=true"
        class="flex items-center justify-items bg-gray-800 p-2 px-3 md:p-1 md:px-2 rounded text-gray-200 hover:shadow-lg leading-none"
    >
        Latest Post &raquo;
    </a>
</div>
