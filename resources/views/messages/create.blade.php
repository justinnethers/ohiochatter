<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight">
                New Message
            </h2>
            <x-nav-link href="{{ route('messages.index') }}">Back to Messages</x-nav-link>
        </div>
    </x-slot>

    <div>
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:mt-4">
            <div class="container">
                <div class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md shadow-lg">
                    <form action="{{ route('messages.store') }}" method="POST">
                        @csrf

                        <div class="space-y-4">
                            <div>
                                <label for="recipients" class="block text-sm font-medium text-gray-200">Recipients</label>
                                <select
                                    id="recipients"
                                    name="recipients[]"
                                    multiple
                                    class="mt-1 block w-full rounded-md border-gray-600 bg-gray-800 text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                >
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->username }}</option>
                                    @endforeach
                                </select>
                                @error('recipients')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-200">Subject</label>
                                <input
                                    type="text"
                                    name="subject"
                                    id="subject"
                                    class="mt-1 block w-full rounded-md border-gray-600 bg-gray-800 text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                >
                                @error('subject')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-200">Message</label>
                                <textarea
                                    id="message"
                                    name="message"
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-600 bg-gray-800 text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                ></textarea>
                                @error('message')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex justify-end gap-x-3">
                                <x-primary-button type="button" onclick="window.history.back()">
                                    Cancel
                                </x-primary-button>
                                <x-primary-button>
                                    Send Message
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
