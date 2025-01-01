<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-gray-800 dark:text-gray-200 leading-tight">
            Create Thread
        </h2>
    </x-slot>

    <form
        class="bg-gray-800 p-8 rounded-lg shadow mb-8 mt-8"
        action="/threads"
        method="POST"
    >
        @csrf
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
            <div>
                <x-wysiwyg id="body" />
                <div class="h-4"></div>
                <x-primary-button>Publish Thread</x-primary-button>
{{--                <button @click.prevent="togglePoll" class="button red" :class="{ 'btn-danger': showPoll }" v-text="showPoll ? 'Remove Poll' : 'Add Poll'"></button>--}}
            </div>
        </div>
    </form>

    <div class="h-8"></div>
</x-app-layout>
