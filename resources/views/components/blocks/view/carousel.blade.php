@props(['block', 'index' => 0, 'path' => '', 'nested' => false])

@if(!empty($block['data']['images']))
    <div class="{{ $nested ? '' : 'my-6' }} overflow-x-auto">
        <div class="flex gap-{{ $nested ? '3' : '4' }} pb-{{ $nested ? '2' : '4' }}">
            @foreach($block['data']['images'] as $image)
                <img src="{{ Storage::url($image['path']) }}"
                    alt="{{ $image['alt'] ?? '' }}"
                    class="h-{{ $nested ? '40' : '64' }} w-auto rounded-{{ $nested ? 'lg' : 'xl' }} shrink-0">
            @endforeach
        </div>
    </div>
@endif
