<div class="bg-gradient-to-br from-steel-800 to-steel-850 text-white rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 p-4 md:p-6 mb-5 max-w-full overflow-hidden">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
            <span class="w-1 h-5 bg-accent-500 rounded-full"></span>
            Poll
        </h3>
        @if($hasVoted)
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-steel-900/70 rounded-full border border-steel-700/50">
                <svg class="w-4 h-4 text-steel-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <span class="font-bold text-steel-100">{{ $this->voteCount }}</span>
                <span class="text-sm text-steel-400">{{ \Illuminate\Support\Str::plural('vote', $this->voteCount) }}</span>
            </div>
        @endif
    </div>

    @if($hasVoted)
        {{-- Results View (Answered State) - Sorted by votes --}}
        <div class="rounded-lg bg-steel-900/50 shadow-inner p-4 space-y-4">
            @foreach($this->rankedOptions as $index => $option)
                @php
                    $rank = $index + 1;
                    $percentage = $this->getPercentage($option);
                    // Only show medal colors if there are actual votes
                    $hasVotes = $option->votes->count() > 0;
                @endphp
                <div class="relative">
                    <div class="flex flex-col sm:flex-row sm:justify-between mb-2">
                        <span class="break-words text-steel-100 font-medium flex items-center gap-2">
                            @if($rank === 1 && $hasVotes)
                                <span class="text-amber-400">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                                    </svg>
                                </span>
                            @endif
                            {{ $option->label }}
                        </span>
                        <span class="text-sm font-semibold @if($rank === 1 && $hasVotes) text-amber-400 @elseif($rank === 2 && $hasVotes) text-slate-300 @elseif($rank === 3 && $hasVotes) text-orange-400 @else text-steel-400 @endif">
                            {{ $percentage }}%
                        </span>
                    </div>
                    <div class="w-full bg-steel-950 rounded-full overflow-hidden h-3">
                        @if($rank === 1 && $hasVotes)
                            <div class="bg-gradient-to-r from-amber-400 to-yellow-500 rounded-full h-full transition-all duration-500 ease-out" style="width: {{ $percentage }}%"></div>
                        @elseif($rank === 2 && $hasVotes)
                            <div class="bg-gradient-to-r from-slate-300 to-slate-400 rounded-full h-full transition-all duration-500 ease-out" style="width: {{ $percentage }}%"></div>
                        @elseif($rank === 3 && $hasVotes)
                            <div class="bg-gradient-to-r from-orange-400 to-orange-500 rounded-full h-full transition-all duration-500 ease-out" style="width: {{ $percentage }}%"></div>
                        @else
                            <div class="bg-gradient-to-r from-steel-500 to-steel-600 rounded-full h-full transition-all duration-500 ease-out" style="width: {{ $percentage }}%"></div>
                        @endif
                    </div>
                    <span class="text-sm text-steel-400 mt-1.5 block"><span class="font-bold text-steel-300">{{ $option->votes->count() }}</span> {{ \Illuminate\Support\Str::plural('vote', $option->votes->count()) }}</span>
                </div>
            @endforeach
        </div>
    @else
        {{-- Voting View (Unanswered State) --}}
        <div class="space-y-3">
            @if($poll->type === 'single')
                @foreach($poll->pollOptions as $option)
                    <label class="group/option relative flex items-center cursor-pointer p-3 rounded-lg bg-steel-900/50 border border-steel-700/50 hover:border-accent-500/50 hover:bg-steel-900 has-[:checked]:border-accent-500 has-[:checked]:bg-steel-900 has-[:checked]:shadow-[0_0_15px_-3px] has-[:checked]:shadow-accent-500/30 transition-all duration-200">
                        <input type="radio"
                               wire:model.live="selectedOption"
                               name="poll_choice"
                               value="{{ $option->id }}"
                               class="sr-only">
                        {{-- Custom radio indicator --}}
                        <div class="relative w-5 h-5 shrink-0 rounded-full border-2 border-steel-500 bg-steel-950 group-has-[:checked]/option:border-accent-500 transition-all duration-200 flex items-center justify-center">
                            {{-- Inner dot with scale animation --}}
                            <div class="w-2.5 h-2.5 rounded-full bg-gradient-to-br from-accent-400 to-accent-600 scale-0 group-has-[:checked]/option:scale-100 transition-transform duration-200 ease-out"></div>
                        </div>
                        <span class="ml-3 text-steel-200 group-hover/option:text-steel-100 group-has-[:checked]/option:text-white transition-colors">{{ $option->label }}</span>
                    </label>
                @endforeach
            @else
                <p class="text-sm text-steel-400 mb-2">Select all that apply:</p>
                @foreach($poll->pollOptions as $option)
                    <label class="group/option relative flex items-center cursor-pointer p-3 rounded-lg bg-steel-900/50 border border-steel-700/50 hover:border-accent-500/50 hover:bg-steel-900 has-[:checked]:border-accent-500 has-[:checked]:bg-steel-900 has-[:checked]:shadow-[0_0_15px_-3px] has-[:checked]:shadow-accent-500/30 transition-all duration-200">
                        <input type="checkbox"
                               wire:model.live="selectedOptions"
                               value="{{ $option->id }}"
                               class="sr-only">
                        {{-- Custom checkbox indicator --}}
                        <div class="relative w-5 h-5 shrink-0 rounded border-2 border-steel-500 bg-steel-950 group-has-[:checked]/option:border-accent-500 group-has-[:checked]/option:bg-gradient-to-br group-has-[:checked]/option:from-accent-500 group-has-[:checked]/option:to-accent-600 transition-all duration-200 flex items-center justify-center">
                            {{-- Checkmark icon --}}
                            <svg class="w-3 h-3 text-white scale-0 group-has-[:checked]/option:scale-100 transition-transform duration-200 ease-out" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span class="ml-3 break-words flex-1 text-steel-200 group-hover/option:text-steel-100 group-has-[:checked]/option:text-white transition-colors">{{ $option->label }}</span>
                    </label>
                @endforeach
            @endif

            <div class="pt-4">
                <x-primary-button
                    wire:click="vote"
                    type="button"
                    :disabled="$poll->type === 'single' ? empty($selectedOption) : empty($selectedOptions)">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Submit Vote
                </x-primary-button>
            </div>
        </div>
    @endif
</div>
