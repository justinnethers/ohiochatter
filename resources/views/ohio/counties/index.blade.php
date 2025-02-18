<x-app-layout>
    <x-slot name="title">Ohio Counties</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-200 dark:text-gray-200 leading-tight">
            Ohio Counties
        </h2>
    </x-slot>
    <div>
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:pt-4 md:mt-4">
            <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-6">
                @foreach($counties as $county)
                    <article class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md shadow-lg">
                        <a href="{{ route('county.show', $county) }}" class="text-2xl hover:underline text-gray-200">
                            {{ $county->name }}
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
        </div>
    </div>
</x-app-layout>
