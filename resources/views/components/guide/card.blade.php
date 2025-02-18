{{-- resources/views/components/guide/card.blade.php --}}
@props(['content'])

<article class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md mb-2 md:mb-6 shadow-lg">
    <a class="text-2xl hover:underline text-gray-200" href="{{ route('guide.show', $content) }}">
        @if($content->featured)
            <span class="font-black">{{ $content->title }}</span>
        @else
            <span>{{ $content->title }}</span>
        @endif
    </a>

    <div class="md:flex text-base justify-between rounded md:rounded-md px-2 mt-3 mb-4 bg-gray-800 shadow divide-y divide-gray-700">
        <div class="flex items-center space-x-2 py-1.5 px-0.5">
{{--            <x-avatar :user="$content->author" size="6" />--}}
{{--            <span>--}}
{{--                {{ $content->created_at->diffForHumans() }}--}}
{{--                by <a href="{{ route('profile.show', $content->author) }}" class="text-blue-500 hover:underline">{{ $content->author->username }}</a>--}}
{{--            </span>--}}
        </div>

        @if($content->lastReply)
            <div class="flex items-center justify-end space-x-2 bg-main-color posted-by-when rounded shadow md:shadow-none py-1.5 md:p-2 md:p-0 md:m-0">
                <div class="text-right">
                    <span class="md:mr-1">last update </span>
                    <span class="md:mr-1">
                        {{ $content->lastReply->created_at->diffForHumans() }}
                        by
                    </span>
{{--                    <a href="{{ route('profile.show', $content->lastReply->author) }}" class="text-blue-500 hover:underline">--}}
                        {{ $content->lastReply->author->username }}
{{--                    </a>--}}
                </div>
{{--                <x-avatar :user="$content->lastReply->author" size="6" />--}}
            </div>
        @endif
    </div>

    <div class="hidden md:flex flex-wrap text-lg md:mt-2">
        {{-- Location Badge --}}
        <div class="flex order-0 flex-1 md:flex-none">
            @if($content->locatable_type === 'App\Models\Region')
                <a href="{{ route('guide.region', $content->locatable) }}"
                   class="flex items-center justify-items bg-blue-300 p-1 px-2 rounded text-blue-950 hover:shadow-lg leading-none">
                    {{ $content->locatable->name }}
                </a>
            @elseif($content->locatable_type === 'App\Models\County')
                <a href="{{ route('guide.county', ['region' => $content->locatable->region, 'county' => $content->locatable]) }}"
                   class="flex items-center justify-items bg-red-300 p-1 px-2 rounded text-red-950 hover:shadow-lg leading-none">
                    {{ $content->locatable->name }} County
                </a>
            @elseif($content->locatable_type === 'App\Models\City')
                <a href="{{ route('guide.city', ['region' => $content->locatable->county->region, 'county' => $content->locatable->county, 'city' => $content->locatable]) }}"
                   class="flex items-center justify-items bg-orange-300 p-1 px-2 rounded text-orange-950 hover:shadow-lg leading-none">
                    {{ $content->locatable->name }}
                </a>
            @endif
        </div>

        {{-- Category Badge --}}
        @if($content->contentCategory)
            <div class="flex md:ml-2">
                <a href="{{ route('guide.category', $content->contentCategory) }}"
                   class="flex items-center justify-items bg-gray-800 p-1 px-2 rounded text-gray-200 hover:shadow-lg leading-none">
                    {{ $content->contentCategory->name }}
                </a>
            </div>
        @endif
    </div>
</article>
