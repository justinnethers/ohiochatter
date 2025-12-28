@props(['thread'])

<div class="relative">
    <x-thread.listing :$thread/>
    <div class="-mt-4 md:-mt-7 mb-2 md:mb-5 mx-1">
        <div class="bg-steel-900/70 rounded-b-xl px-3 md:px-4 py-2 md:py-3 border border-t-0 border-steel-700/50 text-sm text-steel-400">
            {{ Str::limit(strip_tags($thread->body), 200) }}
        </div>
    </div>
</div>
