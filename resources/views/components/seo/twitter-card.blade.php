@props(['seo' => null])

@if($seo)
    <meta name="twitter:card" content="{{ $seo->twitterCard ?? 'summary_large_image' }}">

    @if($seo->twitterTitle ?? $seo->ogTitle ?? $seo->title)
        <meta name="twitter:title" content="{{ $seo->twitterTitle ?? $seo->ogTitle ?? $seo->title }}">
    @endif

    @if($seo->twitterDescription ?? $seo->ogDescription ?? $seo->description)
        <meta name="twitter:description" content="{{ $seo->twitterDescription ?? $seo->ogDescription ?? $seo->description }}">
    @endif

    @if($seo->twitterImage ?? $seo->ogImage)
        <meta name="twitter:image" content="{{ $seo->twitterImage ?? $seo->ogImage }}">
    @endif
@endif
