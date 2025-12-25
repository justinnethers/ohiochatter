@props(['seo' => null, 'schemas' => []])

@php
    $allSchemas = $seo?->jsonLd ?? $schemas;
@endphp

@if(!empty($allSchemas))
    @foreach($allSchemas as $schema)
        <script type="application/ld+json">
            {!! json_encode(array_merge(['@context' => 'https://schema.org'], $schema), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
    @endforeach
@endif
