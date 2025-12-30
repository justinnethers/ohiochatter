{{-- resources/views/ohio/regions/index.blade.php --}}
<x-app-layout>
    <x-slot name="title">Ohio Regions</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                Ohio Regions
            </h2>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:mt-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($regions as $region)
                    <a href="{{ route('region.show', $region) }}" class="group bg-gradient-to-br from-steel-800 to-steel-850 p-5 text-steel-100 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 hover:border-steel-600 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-accent-400 to-accent-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                        <h3 class="text-xl font-semibold text-white group-hover:text-accent-400 transition-colors mb-2">
                            {{ $region->name }}
                        </h3>

                        @if($region->description)
                            <p class="text-steel-400 text-sm line-clamp-2 mb-3">{{ $region->description }}</p>
                        @endif

                        @if($region->content->count() > 0)
                            <span class="text-sm text-accent-400">
                                {{ $region->content->count() }} {{ Str::plural('guide', $region->content->count()) }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
