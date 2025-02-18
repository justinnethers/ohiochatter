<x-app-layout>
    <x-slot name="title">{{ $region->name }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-200 dark:text-gray-200 leading-tight">
            {{ $region->name }}
        </h2>
    </x-slot>
    <div>
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:pt-4 md:mt-4">
            <div class="bg-gray-700 rounded-lg p-6 mb-8">
                <h1 class="text-3xl font-bold text-gray-100 mb-4">{{ $region->name }}</h1>
                <p class="text-gray-300 text-lg">{{ $region->description }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                @foreach($counties as $county)
                    <article class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md shadow-lg">
                        <a href="{{ route('county.show', ['region' => $region, 'county' => $county]) }}" class="text-2xl hover:underline text-gray-200">
                            {{ $county->name }} County
                        </a>

                        <div class="mt-4 text-gray-300">
                            {{ $county->description }}
                        </div>

                        @if($county->content->count() > 0)
                            <div class="mt-4 text-sm text-gray-400">
                                {{ $county->content->count() }} {{ Str::plural('guide', $county->content->count()) }}
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>

            @if($featuredContent->isNotEmpty())
                <section class="mt-8">
                    <h2 class="text-2xl font-bold text-gray-200 mb-4">Featured Guides</h2>
                    @foreach($featuredContent as $content)
                        <x-guide.card :content="$content" />
                    @endforeach
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
