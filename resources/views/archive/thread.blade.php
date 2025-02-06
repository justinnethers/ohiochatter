<x-app-layout>
    <x-slot name="title">Archive {{ $thread->title }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight">
            Archive
        </h2>
    </x-slot>
    <div class="mx-auto container bg-slate-800 rounded-lg mt-12 text-white p-8">
        <h1 class="mb-8">{{ $thread->title }}</h1>
        <ul>
            @foreach($posts as $post)
                <li class="mb-2 bg-slate-700 rounded p-3 px-4">
                    <div class="flex gap-8">
                        <div style="width: 150px;" class="w-auto">
                            <div>{{$post->username}}</div>
                            @if($post->creator && $post->creator->avatar)
                                <img class="rounded-full size-12" src="/storage/avatars/archive/{{ $post->creator->avatar->filename }}" />
                            @endif
                        </div>
                        <div class="flex-1">
                            {!! parseBBCode($post->pagetext) !!}</div>
                    </div>
                </li>
            @endforeach
        </ul>

        {{ $posts->links() }}
    </div>
</x-app-layout>

<style scoped>
    cite {
        display: block;
        margin-bottom: 1rem;
    }

    blockquote {
        margin-bottom: 1rem;
    }
</style>
