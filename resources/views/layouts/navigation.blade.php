<nav x-data="{ open: false }"
     x-init="$store.scroll.init()"
     :class="{ 'h-16': !$store.scroll.scrolled, 'h-12': $store.scroll.scrolled }"
     class="fixed top-0 left-0 right-0 z-50 bg-gradient-to-r from-steel-900 via-steel-800 to-steel-900 backdrop-blur-md border-b border-steel-700/50 shadow-lg shadow-black/20 transition-all duration-300">
    {{-- Blue accent line at top --}}
    <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-accent-500 to-transparent"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full">
        <div class="flex justify-between h-full transition-all duration-300">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a class="text-white font-bold text-xl flex items-center gap-2 group" href="{{ route('thread.index') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-white group-hover:text-accent-400 transition-colors duration-200"/>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('thread.index')" :active="request()->routeIs('thread.index')">
                        {{ __('All Threads') }}
                    </x-nav-link>

                    <x-nav-link href="/forums/serious-business" :active="request()->is('forums/serious-business')">
                        {{ __('Serious Business') }}
                    </x-nav-link>

                    <x-nav-link href="/forums/sports" :active="request()->is('forums/sports')">
                        {{ __('Sports') }}
                    </x-nav-link>

                    <x-nav-link href="{{ route('archive.index') }}" :active="request()->routeIs('archive.*')">
                        {{ __('Archive') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            @if (Auth::check())
                <div class="hidden sm:flex sm:items-center sm:ml-6">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-400 dark:text-gray-400 bg-gray-800 dark:bg-gray-800 hover:text-gray-300 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150 hover:bg-gray-900 hover:shadow-lg"
                                :class="{ 'py-2': !$store.scroll.scrolled, 'py-1': $store.scroll.scrolled }"
                            >
                                <div class="mr-2">{{ Auth::user()->username }}</div>

                                <div class="relative">
                                    <x-avatar size="6" :avatar-path="Auth::user()->avatar_path"/>
                                    @auth
                                        @if($count = Auth::user()->newThreadsCount() > 0)
                                            <span
                                                class="absolute -top-1 -right-1 h-3 w-3 rounded-full bg-red-500"></span>
                                        @endif
                                    @endauth
                                </div>

                                <div class="ml-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
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

                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            @if (Auth::user()->isAdmin())
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
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
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


    <!-- Responsive Navigation Menu -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         class="absolute w-full left-0 top-full bg-white dark:bg-gray-800 sm:hidden min-h-screen">

        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('thread.index')" :active="request()->routeIs('thread.index')">
                {{ __('All Threads') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link href="/forums/serious-business" :active="request()->is('forums/serious-business')">
                {{ __('Serious Business') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link href="/forums/sports" :active="request()->is('forums/sports')">
                {{ __('Sports') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link href="/forums/politics" :active="request()->is('forums/politics')">
                {{ __('Politics') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link
                href="{{ route('archive.index') }}"
                :active="request()->routeIs('archive.*')"
            >
                {{ __('Archive') }}
            </x-responsive-nav-link>

            @if (Auth::check())
                @if (Auth::user()->isAdmin())
                    <x-responsive-nav-link
                        href="/admin"
                        target="_blank"
                    >Admin
                    </x-responsive-nav-link>
                    <x-responsive-nav-link
                        href="/pulse"
                        target="_blank"
                    >Pulse
                    </x-responsive-nav-link>
                @endif
            @endif

            <x-responsive-nav-link
                :href="route('messages.index')"
                :active="request()->routeIs('messages.index')"
            >
                {{ __('Messages') }}
                @auth
                    {{--                    @if(($unreadCount = auth()->user()->threads->filter(function($thread) {--}}
                    {{--                        return $thread->isUnread(auth()->id());--}}
                    {{--                    })->count()) > 0)--}}
                    {{--                        <span class="text-red-500">[{{ $unreadCount }} new]</span>--}}
                    {{--                    @endif--}}
                @endauth
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        @if (Auth::check())
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4">
                    <div
                        class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->username }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                                               onclick="event.preventDefault();
                                        this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                <x-responsive-nav-link href="{{ route('register') }}" :active="request()->routeIs('register')">
                    {{ __('Register') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('login') }}" :active="request()->routeIs('login')">
                    {{ __('Login') }}
                </x-responsive-nav-link>
            </div>
        @endif
    </div>
</nav>
