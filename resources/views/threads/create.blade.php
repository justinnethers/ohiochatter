<x-app-layout>
    <x-slot name="title">Create Thread</x-slot>
    <x-slot name="header">
        <h2 class="font-bold text-white leading-tight flex items-center gap-3">
            <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
            Create Thread
        </h2>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-4 md:p-8 md:mt-4">
            <form action="/threads" method="POST">
                @csrf
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-gradient-to-r from-red-500/20 to-red-600/20 border border-red-500/50 text-red-200 rounded-xl">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="font-semibold">Please fix the following errors:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="space-y-6">
                    <div>
                        <x-input-label for="title" class="mb-2">Title</x-input-label>
                        <x-text-input id="title" name="title" value="{{ old('title') }}" required/>
                    </div>

                    <div>
                        <x-input-label for="forum_id" class="mb-2">Forum</x-input-label>
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

                    <div>
                        <x-wysiwyg id="body"/>
                    </div>

                    <!-- Poll Section -->
                    <div class="bg-steel-900/50 rounded-xl p-4 border border-steel-700/50">
                        <label class="inline-flex items-center text-steel-200 cursor-pointer">
                            <input type="hidden" name="has_poll" value="0">
                            <input type="checkbox"
                                   id="has_poll"
                                   name="has_poll"
                                   value="1"
                                   class="rounded border-steel-600 bg-steel-800 text-accent-500 focus:ring-2 focus:ring-accent-500/20 focus:ring-offset-steel-900"
                                {{ old('has_poll') ? 'checked' : '' }}>
                            <span class="ml-3 font-medium">Add a poll to this thread</span>
                        </label>

                        <div id="poll-fields" class="mt-4 space-y-4" style="display: none;">
                            <div>
                                <x-input-label for="poll_type" class="mb-2">Poll Type</x-input-label>
                                <x-select id="poll_type" name="poll_type">
                                    <option value="single">Single Choice</option>
                                    <option value="multiple">Multiple Choice</option>
                                </x-select>
                            </div>

                            <div>
                                <x-input-label class="mb-2">Poll Options</x-input-label>
                                <div id="poll-options" class="space-y-3">
                                    <div>
                                        <x-text-input
                                            type="text"
                                            name="options[]"
                                            placeholder="Option 1"
                                            value="{{ old('options.0') }}"
                                        />
                                    </div>
                                    <div>
                                        <x-text-input
                                            type="text"
                                            name="options[]"
                                            placeholder="Option 2"
                                            value="{{ old('options.1') }}"
                                        />
                                    </div>
                                </div>

                                <button type="button"
                                        id="add-option"
                                        class="mt-3 text-sm text-accent-400 hover:text-accent-300 font-medium transition-colors">
                                    + Add another option
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <x-primary-button>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            Publish Thread
                        </x-primary-button>
                    </div>
                </div>
            </form>
        </div>
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
                input.className = 'border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg shadow-inner p-2.5 px-4 text-base w-full transition-colors duration-200';

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'text-red-400 hover:text-red-300 font-medium text-sm transition-colors';
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
