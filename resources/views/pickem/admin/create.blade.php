<x-app-layout :seo="$seo ?? null">
    <x-slot name="title">Create Pick 'Em</x-slot>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('pickem.admin.index') }}" class="text-steel-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h2 class="text-lg md:text-xl font-bold text-white leading-tight">
                Create Pick 'Em
            </h2>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:pt-6 md:mt-4">
            <livewire:pickem-editor />
        </div>
    </div>
</x-app-layout>
