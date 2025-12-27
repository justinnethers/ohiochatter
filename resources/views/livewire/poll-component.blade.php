<div class="bg-gradient-to-br from-steel-800 to-steel-850 text-white rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 p-4 sm:p-6 mb-5 max-w-full overflow-hidden">
    <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
        <span class="w-1 h-5 bg-amber-400 rounded-full"></span>
        Poll
    </h3>

    @if($hasVoted)
        @foreach($poll->pollOptions as $option)
            <div class="mb-4">
                <div class="flex flex-col sm:flex-row sm:justify-between mb-1.5">
                    <span class="break-words text-lg text-steel-100 mr-2">{{ $option->label }}</span>
                    <span class="text-sm text-accent-400 font-semibold">{{ $this->getPercentage($option) }}%</span>
                </div>
                <div class="w-full bg-steel-900 rounded-full overflow-hidden">
                    <div class="bg-gradient-to-r from-accent-500 to-accent-600 rounded-full h-2.5 transition-all duration-500" style="width: {{ $this->getPercentage($option) }}%"></div>
                </div>
                <span class="text-xs sm:text-sm text-steel-400 mt-1">{{ $option->votes->count() }} {{ \Illuminate\Support\Str::plural('vote', $option->votes->count()) }}</span>
            </div>
        @endforeach
        <div class="mt-4 pt-3 border-t border-steel-700/50 text-sm text-steel-400">
            Total votes: <span class="font-semibold text-steel-200">{{ $this->voteCount }}</span>
        </div>
    @else
        <div class="space-y-3">
            @if($poll->type === 'single')
                @foreach($poll->pollOptions as $option)
                    <div>
                        <label class="flex items-center cursor-pointer p-3 rounded-lg bg-steel-900/50 border border-steel-700/50 hover:border-steel-600 hover:bg-steel-900 transition-all duration-200">
                            <input type="radio"
                                   wire:model.live="selectedOption"
                                   name="poll_choice"
                                   value="{{ $option->id }}"
                                   class="form-radio text-accent-500 bg-steel-800 border-steel-600 focus:ring-accent-500/20 focus:ring-offset-steel-900">
                            <span class="ml-3 text-steel-200">{{ $option->label }}</span>
                        </label>
                    </div>
                @endforeach
            @else
                @foreach($poll->pollOptions as $option)
                    <div>
                        <label class="flex items-center cursor-pointer p-3 rounded-lg bg-steel-900/50 border border-steel-700/50 hover:border-steel-600 hover:bg-steel-900 transition-all duration-200">
                            <input type="checkbox"
                                   wire:model.live="selectedOptions"
                                   value="{{ $option->id }}"
                                   class="form-checkbox h-4 w-4 text-accent-500 bg-steel-800 border-steel-600 rounded focus:ring-accent-500/20 focus:ring-offset-steel-900">
                            <span class="ml-3 break-words flex-1 text-steel-200">{{ $option->label }}</span>
                        </label>
                    </div>
                @endforeach
            @endif

            <div class="pt-3">
                <button
                    wire:click="vote"
                    type="button"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-accent-500 to-accent-600 rounded-lg text-white font-semibold shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 hover:from-accent-600 hover:to-accent-700 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                    @disabled($poll->type === 'single' ? empty($selectedOption) : empty($selectedOptions))>
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Submit Vote
                </button>
            </div>
        </div>
    @endif
</div>
