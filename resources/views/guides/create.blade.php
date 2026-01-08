<x-app-layout :seo="$seo ?? null">
    <x-slot name="title">{{ isset($draft) ? 'Edit Draft' : 'Create a Guide' }}</x-slot>
    <x-slot name="header">
        <h2 class="text-lg md:text-xl font-bold text-white leading-tight flex items-center gap-3">
            <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
            {{ isset($draft) ? 'Edit Draft Guide' : 'Create an Ohio Guide' }}
        </h2>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-4 md:p-8 md:mt-4">
            @livewire('create-guide', ['draft' => $draft ?? null])
        </div>
    </div>
</x-app-layout>
