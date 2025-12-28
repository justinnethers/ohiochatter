<div class="bg-gradient-to-br from-steel-800 to-steel-850 text-white rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 p-4 md:p-6 mb-5 max-w-full overflow-hidden">
    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
        <span class="w-1 h-5 bg-accent-500 rounded-full"></span>
        <svg class="w-5 h-5 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        Poll
    </h3>

    @if($hasVoted)
        {{-- Results View (Answered State) --}}
        <div class="rounded-lg bg-steel-900/50 shadow-inner p-4 space-y-4">
            @foreach($poll->pollOptions as $option)
                <div>
                    <div class="flex flex-col sm:flex-row sm:justify-between mb-2">
                        <span class="break-words text-steel-100 font-medium">{{ $option->label }}</span>
                        <span class="text-sm text-accent-400 font-semibold">{{ $this->getPercentage($option) }}%</span>
                    </div>
                    <div class="w-full bg-steel-950 rounded-full overflow-hidden h-3">
                        <div class="bg-gradient-to-r from-accent-500 to-accent-600 rounded-full h-full transition-all duration-500 ease-out" style="width: {{ $this->getPercentage($option) }}%"></div>
                    </div>
                    <span class="text-xs text-steel-400 mt-1.5 block">{{ $option->votes->count() }} {{ \Illuminate\Support\Str::plural('vote', $option->votes->count()) }}</span>
                </div>
            @endforeach
        </div>
        <div class="mt-4 pt-3 border-t border-steel-700/50 text-sm text-steel-400 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            Total votes: <span class="font-semibold text-steel-200">{{ $this->voteCount }}</span>
        </div>
    @else
        {{-- Voting View (Unanswered State) --}}
        <div class="space-y-3">
            @if($poll->type === 'single')
                @foreach($poll->pollOptions as $option)
                    <label class="flex items-center cursor-pointer p-3 rounded-lg bg-steel-900/50 border border-steel-700/50 hover:border-accent-500/50 hover:bg-steel-900 transition-all duration-200 group">
                        <input type="radio"
                               wire:model.live="selectedOption"
                               name="poll_choice"
                               value="{{ $option->id }}"
                               class="w-4 h-4 text-accent-500 bg-steel-950 border-steel-600 focus:ring-2 focus:ring-accent-500/20 focus:ring-offset-0">
                        <span class="ml-3 text-steel-200 group-hover:text-steel-100 transition-colors">{{ $option->label }}</span>
                    </label>
                @endforeach
            @else
                <p class="text-sm text-steel-400 mb-2">Select all that apply:</p>
                @foreach($poll->pollOptions as $option)
                    <label class="flex items-center cursor-pointer p-3 rounded-lg bg-steel-900/50 border border-steel-700/50 hover:border-accent-500/50 hover:bg-steel-900 transition-all duration-200 group">
                        <input type="checkbox"
                               wire:model.live="selectedOptions"
                               value="{{ $option->id }}"
                               class="w-4 h-4 rounded text-accent-500 bg-steel-950 border-steel-600 focus:ring-2 focus:ring-accent-500/20 focus:ring-offset-0">
                        <span class="ml-3 break-words flex-1 text-steel-200 group-hover:text-steel-100 transition-colors">{{ $option->label }}</span>
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
