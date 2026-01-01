@props([
    'blocks' => [],
    'mode' => 'view', // 'view' or 'edit'
    'pathPrefix' => '', // For wire:model bindings in edit mode
    'nested' => false, // Whether this is a nested block render
])

@if(!empty($blocks))
    <div class="{{ $nested ? 'space-y-3' : 'space-y-6' }}">
        @foreach($blocks as $index => $block)
            @php
                $blockPath = $pathPrefix ? "{$pathPrefix}.{$index}" : $index;
                $componentName = "blocks.{$mode}.{$block['type']}";
            @endphp

            @if(View::exists("components.{$componentName}"))
                <x-dynamic-component
                    :component="$componentName"
                    :block="$block"
                    :index="$index"
                    :path="$blockPath"
                    :nested="$nested"
                />
            @endif
        @endforeach
    </div>
@endif
