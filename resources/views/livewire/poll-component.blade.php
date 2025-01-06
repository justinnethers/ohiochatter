<div class="bg-white dark:bg-gray-800 text-gray-500 dark:text-white rounded-lg shadow p-4 sm:p-6 mb-4 max-w-full overflow-hidden">
    <h3 class="text-lg font-semibold mb-4">Poll</h3>

    @if($hasVoted)
        @foreach($poll->pollOptions as $option)
            <div class="mb-4">
                <div class="flex flex-col sm:flex-row sm:justify-between mb-1">
                    <span class="break-words text-xl mr-2">{{ $option->label }}</span>
                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ $this->getPercentage($option) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded">
                    <div class="bg-blue-500 rounded h-2" style="width: {{ $this->getPercentage($option) }}%"></div>
                </div>
                <span class="text-xs sm:text-sm text-gray-500 dark:text-gray-200">{{ $option->votes->count() }} {{ \Illuminate\Support\Str::plural('vote', $option->votes->count()) }}</span>
            </div>
        @endforeach
        <div class="mt-4 text-sm text-gray-500 dark:text-gray-200">
            Total votes: {{ $this->voteCount }}
        </div>
    @else
        <div class="space-y-3">
            @if($poll->type === 'single')
                @foreach($poll->pollOptions as $option)
                    <div class="mb-2">
                        <label class="inline-flex items-center">
                            <input type="radio"
                                   wire:model.live="selectedOption"
                                   name="poll_choice"
                                   value="{{ $option->id }}"
                                   class="form-radio">
                            <span class="ml-2">{{ $option->label }}</span>
                        </label>
                    </div>
                @endforeach
            @else
                @foreach($poll->pollOptions as $option)
                    <div class="mb-2">
                        <label class="flex items-center cursor-pointer p-2 hover:bg-gray-50 rounded">
                            <input type="checkbox"
                                   wire:model.live="selectedOptions"
                                   value="{{ $option->id }}"
                                   class="form-checkbox h-4 w-4">
                            <span class="ml-2 break-words flex-1">{{ $option->label }}</span>
                        </label>
                    </div>
                @endforeach
            @endif

            <div class="pt-2">
                <button
                    wire:click="vote"
                    type="button"
                    class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                    @disabled($poll->type === 'single' ? empty($selectedOption) : empty($selectedOptions))>
                    Vote
                </button>
            </div>
        </div>
    @endif
</div>
