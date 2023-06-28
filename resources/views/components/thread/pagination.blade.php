<div class="flex space-x-1 md:ml-2 order-2">
    @if ($thread->replies_count > 25)
        @php
            $pages = intval($thread->replies_count / 25) + 1
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
