<div>
    <button
        wire:click.prevent="delete"
        wire:confirm="Are you sure you want to delete this post? This action cannot be undone."
        class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-red-500 to-red-600 rounded-lg text-white shadow-lg shadow-red-500/25 hover:shadow-red-500/40 hover:from-red-600 hover:to-red-700 transition-all duration-200">
        Delete
    </button>
</div>
