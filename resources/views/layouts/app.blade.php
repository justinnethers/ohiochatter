<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@if (isset($title)){{ $title }} - @endif{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,400;0,700;0,900;1,400;1,700&family=Work+Sans:wght@400;500&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/ui/trumbowyg.min.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/table/ui/trumbowyg.table.min.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/giphy/ui/trumbowyg.giphy.min.css">

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

    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4406607721782655"
            crossorigin="anonymous"></script>
</head>
<body class="font-sans antialiased">
<div class="min-h-screen bg-gray-900 dark:bg-gray-900">
    @include('layouts.navigation')

    @if (isset($header))
        <header x-data="{ scrolled: false }"
                x-init="window.addEventListener('scroll', () => scrolled = window.pageYOffset > 60)"
                :class="{ 'py-6 top-16': !scrolled, 'py-3 top-12': scrolled }"
                class="bg-gray-800 dark:bg-gray-800 shadow sticky z-40 transition-all duration-300 py-6 top-16">
            <div :class="{ 'text-3xl': !scrolled, 'text-xl': scrolled }" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endif

    <main class="container max-w-7xl mx-auto mt-24">
        {{ $slot }}
    </main>

    @include('layouts.footer')
</div>
<script src="//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
    window.jQuery || document.write('<script src="/js/vendor/jquery-3.3.1.min.js"><\/script>')
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/trumbowyg.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/giphy/trumbowyg.giphy.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/table/trumbowyg.table.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/upload/trumbowyg.upload.min.js"></script>
<script
    src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/pasteembed/trumbowyg.pasteembed.min.js"></script>


@if (isset($footer))
    {{ $footer }}
@endif
@stack('footer')
</body>
</html>
