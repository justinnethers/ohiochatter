<div>
    @auth
        @if($pickem && !$hasSubmitted)
            <div
                x-data="{
                    dismissed: localStorage.getItem('pickem-alert-dismissed-{{ $pickem->id }}') === 'true',
                    dismiss() {
                        this.dismissed = true;
                        localStorage.setItem('pickem-alert-dismissed-{{ $pickem->id }}', 'true');
                    }
                }"
                x-show="!dismissed"
                class="container mx-auto pb-4"
            >
                <div class="bg-accent-600 text-white rounded-xl px-4 py-3 flex items-center justify-between gap-4">
                    <p class="text-sm">
                        You haven't submitted your picks for <a href="{{ route('pickem.show', $pickem) }}" class="font-semibold underline hover:no-underline">{{ $pickem->title }}</a>@if($pickem->picks_lock_at) — locks in {{ $pickem->picks_lock_at->diffForHumans(null, true) }}@endif
                    </p>
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ route('pickem.show', $pickem) }}" class="px-3 py-1 bg-white/20 hover:bg-white/30 rounded-full text-sm font-medium transition-colors">Make picks</a>
                        <button @click="dismiss()" type="button" class="text-white/70 hover:text-white text-xl leading-none">&times;</button>
                    </div>
                </div>
            </div>
        @endif
    @endauth
</div>