<article class="bg-gray-800 text-white mb-4 flex rounded-lg">
    <x-post.owner :owner="$post->owner" />
    <div class="flex-1">
        <x-post.header :date="$post->created_at" />
        <div class="prose prose-invert prose-lg p-8">
            {!! $post->body !!}
        </div>
    </div>
</article>
