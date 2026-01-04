<x-app-layout>
    <x-slot name="title">Pick 'Em Admin</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-lg md:text-xl font-bold text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                Pick 'Em Admin
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('pickem.admin.groups') }}"
                   class="px-3 py-1.5 bg-steel-700 hover:bg-steel-600 rounded-lg text-steel-200 text-sm transition-colors">
                    Manage Groups
                </a>
                <a href="{{ route('pickem.admin.create') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-accent-500 to-accent-600 rounded-lg text-white font-semibold text-sm shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Pick 'Em
                </a>
            </div>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:pt-6 md:mt-4">
            <livewire:pickem-admin-manager />
        </div>
    </div>
</x-app-layout>
