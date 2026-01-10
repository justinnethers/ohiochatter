<div x-data="{ open: false }">
<nav x-init="$store.scroll.init()"
     :class="{ 'h-16': !$store.scroll.scrolled, 'h-12': $store.scroll.scrolled }"
     class="fixed top-0 left-0 right-0 z-50 bg-gradient-to-r from-steel-900 via-steel-800 to-steel-900 backdrop-blur-md border-b border-steel-700/50 shadow-lg shadow-black/20 transition-all duration-300">
    {{-- Blue accent line at top --}}
    <div
        class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-accent-500 to-transparent"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full">
        <div class="flex justify-between h-full transition-all duration-300">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a class="flex items-center group" href="{{ route('home') }}">
                        <x-application-logo />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 sm:-my-px sm:ml-10 sm:flex items-center">
                    <x-nav-link :href="route('thread.index')" :active="request()->routeIs('thread.index')">
                        {{ __('All Threads') }}
                    </x-nav-link>

                    {{-- Forums Megamenu (tablet and desktop) --}}
                    <x-forums-megamenu class="hidden md:block" />

                    {{-- Simple Forums link for small tablets (640-767px) --}}
                    <x-nav-link
                        href="/forums/serious-business"
                        :active="request()->is('forums/*') || request()->routeIs('archive.*')"
                        class="md:hidden"
                    >
                        {{ __('Forums') }}
                    </x-nav-link>

                    <x-nav-link href="{{ route('pickem.index') }}" :active="request()->routeIs('pickem.*')">
                        {{ __('Pick \'ems') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            @if (Auth::check())
                <div class="hidden sm:flex sm:items-center sm:ml-6 gap-2">
                    <livewire:notifications-dropdown/>
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center px-3 py-2 border border-steel-600/50 text-sm leading-4 font-medium rounded-lg text-steel-200 bg-steel-800/80 hover:text-white hover:bg-steel-700 hover:border-steel-500 focus:outline-none focus:ring-2 focus:ring-accent-500/20 transition-all duration-200 hover:shadow-lg"
                                :class="{ 'py-2': !$store.scroll.scrolled, 'py-1': $store.scroll.scrolled }"
                            >
                                <div class="mr-2">{{ Auth::user()->username }}</div>

                                <div class="relative">
                                    <x-avatar size="6" :avatar-path="Auth::user()->avatar_path"/>
                                    @auth
                                        @if($count = Auth::user()->newThreadsCount() > 0)
                                            <span
                                                class="absolute -top-1 -right-1 h-3 w-3 rounded-full bg-red-500 ring-2 ring-steel-800"></span>
                                        @endif
                                    @endauth
                                </div>

                                <div class="ml-1">
                                    <svg class="fill-current h-4 w-4 text-steel-400" xmlns="http://www.w3.org/2000/svg"
                                         viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                              d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                              clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('messages.index')">
                                {{ __('Messages') }}
                                @auth
                                    @if($unreadCount = Auth::user()->unreadMessagesCount())
                                        <span class="text-red-500">[{{ $unreadCount }} new]</span>
                                    @endif
                                @endauth
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('dashboard')">
                                {{ __('Dashboard') }}
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('User Settings') }}
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('profile.show', auth()->user())">
                                {{ __('Public Profile') }}
                            </x-dropdown-link>

                            <div class="border-t border-steel-700/50 my-1"></div>

                            @if (Auth::user()->isAdmin())
                                <x-dropdown-link :href="route('guide.my-guides')">
                                    {{ __('Guides') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('pulse')" target="_blank">
                                    {{ __('Pulse') }}
                                </x-dropdown-link>
                                <x-dropdown-link href="/admin" target="_blank">
                                    {{ __('Admin') }}
                                </x-dropdown-link>
                            @endif

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                                 onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            @else
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link href="{{ route('register') }}" :active="request()->routeIs('register')">
                        {{ __('Register') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('login') }}" :active="request()->routeIs('login')">
                        {{ __('Login') }}
                    </x-nav-link>
                </div>
            @endif

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden gap-2">
                @auth
                    <livewire:notifications-dropdown/>
                @endauth
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-lg text-steel-400 hover:text-white hover:bg-steel-700/50 focus:outline-none focus:bg-steel-700/50 focus:text-white transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                              stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>


</nav>

{{-- Mobile Slide-in Drawer --}}
<div
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[60] sm:hidden"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="absolute inset-0 bg-black/60 backdrop-blur-sm"
    ></div>

    {{-- Drawer Panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-[cubic-bezier(0.32,0.72,0,1)] duration-500"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-[cubic-bezier(0.32,0.72,0,1)] duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="absolute right-0 top-0 bottom-0 w-[85%] max-w-sm bg-gradient-to-b from-steel-800 to-steel-900 shadow-2xl shadow-black/50 flex flex-col"
    >
        {{-- Drawer Header --}}
        <div class="flex items-center justify-between p-4 border-b border-steel-700/50">
            <a href="{{ route('home') }}" class="flex items-center gap-2" @click="open = false">
                <x-application-logo :showText="false" />
                <span class="text-white font-bold text-lg">Ohio Chatter</span>
            </a>
            <button
                @click="open = false"
                class="p-2 rounded-lg text-steel-400 hover:text-white hover:bg-steel-700/50 transition-colors"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Drawer Content --}}
        <div class="flex-1 overflow-y-auto py-4">
            {{-- Forums Section --}}
            <div class="px-4 mb-2">
                <h3 class="text-xs font-semibold text-steel-500 uppercase tracking-wider">Forums</h3>
            </div>
            <div class="space-y-1">
                <x-responsive-nav-link :href="route('thread.index')" :active="request()->routeIs('thread.index')" @click="open = false">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-steel-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        {{ __('All Threads') }}
                    </span>
                </x-responsive-nav-link>

                <x-responsive-nav-link href="/forums/serious-business" :active="request()->is('forums/serious-business')" @click="open = false">
                    <span class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        {{ __('Serious Business') }}
                    </span>
                </x-responsive-nav-link>

                <x-responsive-nav-link href="/forums/sports" :active="request()->is('forums/sports')" @click="open = false">
                    <span class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        {{ __('Sports') }}
                    </span>
                </x-responsive-nav-link>

                <x-responsive-nav-link href="/forums/politics" :active="request()->is('forums/politics')" @click="open = false">
                    <span class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                        {{ __('Politics') }}
                    </span>
                </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ route('archive.index') }}" :active="request()->routeIs('archive.*')" @click="open = false">
                    <span class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-steel-500"></span>
                        {{ __('Archive') }}
                    </span>
                </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ route('pickem.index') }}" :active="request()->routeIs('pickem.*')" @click="open = false">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-steel-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('Pick \'ems') }}
                    </span>
                </x-responsive-nav-link>
            </div>

            @if (Auth::check())
                {{-- Account Section --}}
                <div class="px-4 mt-6 mb-2">
                    <h3 class="text-xs font-semibold text-steel-500 uppercase tracking-wider">Account</h3>
                </div>

                {{-- User Info --}}
                <div class="px-4 py-3 flex items-center gap-3">
                    <x-avatar size="10" :avatar-path="Auth::user()->avatar_path"/>
                    <div>
                        <div class="font-semibold text-white">{{ Auth::user()->username }}</div>
                        <div class="text-sm text-steel-400">{{ Auth::user()->email }}</div>
                    </div>
                </div>

                <div class="space-y-1">
                    <x-responsive-nav-link :href="route('messages.index')" :active="request()->routeIs('messages.index')" @click="open = false">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-steel-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ __('Messages') }}
                            @if($unreadCount = Auth::user()->unreadMessagesCount())
                                <span class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $unreadCount }}</span>
                            @endif
                        </span>
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('dashboard')" @click="open = false">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-steel-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                            </svg>
                            {{ __('Dashboard') }}
                        </span>
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('profile.edit')" @click="open = false">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-steel-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ __('Settings') }}
                        </span>
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('profile.show', auth()->user())" @click="open = false">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-steel-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ __('Profile') }}
                        </span>
                    </x-responsive-nav-link>
                </div>

                @if (Auth::user()->isAdmin())
                    {{-- Admin Section --}}
                    <div class="px-4 mt-6 mb-2">
                        <h3 class="text-xs font-semibold text-steel-500 uppercase tracking-wider">Admin</h3>
                    </div>
                    <div class="space-y-1">
                        <x-responsive-nav-link href="/admin" target="_blank">
                            <span class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-steel-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                </svg>
                                {{ __('Admin Panel') }}
                            </span>
                        </x-responsive-nav-link>
                        <x-responsive-nav-link href="/pulse" target="_blank">
                            <span class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-steel-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                {{ __('Pulse') }}
                            </span>
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('guide.my-guides')" @click="open = false">
                            <span class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-steel-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ __('Guides') }}
                            </span>
                        </x-responsive-nav-link>
                    </div>
                @endif
            @else
                {{-- Guest Actions --}}
                <div class="px-4 mt-6 space-y-3">
                    <a
                        href="{{ route('login') }}"
                        class="block w-full py-3 px-4 text-center font-semibold text-white bg-accent-500 hover:bg-accent-600 rounded-lg transition-colors"
                        @click="open = false"
                    >
                        {{ __('Log In') }}
                    </a>
                    <a
                        href="{{ route('register') }}"
                        class="block w-full py-3 px-4 text-center font-semibold text-steel-300 border border-steel-600 hover:border-steel-500 hover:text-white rounded-lg transition-colors"
                        @click="open = false"
                    >
                        {{ __('Create Account') }}
                    </a>
                </div>
            @endif
        </div>

        {{-- Drawer Footer --}}
        @if (Auth::check())
            <div class="border-t border-steel-700/50 p-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="flex items-center gap-3 w-full px-3 py-2 text-left text-steel-300 hover:text-white hover:bg-steel-700/50 rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
</div>
