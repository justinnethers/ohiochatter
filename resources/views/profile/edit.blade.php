<x-app-layout :seo="$seo ?? null">
    <x-slot name="title">Profile Settings</x-slot>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-3">
            <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
            {{ __('Profile Settings') }}
        </h2>
    </x-slot>

    <div class="container mx-auto">
        <div class="max-w-2xl mx-auto space-y-6 p-4 md:p-0 md:py-6">
            <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 md:p-8 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 md:p-8 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
                @include('profile.partials.update-password-form')
            </div>

            <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 md:p-8 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
