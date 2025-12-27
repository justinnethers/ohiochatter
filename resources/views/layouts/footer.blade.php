{{-- resources/views/layouts/app.blade.php --}}
<footer class="bg-gradient-to-b from-steel-900 to-steel-950 text-steel-300 mt-12 relative">
    {{-- Top accent line --}}
    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-accent-500/50 to-transparent"></div>

    <div class="container max-w-7xl mx-auto px-4 py-12 pb-32">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-10">
            {{-- Logo section --}}
            <div class="order-last lg:order-first">
                <div class="flex flex-col items-center lg:items-start">
                    <div class="w-40 h-40 bg-white p-3 rounded-2xl shadow-xl shadow-black/30 mb-5 hover:scale-105 transition-transform duration-300">
                        <a href="/">
                            <img src="{{ asset('images/logo.png') }}" alt="Site Logo" class="w-full h-full object-contain">
                        </a>
                    </div>
                    <p class="text-sm text-steel-500 text-center lg:text-left">
                        &copy; {{ date('Y') }} Ohio Chatter.<br>
                        <span class="text-steel-600">All rights reserved.</span>
                    </p>
                </div>
            </div>

            {{-- Active users section --}}
            <livewire:active-users />

            {{-- Links section --}}
            <div class="lg:col-span-2 space-y-8">
                <x-search-form />

                <div class="grid grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
                            <span class="w-1 h-5 bg-accent-500 rounded-full"></span>
                            Forums
                        </h3>
                        <ul class="space-y-3">
                            <li>
                                <a href="{{ route('thread.index') }}" class="text-steel-400 hover:text-white hover:pl-2 transition-all duration-200">
                                    All Threads
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('forum.show', 'serious-business') }}" class="text-steel-400 hover:text-accent-400 hover:pl-2 transition-all duration-200">
                                    Serious Business
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('forum.show', 'sports') }}" class="text-steel-400 hover:text-accent-400 hover:pl-2 transition-all duration-200">
                                    Sports
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('forum.show', 'politics') }}" class="text-steel-400 hover:text-amber-400 hover:pl-2 transition-all duration-200">
                                    Politics
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('archive.index') }}" class="text-steel-400 hover:text-steel-200 hover:pl-2 transition-all duration-200">
                                    Archive
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('ohio.index') }}" class="text-steel-400 hover:text-amber-300 hover:pl-2 transition-all duration-200">
                                    Ohio Guide
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
                            <span class="w-1 h-5 bg-steel-500 rounded-full"></span>
                            Resources
                        </h3>
                        <ul class="space-y-3">
                            @auth
                                <li>
                                    <a href="{{ route('dashboard') }}" class="text-steel-400 hover:text-white hover:pl-2 transition-all duration-200">
                                        Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('profile.edit') }}" class="text-steel-400 hover:text-white hover:pl-2 transition-all duration-200">
                                        Profile
                                    </a>
                                </li>
                            @else
                                <li>
                                    <a href="{{ route('login') }}" class="text-steel-400 hover:text-white hover:pl-2 transition-all duration-200">
                                        Login
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('register') }}" class="text-steel-400 hover:text-white hover:pl-2 transition-all duration-200">
                                        Register
                                    </a>
                                </li>
                            @endauth
                            <li>
                                <a href="https://paypal.me/justinnethers?locale.x=en_US"
                                   class="inline-flex items-center gap-2 text-steel-400 hover:text-amber-400 hover:pl-2 transition-all duration-200"
                                   target="_blank">
                                    Buy Me A Beer <span class="text-lg">🍺</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('privacy') }}" class="text-steel-400 hover:text-white hover:pl-2 transition-all duration-200">
                                    Privacy Policy
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
