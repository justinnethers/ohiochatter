<x-app-layout>
    <x-slot name="title">New Private Message</x-slot>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight">
                New Message
            </h2>
            <x-nav-link href="{{ route('messages.index') }}">Back to Messages</x-nav-link>
        </div>
    </x-slot>

    <form
        class="bg-gray-800 p-8 rounded-lg shadow mb-8 mt-8"
        action="{{ route('messages.store') }}"
        method="POST"
    >
        @csrf
        <div class="p-2 md:p-0">
            <div class="mb-4">
                <livewire:user-select />
                @error('recipients')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <x-input-label for="subject">Subject</x-input-label>
                <x-text-input
                    id="subject"
                    name="subject"
                    value="{{ old('subject') }}"
                    required
                />
                @error('subject')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <x-wysiwyg id="message" name="message" />
                @error('message')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-x-3">
                <x-secondary-button type="button" onclick="window.history.back()">
                    Cancel
                </x-secondary-button>
                <x-primary-button>Send Message</x-primary-button>
            </div>
        </div>
    </form>

    <div class="h-8"></div>
</x-app-layout>
