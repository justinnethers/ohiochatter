<div>
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-500/20 border border-green-500/50 rounded-lg text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-8">
        {{-- Pick 'Em Settings --}}
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-white">Pick 'Em Settings</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block font-semibold text-steel-200 mb-2">Title</label>
                    <input
                        type="text"
                        wire:model="title"
                        class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg shadow-inner p-2.5 px-4 text-base transition-colors duration-200"
                        placeholder="e.g., NFL Week 1 Picks"
                    >
                    @error('title')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold text-steel-200 mb-2">Group (optional)</label>
                    <select
                        wire:model="pickem_group_id"
                        class="w-full border border-steel-600 bg-steel-950 text-steel-100 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg shadow-inner p-2.5 px-4 text-base transition-colors duration-200"
                    >
                        <option value="">No Group</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-steel-200 mb-2">Scoring Type</label>
                    <select
                        wire:model="scoring_type"
                        class="w-full border border-steel-600 bg-steel-950 text-steel-100 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg shadow-inner p-2.5 px-4 text-base transition-colors duration-200"
                    >
                        <option value="simple">Simple (1 point per correct pick)</option>
                        <option value="weighted">Weighted (custom points per matchup)</option>
                        <option value="confidence">Confidence (user assigns points)</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block font-semibold text-steel-200 mb-2">Picks Lock At (optional)</label>
                    <input
                        type="datetime-local"
                        wire:model="picks_lock_at"
                        class="w-full border border-steel-600 bg-steel-950 text-steel-100 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg shadow-inner p-2.5 px-4 text-base transition-colors duration-200"
                    >
                    <p class="mt-1 text-xs text-steel-500">Users cannot submit or change picks after this time.</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block font-semibold text-steel-200 mb-2">Description (optional)</label>
                    <textarea
                        wire:model="body"
                        rows="3"
                        class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg shadow-inner p-2.5 px-4 text-base transition-colors duration-200 resize-none"
                        placeholder="Rules, context, or notes for participants..."
                    ></textarea>
                </div>
            </div>
        </div>

        {{-- Matchups Section --}}
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-white">Matchups</h3>
                <button
                    type="button"
                    wire:click="openMatchupForm"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-accent-500 to-accent-600 rounded-lg text-white font-semibold text-sm shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 transition-all duration-200"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Matchup
                </button>
            </div>

            @if(empty($matchups))
                <div class="text-center py-8 bg-steel-800/50 rounded-xl border border-dashed border-steel-600">
                    <p class="text-steel-400">No matchups yet. Add some matchups to create picks!</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($matchups as $index => $matchup)
                        <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-4 border border-steel-700/50">
                            <div class="flex items-center gap-4">
                                {{-- Reorder Buttons --}}
                                <div class="flex flex-col gap-1">
                                    <button
                                        type="button"
                                        wire:click="moveMatchupUp({{ $index }})"
                                        @class([
                                            'p-1 rounded transition-colors',
                                            'text-steel-600 cursor-not-allowed' => $index === 0,
                                            'text-steel-400 hover:text-white' => $index > 0,
                                        ])
                                        @disabled($index === 0)
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="moveMatchupDown({{ $index }})"
                                        @class([
                                            'p-1 rounded transition-colors',
                                            'text-steel-600 cursor-not-allowed' => $index === count($matchups) - 1,
                                            'text-steel-400 hover:text-white' => $index < count($matchups) - 1,
                                        ])
                                        @disabled($index === count($matchups) - 1)
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Matchup Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 text-white font-medium">
                                        <span @class(['text-green-400' => $matchup['winner'] === 'a'])>{{ $matchup['option_a'] }}</span>
                                        <span class="text-steel-500">vs</span>
                                        <span @class(['text-green-400' => $matchup['winner'] === 'b'])>{{ $matchup['option_b'] }}</span>
                                        @if($matchup['winner'] === 'push')
                                            <span class="px-2 py-0.5 bg-yellow-500/20 text-yellow-400 text-xs rounded-full">Push</span>
                                        @endif
                                    </div>
                                    @if($matchup['description'])
                                        <p class="text-sm text-steel-400 mt-1">{{ $matchup['description'] }}</p>
                                    @endif
                                    @if($scoring_type === 'weighted')
                                        <p class="text-xs text-steel-500 mt-1">{{ $matchup['points'] }} point(s)</p>
                                    @endif
                                </div>

                                {{-- Winner Selection (only show for existing pickems) --}}
                                @if($pickem)
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-steel-500">Winner:</span>
                                        <div class="flex gap-1">
                                            <button
                                                type="button"
                                                wire:click="setWinner({{ $index }}, 'a')"
                                                @class([
                                                    'px-2 py-1 rounded text-xs font-medium transition-colors',
                                                    'bg-green-500 text-white' => $matchup['winner'] === 'a',
                                                    'bg-steel-700 text-steel-300 hover:bg-steel-600' => $matchup['winner'] !== 'a',
                                                ])
                                            >
                                                A
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="setWinner({{ $index }}, 'b')"
                                                @class([
                                                    'px-2 py-1 rounded text-xs font-medium transition-colors',
                                                    'bg-green-500 text-white' => $matchup['winner'] === 'b',
                                                    'bg-steel-700 text-steel-300 hover:bg-steel-600' => $matchup['winner'] !== 'b',
                                                ])
                                            >
                                                B
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="setWinner({{ $index }}, 'push')"
                                                @class([
                                                    'px-2 py-1 rounded text-xs font-medium transition-colors',
                                                    'bg-yellow-500 text-white' => $matchup['winner'] === 'push',
                                                    'bg-steel-700 text-steel-300 hover:bg-steel-600' => $matchup['winner'] !== 'push',
                                                ])
                                            >
                                                Push
                                            </button>
                                            @if($matchup['winner'])
                                                <button
                                                    type="button"
                                                    wire:click="setWinner({{ $index }}, null)"
                                                    class="px-2 py-1 rounded text-xs font-medium bg-steel-700 text-steel-300 hover:bg-steel-600 transition-colors"
                                                >
                                                    Clear
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Edit/Delete --}}
                                <div class="flex items-center gap-1">
                                    <button
                                        type="button"
                                        wire:click="openMatchupForm({{ $index }})"
                                        class="p-2 text-steel-400 hover:text-white transition-colors"
                                        title="Edit"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="removeMatchup({{ $index }})"
                                        wire:confirm="Remove this matchup?"
                                        class="p-2 text-steel-400 hover:text-red-400 transition-colors"
                                        title="Remove"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-4 border-t border-steel-700">
            <div>
                @if($pickem && !$is_finalized)
                    <button
                        type="button"
                        wire:click="finalize"
                        wire:confirm="Finalize this Pick 'Em? This marks it as complete and calculates final scores."
                        class="px-4 py-2 bg-green-600 hover:bg-green-500 rounded-lg text-white font-semibold transition-colors"
                    >
                        Finalize Pick 'Em
                    </button>
                @elseif($is_finalized)
                    <span class="px-4 py-2 bg-green-500/20 text-green-400 rounded-lg font-medium">
                        Finalized
                    </span>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('pickem.admin.index') }}"
                    class="px-4 py-2 bg-steel-700 hover:bg-steel-600 rounded-lg text-steel-200 transition-colors"
                >
                    Cancel
                </a>
                <button
                    type="submit"
                    class="px-6 py-2 bg-gradient-to-r from-accent-500 to-accent-600 rounded-lg text-white font-semibold shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 transition-all duration-200"
                >
                    {{ $pickem ? 'Update' : 'Create' }} Pick 'Em
                </button>
            </div>
        </div>
    </form>

    {{-- Matchup Form Modal --}}
    @if($showMatchupForm)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click.self="closeMatchupForm">
            <div class="bg-steel-800 rounded-xl p-6 w-full max-w-md border border-steel-700">
                <h4 class="text-lg font-semibold text-white mb-4">
                    {{ $editingMatchupIndex !== null ? 'Edit Matchup' : 'Add Matchup' }}
                </h4>

                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-steel-200 mb-2">Option A</label>
                        <input
                            type="text"
                            wire:model="matchupOptionA"
                            class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg shadow-inner p-2.5 px-4 text-base transition-colors duration-200"
                            placeholder="e.g., Bengals"
                        >
                        @error('matchupOptionA')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-steel-200 mb-2">Option B</label>
                        <input
                            type="text"
                            wire:model="matchupOptionB"
                            class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg shadow-inner p-2.5 px-4 text-base transition-colors duration-200"
                            placeholder="e.g., Browns"
                        >
                        @error('matchupOptionB')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-steel-200 mb-2">Description (optional)</label>
                        <input
                            type="text"
                            wire:model="matchupDescription"
                            class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg shadow-inner p-2.5 px-4 text-base transition-colors duration-200"
                            placeholder="e.g., Week 1 - Sunday 1pm"
                        >
                    </div>

                    @if($scoring_type === 'weighted')
                        <div>
                            <label class="block font-semibold text-steel-200 mb-2">Points</label>
                            <input
                                type="number"
                                wire:model="matchupPoints"
                                min="1"
                                class="w-full border border-steel-600 bg-steel-950 text-steel-100 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg shadow-inner p-2.5 px-4 text-base transition-colors duration-200"
                            >
                        </div>
                    @endif

                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            wire:click="closeMatchupForm"
                            class="px-4 py-2 bg-steel-700 hover:bg-steel-600 rounded-lg text-steel-200 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            wire:click="saveMatchup"
                            class="px-4 py-2 bg-gradient-to-r from-accent-500 to-accent-600 rounded-lg text-white font-semibold shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 transition-all duration-200"
                        >
                            {{ $editingMatchupIndex !== null ? 'Update' : 'Add' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
