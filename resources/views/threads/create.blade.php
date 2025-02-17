<x-app-layout>
    <x-slot name="title">Create Thread</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-gray-200 dark:text-gray-200 leading-tight">
            Create Thread
        </h2>
    </x-slot>

    <div class="p-2">
        <form
            class="px-0 md:px-8"
            action="/threads"
            method="POST"
        >
            @csrf
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-500 text-white rounded">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="p-2 md:p-0">
                <div class="mb-4">
                    <x-input-label for="title">Title</x-input-label>
                    <x-text-input id="title" name="title" value="{{ old('title') }}" required />
                </div>

                <div class="mb-4">
                    <x-input-label for="forum_id">Forum</x-input-label>
                    <x-select id="forum_id" name="forum_id">
                        @foreach ($forums as $f)
                            @if ($f->id == $forum->id)
                                @php ($selected = ' selected')
                            @else
                                @php ($selected = '')
                            @endif
                            @if ($f->is_restricted)
                                @can('moderate', $f)
                                    <option value="{{ $f->id }}"{{ $selected }}>{{ $f->name }}</option>
                                @endcan
                            @else
                                <option value="{{ $f->id }}"{{ $selected }}>{{ $f->name }}</option>
                            @endif
                        @endforeach
                    </x-select>
                </div>

                <div class="mb-4">
                    <x-wysiwyg id="body" />
                </div>

                <!-- Poll Section -->
                <div class="mb-4">
                    <label class="inline-flex items-center text-gray-200">
                        <input type="hidden" name="has_poll" value="0">
                        <input type="checkbox"
                               id="has_poll"
                               name="has_poll"
                               value="1"
                               class="rounded border-gray-600 bg-gray-700 text-blue-500 focus:ring-blue-500"
                            {{ old('has_poll') ? 'checked' : '' }}>
                        <span class="ml-2">Add a poll to this thread</span>
                    </label>
                </div>

                <div id="poll-fields" class="mb-4" style="display: none;">
                    <div class="mb-4">
                        <x-input-label for="poll_type">Poll Type</x-input-label>
                        <x-select id="poll_type" name="poll_type">
                            <option value="single">Single Choice</option>
                            <option value="multiple">Multiple Choice</option>
                        </x-select>
                    </div>

                    <div id="poll-options" class="space-y-3">
                        <div>
                            <x-text-input
                                type="text"
                                name="options[]"
                                placeholder="Option 1"
                                value="{{ old('options.0') }}"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <x-text-input
                                type="text"
                                name="options[]"
                                placeholder="Option 2"
                                value="{{ old('options.1') }}"
                                class="w-full"
                            />
                        </div>
                    </div>

                    <button type="button"
                            id="add-option"
                            class="mt-3 text-sm text-blue-400 hover:text-blue-300">
                        + Add another option
                    </button>
                </div>

                <div>
                    <x-primary-button>Publish Thread</x-primary-button>
                </div>
            </div>
        </form>

        <div class="h-8"></div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hasPoll = document.getElementById('has_poll');
            const pollFields = document.getElementById('poll-fields');

            // Initialize poll fields visibility
            pollFields.style.display = hasPoll.checked ? 'block' : 'none';

            // Toggle poll fields
            hasPoll.addEventListener('change', function() {
                pollFields.style.display = this.checked ? 'block' : 'none';
            });

            // Add new option
            document.getElementById('add-option').addEventListener('click', function() {
                const container = document.getElementById('poll-options');
                const optionCount = container.children.length + 1;

                const wrapper = document.createElement('div');
                wrapper.className = 'flex gap-2 items-center';

                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'options[]';
                input.placeholder = `Option ${optionCount}`;
                input.className = 'w-full rounded-md border-gray-600 bg-gray-700 text-gray-200 focus:border-blue-500 focus:ring-blue-500';

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'text-red-400 hover:text-red-300';
                removeButton.textContent = 'Remove';
                removeButton.onclick = function() {
                    wrapper.remove();
                };

                wrapper.appendChild(input);
                wrapper.appendChild(removeButton);
                container.appendChild(wrapper);
            });

            // Handle old poll data if validation failed
            @if(old('has_poll'))
                hasPoll.checked = true;
                pollFields.style.display = 'block';
            @endif
        });
    </script>
</x-app-layout>
