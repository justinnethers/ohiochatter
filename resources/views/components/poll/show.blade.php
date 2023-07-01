<div class="poll rounded shadow-lg bg-gray-800 text-white p-4 md:p-8 mb-8">
    @if ($hasVoted)
        <ul class="list-group">
            <h3 class="font-bold text-2xl mb-8">Poll Results</h3>
            <li class="list-group-item">
                @foreach ($poll->pollOptions as $option)
                    <div class="flex relative py-2 mb-2 text-lg bg-gray-200 rounded shadow flex">
                        @if ($option->votes->count() > 0)
                            <span class="flex items-center pl-4 z-9 font-bold text-gray-950">{{ $option->label }}</span>
                            <span
                                class="flex items-center pl-4 font-bold absolute top-0 bottom-0 left-0 z-10 overflow-hidden text-white hidden-xs"
                                style=" width: {{ $option->votes->count() / $voteCount * 100 }}%;"
                            >
                                {{ $option->label }}
                            </span>
                        @else
                            <span class="flex items-center pl-4 z-9 text-blue-900">{{ $option->label }}</span>
                        @endif
                        <span class="flex-1"></span>
                        <span
                            class="flex items-center text-blue-900 text-lg mr-2 font-bold"
                        >
                            {{ $option->votes->count() }}
                            {{ Str::plural('vote', $option->votes->count()) }}
                            ({{ number_format($option->votes->count() / $voteCount * 100, 0) }}%)
                        </span>
                        <span
                            class="absolute top-0 bottom-0 left-0 bg-blue-700 rounded"
                            style="width: {{ $option->votes->count() / $voteCount * 100 }}%;"
                        ></span>
                    </div>
                @endforeach
            </li>
        </ul>
    @else
        <ul class="list-group">
            <h3 class="font-bold text-3xl mb-8">Poll</h3>
            <li class="list-group-item">
                <form action="/polls/{{ $poll->id }}/vote" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="{{ $poll->type }}"/>
                    <input type="hidden" name="slug" value="{{ $thread->slug }}"/>
                    @foreach ($poll->pollOptions as $option)
                        <div class="inputGroup text-blue-900">
                            @if ($poll->type == 'multiple')
                                <input type="checkbox" id="option{{ $option->id }}" value="{{ $option->id }}"
                                       name="{{ $option->id }}"/>
                                <label class=" text-blue-900" for="option{{ $option->id }}">{{ $option->label }}</label>
                            @else
                                <input type="radio" id="option{{ $option->id }}" value="{{ $option->id }}"
                                       name="option"/>
                                <label class=" text-blue-900" for="option{{ $option->id }}">{{ $option->label }}</label>
                            @endif
                        </div>
                    @endforeach
                    <button class="btn btn-info" type="submit">Submit Vote</button>
                </form>
            </li>
        </ul>
    @endif
</div>
