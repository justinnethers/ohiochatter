@props(['showText' => true, 'scrolled' => false])

<div class="flex items-center gap-2">
    <div
        class="bg-white rounded-md p-1 transition-all duration-200"
        :class="$store.scroll.scrolled ? 'p-0.5' : 'p-1'"
    >
        <img
            src="/images/logo.png"
            alt="Ohio Chatter"
            class="w-auto transition-all duration-200"
            :class="$store.scroll.scrolled ? 'h-5' : 'h-6'"
        >
    </div>
    @if($showText)
        <span
            class="text-white font-bold hidden sm:inline group-hover:text-accent-400 transition-all duration-200"
            :class="$store.scroll.scrolled ? 'text-lg' : 'text-xl'"
        >Ohio Chatter</span>
    @endif
</div>
