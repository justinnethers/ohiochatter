@php
    $previewLocation = $this->getPreviewLocation();
    $previewCategories = $this->getPreviewCategories();
    $categoryColors = [
        'Food & Drink' => ['bg' => 'bg-amber-500/20', 'text' => 'text-amber-400'],
        'Outdoors & Nature' => ['bg' => 'bg-emerald-500/20', 'text' => 'text-emerald-400'],
        'Arts & Culture' => ['bg' => 'bg-violet-500/20', 'text' => 'text-violet-400'],
        'Entertainment' => ['bg' => 'bg-rose-500/20', 'text' => 'text-rose-400'],
        'Shopping' => ['bg' => 'bg-sky-500/20', 'text' => 'text-sky-400'],
        'Family' => ['bg' => 'bg-cyan-500/20', 'text' => 'text-cyan-400'],
    ];
@endphp

<div class="bg-gradient-to-br from-steel-800 to-steel-850 p-4 md:p-8 text-steel-100 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
    {{-- Preview Header Badge --}}
    <div class="mb-6 flex items-center justify-between">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-500/20 text-amber-400 rounded-full text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Preview Mode
        </div>
        <button type="button" wire:click="togglePreview"
            class="inline-flex items-center gap-2 px-3 py-1.5 bg-steel-700 text-steel-200 rounded-lg hover:bg-steel-600 transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Back to Editor
        </button>
    </div>

    <article>
        <header class="mb-6">
            {{-- Location & Categories --}}
            <div class="flex flex-wrap gap-2 mb-4">
                @if($previewLocation)
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-accent-500/20 text-accent-400">
                        {{ $previewLocation['name'] }}
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-steel-700/50 text-steel-500 border border-dashed border-steel-600">
                        No location selected
                    </span>
                @endif

                @forelse($previewCategories as $category)
                    @php $catColors = $categoryColors[$category['parent']['name'] ?? ''] ?? ['bg' => 'bg-steel-700', 'text' => 'text-steel-300']; @endphp
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium {{ $catColors['bg'] }} {{ $catColors['text'] }}">
                        {{ $category['name'] }}
                    </span>
                @empty
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-steel-700/50 text-steel-500 border border-dashed border-steel-600">
                        No categories selected
                    </span>
                @endforelse
            </div>

            {{-- Title --}}
            @if($title)
                <h1 class="text-2xl md:text-4xl font-bold text-white mb-4">{{ $title }}</h1>
            @else
                <div class="text-2xl md:text-4xl font-bold text-steel-600 mb-4 border-b-2 border-dashed border-steel-700 pb-2">
                    [Title will appear here]
                </div>
            @endif

            {{-- Author Info --}}
            <div class="flex items-center text-steel-400 text-sm">
                <div class="flex items-center">
                    <x-avatar :avatar-path="auth()->user()->avatar_path" size="8" />
                    <span class="ml-2">By <span class="text-accent-400">{{ auth()->user()->username }}</span></span>
                </div>
                <span class="mx-2">&bull;</span>
                <span>{{ now()->format('F j, Y') }}</span>
            </div>
        </header>

        {{-- Excerpt --}}
        @if($excerpt)
            <div class="text-lg text-steel-300 mb-6 border-l-4 border-accent-500 pl-4">
                {{ $excerpt }}
            </div>
        @endif

        {{-- Guide Metadata --}}
        @if($guideRating || $guideWebsite || $guideAddress)
            <div class="mb-6 p-4 bg-steel-900/50 rounded-lg border border-steel-700/50">
                <div class="flex flex-wrap gap-4">
                    @if($guideRating)
                        <div class="flex items-center gap-2">
                            <span class="text-steel-400 text-sm">Rating:</span>
                            <div class="flex items-center gap-0.5">
                                @for($star = 1; $star <= 5; $star++)
                                    <span class="text-lg {{ $guideRating >= $star ? 'text-amber-400' : 'text-steel-600' }}">★</span>
                                @endfor
                            </div>
                        </div>
                    @endif
                    @if($guideWebsite)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-steel-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            <a href="{{ $guideWebsite }}" target="_blank" class="text-accent-400 hover:text-accent-300 text-sm">{{ $guideWebsite }}</a>
                        </div>
                    @endif
                    @if($guideAddress)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-steel-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-steel-300 text-sm">{{ $guideAddress }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Content Blocks --}}
        <x-blocks.renderer :blocks="$blocks ?? []" mode="view" />

    </article>
</div>
