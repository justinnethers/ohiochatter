{{-- resources/views/layouts/app.blade.php --}}
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name='admaven-placement' content=Bqjw8rHY4>

    @guest
        {{--        @php--}}
        {{--            $isUS = session('is_us_visitor', function () {--}}
        {{--                $position = \Stevebauman\Location\Facades\Location::get();--}}
        {{--                $isUS = $position && $position->countryCode === 'US';--}}
        {{--                session(['is_us_visitor' => $isUS]);--}}
        {{--                return $isUS;--}}
        {{--            });--}}
        {{--        @endphp--}}
        {{--        @if(!$isUS)--}}
        {{--            <script data-cfasync="false" src="//dcbbwymp1bhlf.cloudfront.net/?wbbcd=1235535"></script>--}}
        {{--        @endif--}}
    @endguest

    {{-- SEO Meta Tags --}}
    @if(isset($seo))
        <x-seo.head :seo="$seo"/>
        <title>{{ $seo->title }} - {{ config('app.name', 'OhioChatter') }}</title>
    @else
        <meta name="description" content="{{ $meta ?? '' }}">
        <title>@if (isset($title))
                {{ $title }} -
            @endif{{ config('app.name', 'Laravel') }}</title>
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Bitter:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
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

    {{-- AdSense script for manual ad units only (no auto ads) --}}
    <script async
            src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"
            crossorigin="anonymous"></script>

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
<div class="min-h-screen bg-gradient-to-br from-steel-950 via-steel-900 to-steel-950 relative">
    {{-- Subtle background pattern overlay --}}
    <div
        class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSA2MCAwIEwgMCAwIDAgNjAiIGZpbGw9Im5vbmUiIHN0cm9rZT0icmdiYSgyNTUsMjU1LDI1NSwwLjAyKSIgc3Ryb2tlLXdpZHRoPSIxIi8+PC9wYXR0ZXJuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI2dyaWQpIi8+PC9zdmc+')] opacity-50 pointer-events-none"></div>

    <div class="relative">
        @include('layouts.navigation')

        @if (isset($header))
            <header x-data="{}"
                    x-bind:class="{ 'py-2 md:py-4 top-16': !$store.scroll.scrolled, 'py-1.5 md:py-2 top-10': $store.scroll.scrolled }"
                    class="bg-gradient-to-r from-steel-800 via-steel-800 to-steel-850 border-b border-steel-700/50 shadow-xl shadow-black/20 sticky z-40 transition-all duration-300">
                <div
                    x-bind:class="{ 'text-base md:text-lg': !$store.scroll.scrolled, 'text-sm md:text-base': $store.scroll.scrolled }"
                    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <main class="container max-w-7xl mx-auto {{ isset($header) ? 'mt-20 md:mt-24' : 'mt-16' }} relative z-10">
            {{ $slot }}
        </main>

        @include('layouts.footer')
    </div>
</div>

@if (isset($footer))
    {{ $footer }}
@endif
@stack('footer')

<x-guest-signup-modal/>
</body>
</html>
