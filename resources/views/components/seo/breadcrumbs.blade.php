@props(['seo' => null, 'items' => []])

@php
    $breadcrumbs = $seo?->breadcrumbs ?? $items;
    // Ensure Home is always first
    if (!empty($breadcrumbs) && ($breadcrumbs[0]['name'] ?? '') !== 'Home') {
        array_unshift($breadcrumbs, ['name' => 'Home', 'url' => config('app.url')]);
    }
@endphp

@if(!empty($breadcrumbs))
    <nav aria-label="Breadcrumb" class="mb-4">
        <ol class="flex flex-wrap items-center gap-1 text-sm text-gray-400">
            @foreach($breadcrumbs as $index => $crumb)
                <li class="flex items-center">
                    @if($index > 0)
                        <svg class="w-4 h-4 mx-1 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @endif

                    @if(isset($crumb['url']) && $index < count($breadcrumbs) - 1)
                        <a href="{{ $crumb['url'] }}" class="hover:text-gray-200 transition-colors">
                            {{ $crumb['name'] }}
                        </a>
                    @else
                        <span class="text-gray-300 font-medium">{{ $crumb['name'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
