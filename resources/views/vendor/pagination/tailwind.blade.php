@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="py-2">
        {{-- Mobile pagination --}}
        <div class="flex justify-center items-center flex-1 sm:hidden @if(isset($top) && $top) mb-6 @else mt-6 @endif">
            <div class="flex items-center gap-1.5 text-lg">
                <a class="flex items-center justify-center w-10 h-10 rounded-lg transition-all duration-200 {{ ($paginator->currentPage() == 1) ? 'text-steel-600 cursor-not-allowed' : 'text-steel-300 bg-steel-800 border border-steel-700/50 hover:bg-steel-700 hover:text-white hover:border-steel-600' }}" href="{{ $paginator->url(1) }}">&laquo;</a>
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
                        <a class="flex items-center justify-center w-10 h-10 rounded-lg transition-all duration-200 {{ ($paginator->currentPage() == $i) ? 'bg-accent-500/15 text-accent-400 font-bold border border-steel-700/50' : 'text-steel-300 bg-steel-800 border border-steel-700/50 hover:bg-steel-700 hover:text-white hover:border-steel-600' }}" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                    @endif
                @endfor
                <a class="flex items-center justify-center w-10 h-10 rounded-lg transition-all duration-200 {{ ($paginator->currentPage() == $paginator->lastPage()) ? 'text-steel-600 cursor-not-allowed' : 'text-steel-300 bg-steel-800 border border-steel-700/50 hover:bg-steel-700 hover:text-white hover:border-steel-600' }}" href="{{ $paginator->url($paginator->lastPage()) }}">&raquo;</a>
            </div>
        </div>

        {{-- Desktop pagination --}}
        <div class="hidden sm:flex justify-center items-center flex-1 @if(isset($top) && $top) mb-4 @else mt-4 @endif">
            <div class="flex items-center gap-1">
                <a class="flex items-center justify-center w-8 h-8 rounded-lg text-sm transition-all duration-200 {{ ($paginator->currentPage() == 1) ? 'text-steel-600 cursor-not-allowed' : 'text-steel-300 bg-steel-800 border border-steel-700/50 hover:bg-steel-700 hover:text-white hover:border-steel-600' }}" href="{{ $paginator->url(1) }}">&laquo;</a>
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-2 text-steel-600">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="flex items-center justify-center w-8 h-8 rounded-lg bg-accent-500/15 text-accent-400 font-semibold border border-steel-700/50">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="flex items-center justify-center w-8 h-8 rounded-lg text-steel-300 bg-steel-800 border border-steel-700/50 hover:bg-steel-700 hover:text-white hover:border-steel-600 transition-all duration-200" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
                <a class="flex items-center justify-center w-8 h-8 rounded-lg text-sm transition-all duration-200 {{ ($paginator->currentPage() == $paginator->lastPage()) ? 'text-steel-600 cursor-not-allowed' : 'text-steel-300 bg-steel-800 border border-steel-700/50 hover:bg-steel-700 hover:text-white hover:border-steel-600' }}" href="{{ $paginator->url($paginator->lastPage()) }}">&raquo;</a>
            </div>
        </div>

        <div class="hidden @if (isset($top) && !$top) sm:flex-1 sm:flex sm:flex-col sm:items-center sm:justify-between sm:space-y-4 @endif">
            <div>
                <p class="text-sm text-steel-500 leading-5">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="text-steel-300">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="text-steel-300">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span class="text-steel-300">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>
        </div>
    </nav>
@endif
