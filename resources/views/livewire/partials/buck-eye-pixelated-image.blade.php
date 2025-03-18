<!-- Transparent overlay div to prevent image selection -->
<div class="col-span-3 relative rounded-xl shadow-sm ring-1 bg-gray-900 ring-white/10 mb-2 md:mb-4">
    <div class="absolute inset-0 w-full h-full z-10"
         style="pointer-events: auto; user-select: none; -webkit-user-select: none;"
         oncontextmenu="return false;">
    </div>
    <img
        src="{{ $imageUrl }}"
        alt="Pixelated Ohio Item"
        class="w-full select-none pointer-events-none rounded-xl"
        style="filter: blur({{ max(0, $pixelationLevel * 4) }}px); image-rendering: pixelated; -webkit-user-select: none; user-select: none;"
        wire:key="image-{{ $pixelationLevel }}"
        draggable="false"
    >
    @if($gameComplete && ($puzzle->image_attribution || $puzzle->link))
        <div
            class="absolute bottom-0 h-auto p-6 w-full z-20 break-all bg-gradient-to-t from-gray-900/95 to-gray-900/30 rounded-b-xl">
            @if($puzzle->image_attribution)
                <div class="text-base text-gray-100 mb-2">
                    <div
                        class="font-semibold text-xs uppercase">Image Attribution
                    </div>
                    <div class="text-xs">{!! $puzzle->image_attribution !!}</div>
                </div>
            @endif
            @if($puzzle->link)
                <div class="text-sm">
                    <a href="{{ $puzzle->link }}" target="_blank"
                       class="text-blue-300 hover:text-blue-400 underline">
                        Learn more about {{ $puzzle->answer }}
                    </a>
                </div>
            @endif
        </div>
    @endif
</div>
