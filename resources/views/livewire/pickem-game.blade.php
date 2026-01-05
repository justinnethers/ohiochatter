<div>
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-emerald-500/20 border border-emerald-500/50 rounded-xl text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-rose-500/20 border border-rose-500/50 rounded-xl text-rose-400">
            {{ session('error') }}
        </div>
    @endif

    @if($pickem->scoring_type === 'confidence' && !$pickem->isLocked())
        <p class="text-sm text-steel-400 mb-3">Assign confidence points to each matchup. Click a selected value again to deselect it.</p>
    @endif

    <div class="space-y-3">
        @foreach($pickem->matchups as $matchup)
            @php
                $userPick = $picks[$matchup->id] ?? null;
                $isCorrect = $matchup->winner && $userPick === $matchup->winner;
                $isWrong = $matchup->winner && $userPick && $userPick !== $matchup->winner && $matchup->winner !== 'push';
            @endphp
            <article class="group bg-gradient-to-br from-steel-800 to-steel-850 p-3 md:p-4 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 relative overflow-hidden">
                {{-- Left edge indicator for pick state --}}
                @if($isCorrect)
                    <div class="absolute left-0 top-4 bottom-4 w-1 bg-emerald-500 rounded-r-full"></div>
                @elseif($isWrong)
                    <div class="absolute left-0 top-4 bottom-4 w-1 bg-rose-500 rounded-r-full"></div>
                @elseif($userPick)
                    <div class="absolute left-0 top-4 bottom-4 w-1 bg-accent-500 rounded-r-full"></div>
                @endif

                {{-- Description if present --}}
                @if($matchup->description)
                    <div class="text-sm text-steel-400 mb-2">{{ $matchup->description }}</div>
                @endif

                {{-- Main selection area --}}
                <div class="flex flex-col md:flex-row md:items-center gap-3">
                    {{-- Option buttons --}}
                    <div class="flex items-center gap-2 flex-1">
                        <button
                            wire:click="makePick({{ $matchup->id }}, 'a')"
                            @if($pickem->isLocked()) disabled @endif
                            class="flex-1 md:flex-none px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200
                                {{ $userPick === 'a'
                                    ? 'bg-accent-500 text-white shadow-lg shadow-accent-500/25'
                                    : 'bg-steel-900/70 text-steel-300 border border-steel-700/50 hover:border-steel-600 hover:text-white' }}
                                {{ $pickem->isLocked() ? 'cursor-not-allowed opacity-60' : '' }}
                                {{ $matchup->winner === 'a' ? 'ring-2 ring-emerald-500 ring-offset-2 ring-offset-steel-800' : '' }}"
                        >{{ $matchup->option_a }}</button>

                        <span class="text-steel-500 text-sm px-1">vs</span>

                        <button
                            wire:click="makePick({{ $matchup->id }}, 'b')"
                            @if($pickem->isLocked()) disabled @endif
                            class="flex-1 md:flex-none px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200
                                {{ $userPick === 'b'
                                    ? 'bg-accent-500 text-white shadow-lg shadow-accent-500/25'
                                    : 'bg-steel-900/70 text-steel-300 border border-steel-700/50 hover:border-steel-600 hover:text-white' }}
                                {{ $pickem->isLocked() ? 'cursor-not-allowed opacity-60' : '' }}
                                {{ $matchup->winner === 'b' ? 'ring-2 ring-emerald-500 ring-offset-2 ring-offset-steel-800' : '' }}"
                        >{{ $matchup->option_b }}</button>
                    </div>

                    {{-- Right side: confidence/points/status --}}
                    <div class="flex items-center gap-3">
                        {{-- Confidence points for confidence mode --}}
                        @if($pickem->scoring_type === 'confidence')
                            @if(!$pickem->isLocked())
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-xs text-steel-500">Points</span>
                                    <div class="flex items-center gap-1 bg-steel-900/50 rounded-full px-2 py-1 shadow-inner">
                                    @for($i = 1; $i <= $pickem->matchups->count(); $i++)
                                        @php
                                            $isSelected = ($confidences[$matchup->id] ?? '') == $i;
                                            $isUsed = in_array($i, $this->usedConfidences) && !$isSelected;
                                        @endphp
                                        <button
                                            wire:click="setConfidence({{ $matchup->id }}, {{ $i }})"
                                            @if($isUsed) disabled @endif
                                            class="w-6 h-6 rounded-full text-xs font-bold transition-all duration-200
                                                {{ $isSelected
                                                    ? 'bg-accent-500 text-white shadow-md'
                                                    : ($isUsed
                                                        ? 'bg-steel-800 text-steel-600 cursor-not-allowed'
                                                        : 'bg-steel-700 text-steel-300 hover:bg-steel-600 hover:text-white') }}"
                                        >{{ $i }}</button>
                                    @endfor
                                    </div>
                                </div>
                            @else
                                @if(isset($confidences[$matchup->id]))
                                    <div class="inline-flex items-center gap-1 px-3 py-1 bg-steel-900/70 rounded-full border border-steel-700/50">
                                        <span class="font-bold text-accent-400">{{ $confidences[$matchup->id] }}</span>
                                        <span class="text-sm text-steel-400">pts</span>
                                    </div>
                                @endif
                            @endif
                        @endif

                        {{-- Points for weighted mode --}}
                        @if($pickem->scoring_type === 'weighted')
                            <div class="inline-flex items-center gap-1 px-3 py-1 bg-steel-900/70 rounded-full border border-steel-700/50">
                                <span class="font-bold text-accent-400">{{ $matchup->points }}</span>
                                <span class="text-sm text-steel-400">{{ Str::plural('pt', $matchup->points) }}</span>
                            </div>
                        @endif

                        {{-- Push indicator --}}
                        @if($matchup->winner === 'push')
                            <span class="inline-flex items-center px-3 py-1 bg-yellow-500/20 rounded-full text-yellow-400 text-xs font-medium">
                                Push
                            </span>
                        @endif

                        {{-- Pick counts when locked --}}
                        @if($pickem->isLocked() && $matchup->winner)
                            <div class="inline-flex items-center gap-1 px-2 py-1 bg-steel-900/70 rounded-full border border-steel-700/50 text-xs text-steel-400">
                                <span class="{{ $matchup->winner === 'a' ? 'text-emerald-400 font-bold' : '' }}">{{ $matchup->getPickCountForOption('a') }}</span>
                                <span>-</span>
                                <span class="{{ $matchup->winner === 'b' ? 'text-emerald-400 font-bold' : '' }}">{{ $matchup->getPickCountForOption('b') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    {{-- Submit Button --}}
    @if(!$pickem->isLocked())
        @auth
            <div class="mt-6">
                <button
                    wire:click="submitPicks"
                    wire:loading.attr="disabled"
                    class="w-full md:w-auto px-6 py-3 bg-gradient-to-r from-accent-500 to-accent-600 rounded-lg text-white font-semibold shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 hover:from-accent-600 hover:to-accent-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove>{{ $hasSubmitted ? 'Update Picks' : 'Submit Picks' }}</span>
                    <span wire:loading>Saving...</span>
                </button>
            </div>
        @else
            <div class="mt-6 p-4 bg-steel-700/50 rounded-lg text-center">
                <p class="text-steel-300">
                    <a href="{{ route('login') }}" class="text-accent-400 hover:text-accent-300">Log in</a>
                    to make your picks
                </p>
            </div>
        @endauth
    @endif

    {{-- Show user's score when locked --}}
    @if($pickem->isLocked() && auth()->check())
        @php
            $userScore = $pickem->getUserScore(auth()->user());
            $maxScore = $pickem->getMaxPossibleScore();
        @endphp
        <div class="mt-6 p-4 bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl border border-accent-500/30 shadow-lg shadow-black/20">
            <div class="text-center">
                <span class="text-steel-300">Your Score:</span>
                <span class="text-2xl font-bold text-accent-400 ml-2">{{ $userScore }}</span>
                <span class="text-steel-400">/ {{ $maxScore }}</span>
            </div>
        </div>
    @endif
</div>
