<div>
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-500/20 border border-green-500/50 rounded-lg text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-500/20 border border-red-500/50 rounded-lg text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <div class="space-y-4">
        @foreach($pickem->matchups as $matchup)
            <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-4 border border-steel-700/50">
                @if($matchup->description)
                    <div class="text-sm text-steel-400 mb-3">{{ $matchup->description }}</div>
                @endif

                <div class="grid grid-cols-2 gap-3">
                    {{-- Option A --}}
                    <button
                        wire:click="makePick({{ $matchup->id }}, 'a')"
                        @if($pickem->isLocked()) disabled @endif
                        class="relative p-4 rounded-lg border-2 transition-all duration-200 text-left
                            {{ ($picks[$matchup->id] ?? '') === 'a'
                                ? 'border-accent-500 bg-accent-500/20'
                                : 'border-steel-600 hover:border-steel-500 bg-steel-700/30' }}
                            {{ $pickem->isLocked() ? 'cursor-not-allowed opacity-75' : 'cursor-pointer' }}"
                    >
                        <span class="text-lg font-semibold text-white">{{ $matchup->option_a }}</span>
                        @if($matchup->winner === 'a')
                            <span class="absolute top-2 right-2 text-green-400">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </span>
                        @endif
                        @if($pickem->isLocked() && $matchup->winner)
                            <div class="mt-2 text-xs text-steel-400">
                                {{ $matchup->getPickCountForOption('a') }} picks
                            </div>
                        @endif
                    </button>

                    {{-- Option B --}}
                    <button
                        wire:click="makePick({{ $matchup->id }}, 'b')"
                        @if($pickem->isLocked()) disabled @endif
                        class="relative p-4 rounded-lg border-2 transition-all duration-200 text-left
                            {{ ($picks[$matchup->id] ?? '') === 'b'
                                ? 'border-accent-500 bg-accent-500/20'
                                : 'border-steel-600 hover:border-steel-500 bg-steel-700/30' }}
                            {{ $pickem->isLocked() ? 'cursor-not-allowed opacity-75' : 'cursor-pointer' }}"
                    >
                        <span class="text-lg font-semibold text-white">{{ $matchup->option_b }}</span>
                        @if($matchup->winner === 'b')
                            <span class="absolute top-2 right-2 text-green-400">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </span>
                        @endif
                        @if($pickem->isLocked() && $matchup->winner)
                            <div class="mt-2 text-xs text-steel-400">
                                {{ $matchup->getPickCountForOption('b') }} picks
                            </div>
                        @endif
                    </button>
                </div>

                {{-- Push indicator --}}
                @if($matchup->winner === 'push')
                    <div class="mt-3 text-center text-sm text-yellow-400">
                        Push (Tie) - All picks count as correct
                    </div>
                @endif

                {{-- Confidence selector for confidence mode --}}
                @if($pickem->scoring_type === 'confidence' && !$pickem->isLocked())
                    <div class="mt-3 flex items-center gap-2">
                        <label class="text-sm text-steel-400">Confidence:</label>
                        <select
                            wire:change="setConfidence({{ $matchup->id }}, $event.target.value)"
                            class="bg-steel-700 border-steel-600 rounded text-sm text-white px-3 py-1.5 focus:border-accent-500 focus:ring-accent-500"
                        >
                            <option value="">Select points...</option>
                            @for($i = 1; $i <= $pickem->matchups->count(); $i++)
                                <option
                                    value="{{ $i }}"
                                    {{ ($confidences[$matchup->id] ?? '') == $i ? 'selected' : '' }}
                                    {{ in_array($i, $this->usedConfidences) && ($confidences[$matchup->id] ?? '') != $i ? 'disabled' : '' }}
                                >{{ $i }} point{{ $i > 1 ? 's' : '' }}</option>
                            @endfor
                        </select>
                        @if(isset($confidences[$matchup->id]))
                            <span class="text-accent-400 font-semibold">{{ $confidences[$matchup->id] }} pts</span>
                        @endif
                    </div>
                @endif

                {{-- Show points for weighted mode --}}
                @if($pickem->scoring_type === 'weighted')
                    <div class="mt-2 text-sm text-steel-400">
                        Worth: <span class="text-accent-400 font-semibold">{{ $matchup->points }} point{{ $matchup->points > 1 ? 's' : '' }}</span>
                    </div>
                @endif

                {{-- Show confidence for confidence mode when locked --}}
                @if($pickem->scoring_type === 'confidence' && $pickem->isLocked() && isset($confidences[$matchup->id]))
                    <div class="mt-2 text-sm text-steel-400">
                        Your confidence: <span class="text-accent-400 font-semibold">{{ $confidences[$matchup->id] }} point{{ $confidences[$matchup->id] > 1 ? 's' : '' }}</span>
                    </div>
                @endif
            </div>
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
        <div class="mt-6 p-4 bg-gradient-to-r from-accent-500/20 to-accent-600/20 border border-accent-500/30 rounded-lg">
            <div class="text-center">
                <span class="text-steel-300">Your Score:</span>
                <span class="text-2xl font-bold text-accent-400 ml-2">{{ $userScore }}</span>
                <span class="text-steel-400">/ {{ $maxScore }}</span>
            </div>
        </div>
    @endif
</div>
