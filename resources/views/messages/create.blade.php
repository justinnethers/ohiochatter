<x-app-layout>
    <x-slot name="title">New Private Message</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                New Message
            </h2>
            <a href="{{ route('messages.index') }}" class="text-steel-300 hover:text-white transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Messages
            </a>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-4 md:p-8 md:mt-4">
            <form action="{{ route('messages.store') }}" method="POST">
                @csrf

                <div class="space-y-6">
                    <div>
                        <x-input-label class="mb-2">Recipients</x-input-label>
                        <livewire:user-select />
                        <x-input-error :messages="$errors->get('recipients')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="subject" class="mb-2">Subject</x-input-label>
                        <x-text-input
                            id="subject"
                            name="subject"
                            value="{{ old('subject') }}"
                            required
                        />
                        <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label class="mb-2">Message</x-input-label>
                        <x-wysiwyg id="message" name="message" />
                        <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-secondary-button type="button" onclick="window.history.back()">
                            Cancel
                        </x-secondary-button>
                        <x-primary-button>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            Send Message
                        </x-primary-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
