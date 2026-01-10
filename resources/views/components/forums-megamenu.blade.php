@props(['class' => ''])

<div
    x-data="{
        open: false,
        timeout: null,
        enter() {
            clearTimeout(this.timeout);
            this.open = true;
        },
        leave() {
            this.timeout = setTimeout(() => this.open = false, 100);
        }
    }"
    @keydown.escape.window="open = false"
    class="relative {{ $class }}"
>
    {{-- Trigger Button --}}
    <button
        type="button"
        @mouseenter="enter()"
        @mouseleave="leave()"
        @focus="enter()"
        :aria-expanded="open"
        aria-haspopup="true"
        class="inline-flex items-center gap-1 px-1 pt-1 border-b-2 text-sm font-medium leading-5 tracking-wide focus:outline-none transition-all duration-200"
        :class="{
            'border-accent-500 text-white': open,
            'border-transparent text-steel-300 hover:text-white hover:border-accent-400/50': !open
        }"
    >
        {{ __('Forums') }}
        <svg
            class="w-4 h-4 transition-transform duration-200"
            :class="{ 'rotate-180': open }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    {{-- Megamenu Panel --}}
    <div
        x-show="open"
        @mouseenter="enter()"
        @mouseleave="leave()"
        class="absolute left-0 top-full pt-1 z-50"
        style="display: none;"
    >

        {{-- Actual panel with transitions --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            role="menu"
            aria-orientation="vertical"
        >
        <div class="w-[500px] bg-gradient-to-b from-steel-800 to-steel-900 rounded-xl shadow-2xl shadow-black/40 border border-steel-700/50 overflow-hidden">
            {{-- Header --}}
            <div class="px-4 py-3 border-b border-steel-700/50 bg-steel-800/50">
                <h3 class="text-sm font-semibold text-steel-200">Browse Forums</h3>
            </div>

            {{-- Forum Grid --}}
            <div class="p-4 grid grid-cols-2 gap-3">
                {{-- Serious Business --}}
                <a
                    href="/forums/serious-business"
                    class="group p-3 rounded-lg hover:bg-steel-700/50 transition-colors"
                    role="menuitem"
                >
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                        <span class="text-sm font-semibold text-white group-hover:text-accent-300 transition-colors">Serious Business</span>
                    </div>
                    <p class="text-xs text-steel-400 leading-relaxed">General discussion for Ohio topics and beyond</p>
                </a>

                {{-- Sports --}}
                <a
                    href="/forums/sports"
                    class="group p-3 rounded-lg hover:bg-steel-700/50 transition-colors"
                    role="menuitem"
                >
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                        <span class="text-sm font-semibold text-white group-hover:text-accent-300 transition-colors">Sports</span>
                    </div>
                    <p class="text-xs text-steel-400 leading-relaxed">Ohio State, Buckeyes, Browns, Bengals & more</p>
                </a>

                {{-- Politics --}}
                <a
                    href="/forums/politics"
                    class="group p-3 rounded-lg hover:bg-steel-700/50 transition-colors"
                    role="menuitem"
                >
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-2 h-2 rounded-full bg-red-500"></div>
                        <span class="text-sm font-semibold text-white group-hover:text-accent-300 transition-colors">Politics</span>
                    </div>
                    <p class="text-xs text-steel-400 leading-relaxed">Political news and civil debate</p>
                </a>

                {{-- Forum Archive --}}
                <a
                    href="{{ route('archive.index') }}"
                    class="group p-3 rounded-lg hover:bg-steel-700/50 transition-colors"
                    role="menuitem"
                >
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-2 h-2 rounded-full bg-orange-500"></div>
                        <span class="text-sm font-semibold text-white group-hover:text-accent-300 transition-colors">Forum Archive</span>
                    </div>
                    <p class="text-xs text-steel-400 leading-relaxed">Classic threads from years past</p>
                </a>
            </div>
        </div>
        </div>
    </div>
</div>
