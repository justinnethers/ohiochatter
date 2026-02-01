<div
    x-data="{
        currentGuess: '',
        submittedGuess: '',
        isSubmitting: false,
        addLetter(letter) {
            if (this.isSubmitting || $wire.gameState.gameComplete) return;
            if (this.currentGuess.length < {{ $wordLength }}) {
                this.currentGuess += letter.toUpperCase();
            }
        },
        removeLetter() {
            if (this.isSubmitting || $wire.gameState.gameComplete) return;
            this.currentGuess = this.currentGuess.slice(0, -1);
        },
        async submitGuess() {
            if (this.isSubmitting || $wire.gameState.gameComplete) return;
            if (this.currentGuess.length !== {{ $wordLength }}) return;
            this.isSubmitting = true;
            this.submittedGuess = this.currentGuess;
            this.currentGuess = '';
            await $wire.submitGuess(this.submittedGuess);
            this.submittedGuess = '';
            this.isSubmitting = false;
        },
        handleKeydown(event) {
            if (event.metaKey || event.ctrlKey || event.altKey) return;
            if ($wire.gameState.gameComplete) return;

            const key = event.key.toUpperCase();

            if (key === 'ENTER') {
                event.preventDefault();
                this.submitGuess();
            } else if (key === 'BACKSPACE') {
                event.preventDefault();
                this.removeLetter();
            } else if (key.length === 1 && key.match(/[A-Z]/)) {
                event.preventDefault();
                this.addLetter(key);
            }
        }
    }"
    x-init="$el.focus()"
    @keydown.window="handleKeydown($event)"
    tabindex="0"
    class="outline-none"
>
    @if (!$word)
        <div class="bg-yellow-100 p-4 rounded-lg mb-4">
            <p class="text-yellow-800">{{ $errorMessage ?? 'No puzzle available today. Check back tomorrow!' }}</p>
        </div>
    @else
        <div class="max-w-lg mx-auto">
            {{-- Error Message --}}
            @if($errorMessage)
                <div
                    class="bg-red-900/50 border border-red-500 text-red-200 px-4 py-2 rounded mb-4 text-center error-message">
                    {{ $errorMessage }}
                </div>
            @endif

            {{-- Category Hint --}}
            @if($word->category)
                <div class="flex justify-center mb-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm
                        {{ $word->category === 'person' ? 'bg-blue-600/20 text-blue-300 border border-blue-500/30' : '' }}
                        {{ $word->category === 'place' ? 'bg-green-600/20 text-green-300 border border-green-500/30' : '' }}
                        {{ $word->category === 'thing' ? 'bg-purple-600/20 text-purple-300 border border-purple-500/30' : '' }}">
                        <span class="font-medium">Hint:</span>
                        <span class="font-bold uppercase">{{ $word->category }}</span>
                    </div>
                </div>
            @endif

            {{-- Game Grid --}}
            <div class="flex flex-col items-center gap-1 mb-4">
                @for($row = 0; $row < 6; $row++)
                    <div class="flex gap-1" wire:key="row-{{ $row }}">
                        @if(isset($gameState['guesses'][$row]))
                            {{-- Submitted guess row - rendered by server --}}
                            @for($col = 0; $col < $wordLength; $col++)
                                @php
                                    $guess = $gameState['guesses'][$row];
                                    $letter = strtoupper($guess[$col] ?? '');
                                    $status = $gameState['feedback'][$row][$col] ?? 'empty';
                                    $bgColor = match($status) {
                                        'correct' => 'bg-green-600',
                                        'present' => 'bg-yellow-500',
                                        'absent' => 'bg-gray-700',
                                        default => 'bg-gray-800',
                                    };
                                @endphp
                                <div
                                    wire:key="tile-{{ $row }}-{{ $col }}"
                                    class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center text-2xl font-bold text-white rounded {{ $bgColor }}"
                                    style="animation: flip 0.3s ease-in-out {{ $col * 0.1 }}s"
                                >{{ $letter }}</div>
                            @endfor
                        @elseif($row === count($gameState['guesses']) && !$gameState['gameComplete'])
                            {{-- Current input row - rendered by Alpine (no server round-trip) --}}
                            @for($col = 0; $col < $wordLength; $col++)
                                <div
                                    wire:key="input-{{ $col }}"
                                    class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center text-2xl font-bold text-white rounded transition-colors duration-100"
                                    :class="{
                                        'bg-gray-600 animate-pulse': isSubmitting && submittedGuess[{{ $col }}],
                                        'bg-gray-600': !isSubmitting && currentGuess.length > {{ $col }},
                                        'bg-gray-800': !isSubmitting && currentGuess.length <= {{ $col }} && !submittedGuess[{{ $col }}]
                                    }"
                                    x-text="isSubmitting ? (submittedGuess[{{ $col }}] || '') : (currentGuess[{{ $col }}] || '')"
                                ></div>
                            @endfor
                        @else
                            {{-- Empty future row --}}
                            @for($col = 0; $col < $wordLength; $col++)
                                <div
                                    wire:key="empty-{{ $row }}-{{ $col }}"
                                    class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center text-2xl font-bold text-white rounded bg-gray-800"
                                ></div>
                            @endfor
                        @endif
                    </div>
                @endfor
            </div>

            {{-- Virtual Keyboard --}}
            @if(!$gameState['gameComplete'])
                <div class="flex flex-col items-center gap-1">
                    @php
                        $keyboardRows = [
                            ['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P'],
                            ['A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L'],
                            ['ENTER', 'Z', 'X', 'C', 'V', 'B', 'N', 'M', 'BACK'],
                        ];
                    @endphp

                    @foreach($keyboardRows as $rowIndex => $row)
                        <div class="flex gap-1 justify-center">
                            @foreach($row as $key)
                                @php
                                    $isSpecial = in_array($key, ['ENTER', 'BACK']);
                                    $keyState = $keyboardState[$key] ?? 'unused';

                                    $bgColor = match($keyState) {
                                        'correct' => 'bg-green-600 hover:bg-green-500',
                                        'present' => 'bg-yellow-500 hover:bg-yellow-400',
                                        'absent' => 'bg-gray-700 hover:bg-gray-600',
                                        default => 'bg-gray-500 hover:bg-gray-400',
                                    };

                                    $width = $isSpecial ? 'min-w-[60px] md:min-w-[70px]' : 'min-w-[32px] md:min-w-[40px]';
                                @endphp
                                <button
                                    type="button"
                                    @if($key === 'ENTER')
                                        @click="submitGuess()"
                                    :disabled="isSubmitting"
                                    :class="isSubmitting ? 'opacity-50 cursor-not-allowed' : ''"
                                    @elseif($key === 'BACK')
                                        @click="removeLetter()"
                                    @else
                                        @click="addLetter('{{ $key }}')"
                                    @endif
                                    class="{{ $width }} h-12 md:h-14 px-1 md:px-2 rounded font-bold text-white text-sm md:text-base {{ $bgColor }} transition-all duration-150 flex items-center justify-center active:scale-95 active:brightness-90 shadow-sm hover:shadow-md touch-manipulation"
                                >
                                    @if($key === 'BACK')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z"/>
                                        </svg>
                                    @elseif($key === 'ENTER')
                                        <span x-show="!isSubmitting">ENTER</span>
                                        <svg x-show="isSubmitting" x-cloak class="animate-spin h-5 w-5"
                                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    @else
                                        {{ $key }}
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <p class="text-center text-gray-400 text-sm mt-4">
                    Guesses remaining: <span class="font-bold text-white">{{ $gameState['remainingGuesses'] }}</span>
                </p>
            @endif
        </div>

        {{-- Game Complete State - Answer Card (outside max-w-lg for full width) --}}
        @if($gameState['gameComplete'])
            <div id="answer-card"
                 class="relative overflow-hidden rounded-xl border-2 {{ $gameState['gameWon'] ? 'border-green-500/50 bg-gradient-to-br from-green-900/40 via-gray-900 to-gray-900' : 'border-red-500/50 bg-gradient-to-br from-red-900/40 via-gray-900 to-gray-900' }} p-6 text-center shadow-lg mt-6">
                {{-- Decorative background glow --}}
                <div
                    class="absolute inset-0 {{ $gameState['gameWon'] ? 'bg-green-500/5' : 'bg-red-500/5' }} blur-3xl"></div>

                <div class="relative">
                    {{-- Result icon --}}
                    @if($gameState['gameWon'])
                        <div
                            class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-500/20 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-400" viewBox="0 0 20 20"
                                 fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </div>
                    @else
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-500/20 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-400" viewBox="0 0 20 20"
                                 fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </div>
                    @endif

                    {{-- Result message --}}
                    <div
                        class="font-bold text-2xl {{ $gameState['gameWon'] ? 'text-green-400' : 'text-red-400' }} mb-2">
                        {{ $gameState['gameWon'] ? 'Excellent!' : 'Better luck tomorrow!' }}
                    </div>

                    @if($gameState['gameWon'])
                        <p class="text-gray-400 text-sm mb-4">You got it
                            in {{ count($gameState['guesses']) }} {{ count($gameState['guesses']) === 1 ? 'guess' : 'guesses' }}
                            !</p>
                    @endif

                    {{-- Answer tiles --}}
                    <div class="flex justify-center gap-1 mb-4">
                        @foreach(str_split($gameState['answer']) as $letter)
                            <div
                                class="w-10 h-10 md:w-12 md:h-12 bg-green-600 rounded flex items-center justify-center text-lg md:text-xl font-bold text-white shadow-lg">
                                {{ $letter }}
                            </div>
                        @endforeach
                    </div>

                    {{-- Word metadata (category and description) --}}
                    @if($word->hint || $word->category)
                        <div class="bg-gray-800/50 rounded-lg p-4 mb-4 text-left">
                            @if($word->category)
                                <div class="inline-block px-2 py-1 rounded text-xs font-bold uppercase tracking-wide mb-2
                                    {{ $word->category === 'person' ? 'bg-blue-600/30 text-blue-300' : '' }}
                                    {{ $word->category === 'place' ? 'bg-green-600/30 text-green-300' : '' }}
                                    {{ $word->category === 'thing' ? 'bg-purple-600/30 text-purple-300' : '' }}">
                                    {{ $word->category }}
                                </div>
                            @endif
                            @if($word->hint)
                                <p class="text-gray-300 text-sm">{{ $word->hint }}</p>
                            @endif
                        </div>
                    @endif

                    <p class="text-sm text-gray-400 mb-5">Come back tomorrow for a new puzzle!</p>

                    <x-primary-button type="button" x-on:click="shareResults({{ Js::from($gameState['shareText']) }})"
                                      class="inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z"/>
                        </svg>
                        Share Results
                    </x-primary-button>
                </div>
            </div>
        @endif

        {{-- Stats Row (outside max-w-lg for full width) --}}
        @if($gameState['gameComplete'] && $showWordStats && $wordStats)
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mt-6">
                {{-- Today's Stats Card --}}
                <div class="bg-gray-900 rounded-xl border-2 border-gray-700 p-6 shadow-lg">
                    <h3 class="text-lg font-bold text-white mb-4">Today's Stats</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center">
                        <div class="p-3 bg-gray-800 rounded-lg border border-gray-600">
                            <div class="text-2xl font-bold text-white">{{ $wordStats['totalPlayers'] }}</div>
                            <div class="text-xs text-gray-400 font-medium uppercase tracking-wide">Players</div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded-lg border border-gray-600">
                            <div class="text-2xl font-bold text-white">{{ $wordStats['completionRate'] }}%</div>
                            <div class="text-xs text-gray-400 font-medium uppercase tracking-wide">Solved</div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded-lg border border-gray-600">
                            <div class="text-2xl font-bold text-white">{{ $wordStats['averageGuesses'] }}</div>
                            <div class="text-xs text-gray-400 font-medium uppercase tracking-wide">Average</div>
                        </div>
                        <div class="p-3 bg-gray-800 rounded-lg border border-gray-600">
                            <div class="text-2xl font-bold text-white">{{ $wordStats['solvedCount'] }}</div>
                            <div class="text-xs text-gray-400 font-medium uppercase tracking-wide">Winners</div>
                        </div>
                    </div>

                    @if($wordStats['guessDistribution'])
                        <div class="mt-5 pt-4 border-t border-gray-700">
                            <h4 class="text-sm font-bold text-gray-300 mb-3 uppercase tracking-wide">Guess
                                Distribution</h4>
                            <div class="space-y-2">
                                @php
                                    $maxCount = max($wordStats['guessDistribution'] ?: [0]);
                                    $barColors = [
                                        1 => 'bg-green-500',
                                        2 => 'bg-green-600',
                                        3 => 'bg-yellow-500',
                                        4 => 'bg-orange-500',
                                        5 => 'bg-orange-600',
                                        6 => 'bg-red-500',
                                    ];
                                @endphp
                                @foreach($wordStats['guessDistribution'] as $guessNum => $count)
                                    @php $percentage = $maxCount > 0 ? ($count / $maxCount) * 100 : 0; @endphp
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 text-gray-400 text-sm font-bold">{{ $guessNum }}</div>
                                        <div class="flex-1">
                                            <div
                                                class="{{ $barColors[$guessNum] ?? 'bg-gray-500' }} text-white text-xs px-2 py-1 rounded text-right font-bold"
                                                style="width: max(28px, {{ $percentage }}%)"
                                            >
                                                {{ $count }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Your Stats Card --}}
                @auth
                    <div class="bg-gray-900 rounded-xl border-2 border-gray-700 p-6 shadow-lg">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-white">Your Stats</h3>
                            {{--                            <a href="{{ route('ohiowordle.stats') }}" class="text-blue-400 hover:text-blue-300 text-sm font-medium transition-colors">--}}
                            {{--                                View All →--}}
                            {{--                            </a>--}}
                        </div>
                        <livewire:ohio-wordle-user-stats/>
                    </div>
                @else
                    <div class="bg-gray-900 rounded-xl border-2 border-gray-700 p-6 shadow-lg">
                        <h3 class="text-lg font-bold text-white mb-4">Track Your Progress</h3>
                        <p class="text-gray-400 mb-4">Create a free account to save your stats, track streaks, and earn
                            achievements.</p>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <a href="{{ route('register') }}" class="flex-1">
                                <x-primary-button class="w-full justify-center">Create Free Account</x-primary-button>
                            </a>
                            <a href="{{ route('login') }}" class="flex-1">
                                <x-secondary-button class="w-full justify-center">Log In</x-secondary-button>
                            </a>
                        </div>
                    </div>
                @endauth
            </div>
        @endif
    @endif

    <style>
        @keyframes flip {
            0% {
                transform: rotateX(0);
            }
            50% {
                transform: rotateX(90deg);
            }
            100% {
                transform: rotateX(0);
            }
        }
    </style>

    <script>
        function shareResults(shareText) {
            if (!shareText) return;

            // Copy to clipboard using execCommand fallback (Safari-compatible)
            const textarea = document.createElement('textarea');
            textarea.value = shareText;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                showCopiedToast();
            } catch (e) {
                console.error(e);
            }
            document.body.removeChild(textarea);

            // Open share dialog
            if (navigator.share) {
                navigator.share({
                    title: 'Wordio',
                    text: shareText,
                }).catch(() => {
                });
            }
        }

        function showCopiedToast() {
            const existingToast = document.getElementById('share-toast');
            if (existingToast) existingToast.remove();

            const toast = document.createElement('div');
            toast.id = 'share-toast';
            toast.className = 'fixed bottom-4 left-1/2 -translate-x-1/2 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2 z-50 animate-fade-in';
            toast.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Copied to clipboard!
            `;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, 2000);
        }

        document.addEventListener('livewire:init', () => {
            Livewire.on('gameCompleted', () => {
                setTimeout(() => {
                    const answerCard = document.getElementById('answer-card');
                    if (answerCard) {
                        answerCard.scrollIntoView({behavior: 'smooth', block: 'center'});
                    }
                }, 100);
            });
        });
    </script>
</div>
