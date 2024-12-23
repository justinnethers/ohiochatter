<div>
    <form action="{{ route('search.show') }}" method="GET" class="flex gap-2">
        <input
            type="search"
            name="q"
            value="{{ request('q') ?? request('query') }}"
            placeholder="Search threads, posts, users..."
            class=" p-2 flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
        >
        <button
            type="submit"
            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
            Search
        </button>
    </form>
</div>
