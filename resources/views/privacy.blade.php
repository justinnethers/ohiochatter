<x-app-layout :seo="$seo ?? null">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Privacy Policy') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p class="m-0">Ohio Chatter collects some personal information, namely your email address, so you can log in to the site. We will not sell your information to anyone, ever. We may send you an email every now and then to keep you updated on what's going on, but we won't spam you.</p>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
