@props(['seo' => null])

@if($seo)
    @if($seo->description)
        <meta name="description" content="{{ $seo->description }}">
    @endif

    @if($seo->keywords)
        <meta name="keywords" content="{{ $seo->keywords }}">
    @endif

    @if($seo->robots)
        <meta name="robots" content="{{ $seo->robots }}">
    @endif
@endif
