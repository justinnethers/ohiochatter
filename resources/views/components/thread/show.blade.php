<div>
    <x-breadcrumbs :forum="$thread->forum" />
{{--    <h1 class="text-6xl text-white mb-8">{{ $thread->title }}</h1>--}}
    <x-post.post :post="$thread" />
    @foreach ($replies as $post)
        <x-post.post :$post />
    @endforeach
</div>
