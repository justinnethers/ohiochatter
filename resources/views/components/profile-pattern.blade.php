@props(['username'])

@inject('patternService', 'App\Services\ProfilePatternService')

<div {{ $attributes }}>
    {!! $patternService->generateSvg($username) !!}
</div>
