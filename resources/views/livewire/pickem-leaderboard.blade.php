<div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl border border-steel-700/50 p-4 md:p-6">
    <h3 class="text-lg font-semibold text-white mb-4">Leaderboard</h3>

    @if($leaderboard->isEmpty())
        <p class="text-steel-400">No picks submitted yet.</p>
    @else
        <div class="space-y-2">
            @foreach($leaderboard as $index => $entry)
                <div
                    class="flex items-center gap-3 py-2 {{ auth()->check() && $entry['user']->id === auth()->id() ? 'bg-accent-500/10 -mx-2 px-2 rounded-lg' : '' }}">
                    @if($index === 0)
                        <span class="w-7 text-center text-yellow-400 font-bold">1</span>
                    @elseif($index === 1)
                        <span class="w-7 text-center text-gray-300 font-bold">2</span>
                    @elseif($index === 2)
                        <span class="w-7 text-center text-amber-500 font-bold">3</span>
                    @else
                        <span class="w-7 text-center text-white font-medium">{{ $index + 1 }}</span>
                    @endif
                    <img src="{{ $entry['user']->avatar_path }}" alt="" class="w-8 h-8 rounded-full">
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('profile.show', $entry['user']->username) }}"
                           class="font-medium text-white hover:text-accent-400 transition-colors truncate block">
                            {{ $entry['user']->username }}
                        </a>
                    </div>
                    <div class="text-right">
                        <span class="font-bold text-accent-400">{{ $entry['score'] }}</span>
                        <span class="text-steel-500 text-sm">/{{ $entry['max'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
