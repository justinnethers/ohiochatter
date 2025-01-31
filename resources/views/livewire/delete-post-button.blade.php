<div>
    <button
        wire:click.prevent="delete"
        wire:confirm="Are you sure you want to delete this post? This action cannot be undone."
        class="mr-6 text-red-950 hover:text-white bg-red-500 hover:bg-red-700 py-1 px-2 rounded">
        Delete
    </button>
</div>
