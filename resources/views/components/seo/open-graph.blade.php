@props(['seo' => null])

@if($seo)
    <meta property="og:site_name" content="OhioChatter">
    <meta property="og:locale" content="en_US">

    @if($seo->ogTitle ?? $seo->title)
        <meta property="og:title" content="{{ $seo->ogTitle ?? $seo->title }}">
    @endif

    @if($seo->ogDescription ?? $seo->description)
        <meta property="og:description" content="{{ $seo->ogDescription ?? $seo->description }}">
    @endif

    @if($seo->ogType)
        <meta property="og:type" content="{{ $seo->ogType }}">
    @endif

    @if($seo->ogUrl ?? $seo->canonical)
        <meta property="og:url" content="{{ $seo->ogUrl ?? $seo->canonical }}">
    @endif

    @if($seo->ogImage)
        <meta property="og:image" content="{{ $seo->ogImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @endif
@endif
