@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}">
        <div class="flex justify-center items-center flex-1 sm:hidden @if(isset($top) && $top) mb-8 @else mt-8 @endif">
            <ul class="flex text-2xl md:text-sm">
                <li class="mx-1 text-gray-600 rounded">
                    <a class="p-2 {{ ($paginator->currentPage() == 1) ? ' disabled' : 'hover:bg-blue-600 hover:text-white' }}" href="{{ $paginator->url(1) }}">&laquo;</a>
                </li>
                @for ($i = 1; $i <= $paginator->lastPage(); $i++)
                    @php
                        $link_limit = 7;
                        $half_total_links = floor($link_limit / 2);
                        $from = $paginator->currentPage() - $half_total_links;
                        $to = $paginator->currentPage() + $half_total_links;
                        if ($paginator->currentPage() < $half_total_links) {
                            $to += $half_total_links - $paginator->currentPage();
                        }
                        if ($paginator->lastPage() - $paginator->currentPage() < $half_total_links) {
                            $from -= $half_total_links - ($paginator->lastPage() - $paginator->currentPage()) - 1;
                        }
                    @endphp
                    @if ($from < $i && $i < $to)
                        <li class="mx-1 text-gray-600">
                            <a class="p-2  {{ ($paginator->currentPage() == $i) ? ' text-blue-600 font-bold border-b-2 border-blue-600' : 'hover:bg-blue-600 hover:text-white rounded' }}" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                        </li>
                    @endif
                @endfor
                <li class="mx-1 text-gray-600 rounded">
                    <a class="p-2 {{ ($paginator->currentPage() == $paginator->lastPage()) ? ' disabled' : 'hover:bg-blue-600 hover:text-white' }}" href="{{ $paginator->url($paginator->lastPage()) }}">&raquo;</a>
                </li>
            </ul>
        </div>

        <div class="hidden sm:flex justify-center items-center flex-1">
            <ul class="flex items-center mb-4 text-gray-300">
                {{-- Pagination Elements --}}

                <li class="mx-1">
                    <a class="p-2 rounded {{ ($paginator->currentPage() == 1) ? ' disabled' : 'hover:bg-blue-600 hover:text-white' }}" href="{{ $paginator->url(1) }}">&laquo;</a>
                </li>
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                <ul>
                    @if (is_string($element))
                        <span aria-disabled="true" class="relative inline-flex items-center px-4 py-2 -ml-px  text-gray-100 cursor-default leading-5">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="p-2 px-3 text-blue-600 font-bold border-b-2 border-blue-600">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="hover:bg-blue-600 hover:text-white rounded p-2 px-3 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif

                </ul>
                @endforeach
                <li class="mx-1">
                    <a class="p-2 rounded {{ ($paginator->currentPage() == $paginator->lastPage()) ? ' disabled' : 'hover:bg-blue-600 hover:text-white' }}" href="{{ $paginator->url($paginator->lastPage()) }}">&raquo;</a>
                </li>
            </ul>
        </div>

        <div class="hidden @if (isset($top) && !$top) sm:flex-1 sm:flex sm:flex-col sm:items-center sm:justify-between sm:space-y-4 @endif">
            <div>
                <p class="text-sm text-gray-200 leading-5">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-medium">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-medium">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>
        </div>
    </nav>
@endif
