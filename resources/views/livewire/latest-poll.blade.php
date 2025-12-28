<div>
    @if($thread && $poll)
        <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl border border-steel-700/50 p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                    <span class="w-1 h-4 bg-accent-500 rounded-full"></span>
                    Latest Poll
                </h3>
                @if($hasVoted || !auth()->check())
                    <span class="text-xs text-steel-400">{{ $this->voteCount }} {{ \Illuminate\Support\Str::plural('vote', $this->voteCount) }}</span>
                @endif
            </div>

            <a href="{{ $thread->path() }}" class="block text-steel-200 hover:text-white text-sm mb-3 line-clamp-2">
                {{ $poll->question ?? $thread->title }}
            </a>

            @if(!auth()->check())
                {{-- Guest view - show results but prompt to login --}}
                <div class="space-y-2 mb-3">
                    @foreach($poll->pollOptions->sortByDesc(fn($o) => $o->votes->count())->take(4) as $option)
                        @php $percentage = $this->getPercentage($option); @endphp
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-steel-300 truncate mr-2">{{ $option->label }}</span>
                                <span class="text-steel-400">{{ $percentage }}%</span>
                            </div>
                            <div class="w-full bg-steel-950 rounded-full h-1.5">
                                <div class="bg-accent-500 rounded-full h-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('login') }}" class="text-xs text-accent-400 hover:text-accent-300">
                    Login to vote &rarr;
                </a>
            @elseif($hasVoted)
                {{-- Voted view - show results --}}
                @php
                    $maxVotes = $poll->pollOptions->max(fn($o) => $o->votes->count());
                @endphp
                <div class="space-y-2 mb-3">
                    @foreach($poll->pollOptions->sortByDesc(fn($o) => $o->votes->count())->take(4) as $option)
                        @php
                            $percentage = $this->getPercentage($option);
                            $isLeader = $option->votes->count() === $maxVotes && $maxVotes > 0;
                        @endphp
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-steel-300 truncate mr-2 flex items-center gap-1">
                                    @if($isLeader)
                                        <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                                        </svg>
                                    @endif
                                    {{ $option->label }}
                                </span>
                                <span class="@if($isLeader) text-amber-400 @else text-steel-400 @endif">{{ $percentage }}%</span>
                            </div>
                            <div class="w-full bg-steel-950 rounded-full h-1.5">
                                <div class="@if($isLeader) bg-amber-400 @else bg-steel-500 @endif rounded-full h-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <a href="{{ $thread->path() }}" class="text-xs text-steel-400 hover:text-steel-300">
                    View full poll &rarr;
                </a>
            @else
                {{-- Voting view --}}
                <div class="space-y-2 mb-3">
                    @if($poll->type === 'single')
                        @foreach($poll->pollOptions->take(4) as $option)
                            <label class="group/option flex items-center cursor-pointer p-2 rounded-lg bg-steel-900/50 border border-steel-700/50 hover:border-accent-500/50 has-[:checked]:border-accent-500 transition-all text-sm">
                                <input type="radio"
                                       wire:model.live="selectedOption"
                                       name="poll_choice"
                                       value="{{ $option->id }}"
                                       class="sr-only">
                                <div class="relative w-4 h-4 shrink-0 rounded-full border-2 border-steel-500 bg-steel-950 group-has-[:checked]/option:border-accent-500 flex items-center justify-center">
                                    <div class="w-2 h-2 rounded-full bg-accent-500 scale-0 group-has-[:checked]/option:scale-100 transition-transform"></div>
                                </div>
                                <span class="ml-2 text-steel-200 group-has-[:checked]/option:text-white truncate">{{ $option->label }}</span>
                            </label>
                        @endforeach
                    @else
                        @foreach($poll->pollOptions->take(4) as $option)
                            <label class="group/option flex items-center cursor-pointer p-2 rounded-lg bg-steel-900/50 border border-steel-700/50 hover:border-accent-500/50 has-[:checked]:border-accent-500 transition-all text-sm">
                                <input type="checkbox"
                                       wire:model.live="selectedOptions"
                                       value="{{ $option->id }}"
                                       class="sr-only">
                                <div class="relative w-4 h-4 shrink-0 rounded border-2 border-steel-500 bg-steel-950 group-has-[:checked]/option:border-accent-500 group-has-[:checked]/option:bg-accent-500 flex items-center justify-center">
                                    <svg class="w-2.5 h-2.5 text-white scale-0 group-has-[:checked]/option:scale-100 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="ml-2 text-steel-200 group-has-[:checked]/option:text-white truncate">{{ $option->label }}</span>
                            </label>
                        @endforeach
                    @endif
                </div>

                @if($poll->pollOptions->count() > 4)
                    <p class="text-xs text-steel-500 mb-2">+ {{ $poll->pollOptions->count() - 4 }} more options</p>
                @endif

                <button
                    wire:click="vote"
                    type="button"
                    @if($this->isVoteButtonDisabled()) disabled @endif
                    class="w-full py-2 px-3 text-sm font-medium rounded-lg bg-accent-600 hover:bg-accent-500 disabled:bg-steel-700 disabled:text-steel-500 text-white transition-colors">
                    Vote
                </button>
            @endif
        </div>
    @endif
</div>
