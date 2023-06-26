<div class="flex md:ml-2 order-2 w-full mt-4 md:mt-0 md:w-auto">
    @if ($thread->replies_count > 25)
        <div class="flex">
            @php
                $pages = intval($thread->replies_count / 25) + 1
            @endphp
            @if ($pages > 1)
                @for ($i = 1; $i <= $pages; $i++)
                    @if ($i <= 2 || $i == $pages)
                        @if ($i == $pages && $pages > 3)
                            <span class="secondary-text mr-1" style="font-size: 1.5em;line-height: 1; margin-top: 5px">...</span>
                        @endif
                        <a href="{{ $thread->path() }}?page={{ $i }}" class="bg-gray-800 text-gray-200 text-lg md:text-base p-2 px-4 md:p-1 md:px-2 mr-1 rounded hover:shadow hover:bg-gray-700 hover:text-white">{{ $i }}</a>
                    @endif
                @endfor
            @endif
        </div>
    @endif
    <a href="{{ $thread->path() }}/?newestpost=true" class="bg-gray-200 text-blue-700 p-2 px-4 md:p-1 md:px-2 text-lg md:text-sm rounded hover:shadow hover:bg-gray-700 hover:text-white">Latest Post &raquo;</a>
</div>
