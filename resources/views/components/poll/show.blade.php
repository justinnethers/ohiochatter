<div class="bg-white rounded-lg shadow p-6 mb-4">
    <h3 class="text-lg font-semibold mb-4">Poll</h3>

    @if($hasVoted)
        @foreach($poll->pollOptions as $option)
            <div class="mb-4">
                <div class="flex justify-between mb-1">
                    <span>{{ $option->label }}</span>
                    <span>{{ $this->getPercentage($option) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded">
                    <div class="bg-blue-500 rounded h-2" style="width: {{ $this->getPercentage($option) }}%"></div>
                </div>
                <span class="text-sm text-gray-500">{{ $option->votes->count() }} votes</span>
            </div>
        @endforeach
        <div class="mt-4 text-sm text-gray-500">
            Total votes: {{ $this->voteCount }}
        </div>
    @else
        <form wire:submit.prevent="vote">
            @if($poll->type === 'single')
                @foreach($poll->pollOptions as $option)
                    <div class="mb-2">
                        <label class="inline-flex items-center">
                            <input type="radio"
                                   wire:model="selectedOptions"
                                   value="{{ $option->id }}"
                                   class="form-radio">
                            <span class="ml-2">{{ $option->label }}</span>
                        </label>
                    </div>
                @endforeach
            @else
                @foreach($poll->pollOptions as $option)
                    <div class="mb-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox"
                                   wire:model="selectedOptions"
                                   value="{{ $option->id }}"
                                   class="form-checkbox">
                            <span class="ml-2">{{ $option->label }}</span>
                        </label>
                    </div>
                @endforeach
            @endif

            <button type="submit"
                    class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                    @if(empty($selectedOptions)) disabled @endif>
                Vote
            </button>
        </form>
    @endif
</div>
