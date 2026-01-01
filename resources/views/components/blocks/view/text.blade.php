@props(['block', 'index' => 0, 'path' => '', 'nested' => false])

@if(!empty($block['data']['content']))
    <div class="prose {{ $nested ? 'prose-sm' : 'prose-lg' }} prose-invert max-w-none text-steel-300">
        {!! $block['data']['content'] !!}
    </div>
@endif
