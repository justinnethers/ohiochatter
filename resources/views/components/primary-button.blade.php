<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-3 py-1.5 bg-blue-300 dark:bg-blue-300 border border-transparent rounded-md font-medium text-blue-950 dark:text-blue-950 tracking-wide hover:bg-blue-400 dark:hover:bg-blue-400 focus:bg-white dark:focus:bg-white active:bg-gray-300 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
