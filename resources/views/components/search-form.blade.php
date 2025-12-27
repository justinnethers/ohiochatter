<div>
    <form action="{{ route('search.show') }}" method="GET" class="flex gap-3">
        <x-text-input
            type="search"
            name="q"
            value="{{ request('q') ?? request('query') }}"
            placeholder="Search threads, posts, users..."
            class="flex-1"
        />
        <x-primary-button type="submit">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            Search
        </x-primary-button>
    </form>
</div>
