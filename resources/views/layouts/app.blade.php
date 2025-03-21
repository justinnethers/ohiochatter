{{-- resources/views/layouts/app.blade.php --}}
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="description" content="{{ $meta ?? '' }}">

    <title>@if (isset($title))
            {{ $title }} -
        @endif{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,400;0,700;0,900;1,400;1,700&family=Work+Sans:wght@400;500&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --nav-height: 4rem;
        }

        @view-transition {
            navigation: auto;
        }
    </style>

    @if (isset($head))
        {{ $head }}
    @endif

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')

    @if (! Auth::check())
        <script async
                src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4406607721782655"
                crossorigin="anonymous"></script>
    @endif

    {{-- Add this to app.blade.php's <head> section --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('scroll', {
                scrolled: false,
                transitioning: false,
                lastScrollY: 0,
                scrollDirection: null,
                transitionTimer: null,

                init() {
                    // Initialize based on current scroll position
                    this.scrolled = window.pageYOffset > 65;
                    this.lastScrollY = window.pageYOffset;
                    document.documentElement.style.setProperty('--nav-height', this.scrolled ? '3rem' : '4rem');

                    const handleScroll = () => {
                        // Determine scroll direction
                        const currentScrollY = window.pageYOffset;
                        this.scrollDirection = currentScrollY > this.lastScrollY ? 'down' : 'up';
                        this.lastScrollY = currentScrollY;

                        // if (this.transitioning) {
                        //     return;
                        // }

                        // Handle different state changes with increased thresholds
                        if (!this.scrolled && this.scrollDirection === 'down' && currentScrollY > 70) {
                            this.setScrolledState(true);
                        } else if (this.scrolled && this.scrollDirection === 'up' && currentScrollY < 50) {
                            this.setScrolledState(false);
                        }
                    };

                    // Debounced scroll listener with high performance
                    let ticking = false;
                    window.addEventListener('scroll', () => {
                        if (!ticking) {
                            window.requestAnimationFrame(() => {
                                handleScroll();
                                ticking = false;
                            });
                            ticking = true;
                        }
                    }, {passive: true});
                },

                // Method to change state with mandatory cooldown period
                setScrolledState(newState) {
                    // Only change if it's different
                    if (this.scrolled !== newState) {
                        this.scrolled = newState;
                        this.transitioning = true;
                        document.documentElement.style.setProperty('--nav-height', newState ? '3rem' : '4rem');

                        // Clear any existing timer
                        if (this.transitionTimer) {
                            clearTimeout(this.transitionTimer);
                        }

                        // Set a cooldown period to prevent rapid toggling
                        this.transitionTimer = setTimeout(() => {
                            this.transitioning = false;
                        }, 500); // 500ms cooldown before allowing another state change
                    }
                }
            });
        });
    </script>
</head>
@if(app()->isProduction())
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-1KYZYV7374"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());

        gtag('config', 'G-1KYZYV7374');
    </script>
@endif
<body class="font-sans antialiased">
<div class="min-h-screen bg-gray-900 dark:bg-gray-900">
    @include('layouts.navigation')

    @if (isset($header))
        <header x-data="{}"
                x-bind:class="{ 'py-4 top-16': !$store.scroll.scrolled, 'py-2 top-10 shadow-gray-700/50': $store.scroll.scrolled }"
                class="bg-gray-800 dark:bg-gray-800 shadow-xl sticky z-40 transition-all duration-300">
            <div x-bind:class="{ 'text-xl': !$store.scroll.scrolled, 'text-lg': $store.scroll.scrolled }"
                 class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endif

    <main class="container max-w-7xl mx-auto mt-20 md:mt-24">
        {{ $slot }}
    </main>

    @include('layouts.footer')
</div>

@if (isset($footer))
    {{ $footer }}
@endif
@stack('footer')
</body>
</html>
