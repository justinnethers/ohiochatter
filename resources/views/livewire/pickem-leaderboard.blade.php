<div>
    <h3 class="text-lg font-semibold text-white mb-4">Leaderboard</h3>

    @if($leaderboard->isEmpty())
        <p class="text-steel-400">No picks submitted yet.</p>
    @else
        <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl border border-steel-700/50 overflow-hidden">
            <table class="w-full">
                <thead class="bg-steel-700/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-steel-400 uppercase tracking-wider">Rank</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-steel-400 uppercase tracking-wider">Player</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-steel-400 uppercase tracking-wider">Score</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-steel-700/50">
                    @foreach($leaderboard as $index => $entry)
                        <tr class="{{ $index < 3 ? 'bg-steel-700/20' : '' }} {{ auth()->check() && $entry['user']->id === auth()->id() ? 'bg-accent-500/10' : '' }}">
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($index === 0)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-500/20 text-yellow-400 font-bold">1</span>
                                @elseif($index === 1)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-400/20 text-gray-300 font-bold">2</span>
                                @elseif($index === 2)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-600/20 text-amber-500 font-bold">3</span>
                                @else
                                    <span class="inline-flex items-center justify-center w-8 h-8 text-white font-medium">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('profile.show', $entry['user']->username) }}" class="flex items-center gap-3 hover:text-accent-400 transition-colors">
                                    <img src="{{ $entry['user']->avatar_path }}" alt="" class="w-8 h-8 rounded-full bg-steel-600">
                                    <span class="font-medium text-white">{{ $entry['user']->username }}</span>
                                    @if(auth()->check() && $entry['user']->id === auth()->id())
                                        <span class="text-xs text-accent-400">(You)</span>
                                    @endif
                                </a>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="font-bold text-accent-400">{{ $entry['score'] }}</span>
                                <span class="text-steel-500">/ {{ $entry['max'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
