<div>
    @auth
        @if($word && !$hasPlayed)
            <div
                x-data="{
                    dismissed: localStorage.getItem('wordio-alert-dismissed-{{ $word->publish_date }}') === 'true',
                    dismiss() {
                        this.dismissed = true;
                        localStorage.setItem('wordio-alert-dismissed-{{ $word->publish_date }}', 'true');
                    }
                }"
                x-show="!dismissed"
                class="container mx-auto pb-4"
            >
                <div class="bg-accent-600 text-white rounded-xl px-4 py-3 flex items-center justify-between gap-4">
                    <p class="text-sm">
                        Today's <a href="{{ route('ohiowordle.index') }}" class="font-semibold underline hover:no-underline">Wordio</a> is ready — can you guess the {{ $word->word_length }}-letter Ohio word?
                    </p>
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ route('ohiowordle.index') }}" class="px-3 py-1 bg-white/20 hover:bg-white/30 rounded-full text-sm font-medium transition-colors">Play now</a>
                        <button @click="dismiss()" type="button" class="text-white/70 hover:text-white text-xl leading-none">&times;</button>
                    </div>
                </div>
            </div>
        @endif
    @endauth
</div>
