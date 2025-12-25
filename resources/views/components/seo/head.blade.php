{{--
    Master SEO component that includes all SEO tags
    Usage: <x-seo.head :seo="$seo" />
--}}
@props(['seo' => null])

@if($seo)
    <x-seo.meta-tags :seo="$seo" />
    <x-seo.open-graph :seo="$seo" />
    <x-seo.twitter-card :seo="$seo" />
    <x-seo.canonical :seo="$seo" />
    <x-seo.json-ld :seo="$seo" />
@endif
