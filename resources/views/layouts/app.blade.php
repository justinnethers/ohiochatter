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
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-1KYZYV7374"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-1KYZYV7374');
</script>
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
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.30.0/dist/trumbowyg.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.30.0/dist/plugins/giphy/trumbowyg.giphy.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.30.0/dist/plugins/table/trumbowyg.table.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.30.0/dist/plugins/upload/trumbowyg.upload.min.js" crossorigin="anonymous"></script>

<script>

    !function(e){"use strict";var t={enabled:!0,endpoint:"https://noembed.com/embed?nowrap=on"};e.extend(!0,e.trumbowyg,{plugins:{pasteEmbed:{init:function(n){n.o.plugins.pasteEmbed=e.extend(!0,{},t,n.o.plugins.pasteEmbed||{}),Array.isArray(n.o.plugins.pasteEmbed.endpoints)&&(n.o.plugins.pasteEmbed.endpoint=n.o.plugins.pasteEmbed.endpoints[0]),n.o.plugins.pasteEmbed.enabled&&n.pasteHandlers.push((function(t){try{
// Get the pasted text
                    var a=(t.originalEvent||t).clipboardData.getData("Text");
                    if(!a.startsWith("http"))return;

// Convert x.com URLs to twitter.com before any other processing
                    a = a.replace(/https?:\/\/(www\.)?x\.com/g, 'https://twitter.com');

                    var s=n.o.plugins.pasteEmbed.endpoint;
                    t.stopPropagation(),t.preventDefault();
                    var i=new URL(s);
                    i.searchParams.append("url",a.trim()),
                        fetch(i,{method:"GET",cache:"no-cache",signal:AbortSignal.timeout(2e3)})
                            .then((e=>e.json().then((e=>e.html))))
                            .catch((()=>{}))
                            .then((t=>{
                                void 0===t&&(t=e("<a>",{href:a,text:a})[0].outerHTML),
                                    n.execCmd("insertHTML",t)
                            }))}catch(e){}}))}}}})}(jQuery);

</script>

@if (isset($footer))
    {{ $footer }}
@endif
@stack('footer')
</body>
</html>
