<div class="flex space-x-1 md:ml-2 order-2 font-headline">
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
                        class="bg-gray-800 p-1 px-3 rounded text-gray-200 text-lg leading-none flex items-center md:text-sm hover:shadow"
                    >
                        {{ $i }}
                    </a>
                @endif
            @endfor
        @endif
    @endif
    <a
        href="{{ $thread->path() }}/?newestpost=true"
        class="bg-gray-800 p-1 px-3 rounded text-gray-200 text-lg leading-none flex items-center md:text-sm hover:shadow"
    >
        Latest Post &raquo;
    </a>
</div>
