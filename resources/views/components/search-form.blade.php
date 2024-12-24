<div>
    <form action="{{ route('search.show') }}" method="GET" class="flex gap-2">
        <input
            type="search"
            name="q"
            value="{{ request('q') ?? request('query') }}"
            placeholder="Search threads, posts, users..."
            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm p-1.5 px-3 text-lg w-full font-medium"
        >
        <button
            type="submit"
            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
            Search
        </button>
    </form>
</div>
