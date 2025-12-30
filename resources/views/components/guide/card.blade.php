{{-- resources/views/components/guide/card.blade.php --}}
@props(['content'])

<article class="group bg-gradient-to-br from-steel-800 to-steel-850 p-4 md:p-5 text-steel-100 rounded-xl mb-3 md:mb-4 shadow-lg shadow-black/20 border border-steel-700/50 hover:border-steel-600 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
    {{-- Accent stripe (shows on hover) --}}
    <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-accent-400 to-accent-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

    <a class="text-lg md:text-xl font-semibold text-white hover:text-accent-400 transition-colors" href="{{ route('guide.show', $content) }}">
        @if($content->featured)
            <span class="font-bold">{{ $content->title }}</span>
        @else
            <span>{{ $content->title }}</span>
        @endif
    </a>

    <div class="rounded-lg bg-steel-900/50 shadow-inner p-3 mt-3 mb-4">
        <div class="flex items-center justify-between space-x-2 w-full">
            <div class="flex items-center gap-2 text-sm text-steel-300">
                <x-avatar :avatar-path="$content->author->avatar_path" size="6" />
                <span>
                    {{ $content->created_at->diffForHumans() }}
                    by <a href="{{ route('profile.show', $content->author) }}" class="text-accent-400 hover:text-accent-300 font-medium transition-colors">{{ $content->author->name }}</a>
                </span>
            </div>
            <div class="hidden lg:flex flex-wrap gap-2">
                {{-- Location badge --}}
                @if($content->locatable_type === 'App\Models\Region')
                    <a href="{{ route('guide.region', $content->locatable) }}"
                       class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-accent-500/20 text-accent-400 hover:bg-accent-500/30 transition-colors">
                        {{ $content->locatable->name }}
                    </a>
                @elseif($content->locatable_type === 'App\Models\County')
                    <a href="{{ route('guide.county', ['region' => $content->locatable->region, 'county' => $content->locatable]) }}"
                       class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-rose-500/20 text-rose-400 hover:bg-rose-500/30 transition-colors">
                        {{ $content->locatable->name }} County
                    </a>
                @elseif($content->locatable_type === 'App\Models\City')
                    <a href="{{ route('guide.city', ['region' => $content->locatable->county->region, 'county' => $content->locatable->county, 'city' => $content->locatable]) }}"
                       class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/20 text-amber-400 hover:bg-amber-500/30 transition-colors">
                        {{ $content->locatable->name }}
                    </a>
                @endif

                {{-- Category Badge --}}
                @if($content->contentCategory)
                    <a href="{{ route('guide.category', $content->contentCategory) }}"
                       class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-steel-700 text-steel-300 hover:bg-steel-600 hover:text-white transition-colors">
                        {{ $content->contentCategory->name }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Mobile location and category badges --}}
    <div class="flex lg:hidden flex-wrap gap-2">
        @if($content->locatable_type === 'App\Models\Region')
            <a href="{{ route('guide.region', $content->locatable) }}"
               class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-accent-500/20 text-accent-400 hover:bg-accent-500/30 transition-colors">
                {{ $content->locatable->name }}
            </a>
        @elseif($content->locatable_type === 'App\Models\County')
            <a href="{{ route('guide.county', ['region' => $content->locatable->region, 'county' => $content->locatable]) }}"
               class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-rose-500/20 text-rose-400 hover:bg-rose-500/30 transition-colors">
                {{ $content->locatable->name }} County
            </a>
        @elseif($content->locatable_type === 'App\Models\City')
            <a href="{{ route('guide.city', ['region' => $content->locatable->county->region, 'county' => $content->locatable->county, 'city' => $content->locatable]) }}"
               class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/20 text-amber-400 hover:bg-amber-500/30 transition-colors">
                {{ $content->locatable->name }}
            </a>
        @endif

        {{-- Category Badge --}}
        @if($content->contentCategory)
            <a href="{{ route('guide.category', $content->contentCategory) }}"
               class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-steel-700 text-steel-300 hover:bg-steel-600 hover:text-white transition-colors">
                {{ $content->contentCategory->name }}
            </a>
        @endif
    </div>
</article>
