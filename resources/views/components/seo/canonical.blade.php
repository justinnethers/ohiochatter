@props(['seo' => null, 'url' => null])

@php
    $canonicalUrl = $url ?? ($seo?->canonical ?? null);
@endphp

@if($canonicalUrl)
    <link rel="canonical" href="{{ $canonicalUrl }}">
@endif
