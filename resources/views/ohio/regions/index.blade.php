{{-- resources/views/ohio/regions/index.blade.php --}}
<x-app-layout>
    <x-slot name="title">Ohio Regions</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-200 dark:text-gray-200 leading-tight">
            Ohio Regions
        </h2>
    </x-slot>
    <div>
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:pt-4 md:mt-4">
            <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-6">
                @foreach($regions as $region)
                    <article class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md shadow-lg">
                        <a href="{{ route('region.show', $region) }}" class="text-2xl hover:underline text-gray-200">
                            {{ $region->name }}
                        </a>

                        <div class="mt-4 text-gray-300">
                            {{ $region->description }}
                        </div>

                        @if($region->content->count() > 0)
                            <div class="mt-4 text-sm text-gray-400">
                                {{ $region->content->count() }} {{ Str::plural('guide', $region->content->count()) }}
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
