@if (!$gameComplete && $puzzle->category && $remainingGuesses <= 3 || $gameComplete)
    <x-well>
        <h3 class="text-xs text-amber-400 uppercase font-semibold">Hints</h3>
        <div class="flex flex-wrap gap-4">
            <x-pill color="teal">{{ Str::title($puzzle->category) }}</x-pill>
            @if ($puzzle->hint && $remainingGuesses <= 2 || $gameComplete)
                <x-pill color="emerald">{{ $puzzle->hint }}</x-pill>
            @endif
            @if ($puzzle->hint_2 && $remainingGuesses <= 1 || $gameComplete)
                <x-pill color="green">{{ $puzzle->hint_2 }}</x-pill>
            @endif
        </div>

    </x-well>
@endif
