<div>
    @if($processed)
        {{-- Success message --}}
        <div class="mb-6 p-4 rounded-xl {{ $processedAction === 'approved' ? 'bg-green-500/20 border border-green-500/50' : 'bg-red-500/20 border border-red-500/50' }}">
            <div class="flex items-center gap-3">
                @if($processedAction === 'approved')
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div>
                        <p class="font-semibold text-green-300">Revision Approved</p>
                        <p class="text-sm text-green-400">The changes have been applied to the guide.</p>
                    </div>
                @else
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <div>
                        <p class="font-semibold text-red-300">Revision Rejected</p>
                        <p class="text-sm text-red-400">The author has been notified.</p>
                    </div>
                @endif
            </div>
        </div>
    @else
        {{-- Pending revision banner --}}
        <div class="mb-6 p-4 bg-gradient-to-r from-amber-500/20 to-orange-500/20 border border-amber-500/50 rounded-xl">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="font-semibold text-amber-200">Pending Revision</p>
                        <p class="text-sm text-amber-300/80">
                            <a href="{{ route('profile.show', $revision->author) }}" class="text-amber-200 hover:underline">{{ $revision->author->username }}</a>
                            submitted changes {{ $revision->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="togglePreview" type="button"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $showPreview ? 'bg-amber-500 text-white' : 'bg-steel-700 text-steel-200 hover:bg-steel-600' }}">
                        {{ $showPreview ? 'Hide Preview' : 'Preview Changes' }}
                    </button>
                    <button wire:click="approve" type="button"
                        class="px-4 py-2 text-sm font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Approve
                    </button>
                    <button wire:click="openRejectModal" type="button"
                        class="px-4 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Reject
                    </button>
                </div>
            </div>
        </div>

        {{-- Preview panel --}}
        @if($showPreview)
            <div class="mb-6 border-2 border-amber-500/50 rounded-xl overflow-hidden">
                <div class="bg-amber-500/20 px-4 py-2 border-b border-amber-500/50">
                    <h3 class="font-semibold text-amber-200 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Proposed Changes Preview
                    </h3>
                </div>

                <div class="bg-steel-900/50 p-4 md:p-6">
                    {{-- Title comparison --}}
                    @if($revision->title !== $content->title)
                        <div class="mb-4 p-3 bg-steel-800/50 rounded-lg">
                            <p class="text-xs text-steel-500 uppercase tracking-wide mb-1">Title</p>
                            <p class="text-red-400 line-through text-sm">{{ $content->title }}</p>
                            <p class="text-green-400 text-sm">{{ $revision->title }}</p>
                        </div>
                    @endif

                    {{-- Excerpt comparison --}}
                    @if($revision->excerpt !== $content->excerpt)
                        <div class="mb-4 p-3 bg-steel-800/50 rounded-lg">
                            <p class="text-xs text-steel-500 uppercase tracking-wide mb-1">Summary</p>
                            @if($content->excerpt)
                                <p class="text-red-400 line-through text-sm">{{ $content->excerpt }}</p>
                            @else
                                <p class="text-steel-500 text-sm italic">No previous summary</p>
                            @endif
                            @if($revision->excerpt)
                                <p class="text-green-400 text-sm">{{ $revision->excerpt }}</p>
                            @else
                                <p class="text-steel-500 text-sm italic">Summary removed</p>
                            @endif
                        </div>
                    @endif

                    {{-- Full content preview --}}
                    <div class="border-t border-steel-700/50 pt-4 mt-4">
                        <p class="text-xs text-steel-500 uppercase tracking-wide mb-3">Full Content Preview</p>

                        @if($revision->excerpt)
                            <div class="text-lg text-steel-300 mb-6 border-l-4 border-accent-500 pl-4">
                                {{ $revision->excerpt }}
                            </div>
                        @endif

                        {{-- Render the proposed blocks --}}
                        <x-blocks.renderer :blocks="$revision->blocks ?? []" mode="view" />
                    </div>
                </div>
            </div>
        @endif

        {{-- Reject Modal --}}
        @if($showRejectModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                <div class="bg-steel-800 rounded-xl shadow-2xl max-w-md w-full border border-steel-700">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-white mb-2">Reject Revision</h3>
                        <p class="text-steel-400 text-sm mb-4">Optionally provide a reason for rejecting this revision. The author will be notified.</p>

                        <textarea wire:model="rejectReason" rows="3"
                            placeholder="Reason for rejection (optional)..."
                            class="w-full border border-steel-600 bg-steel-900 text-steel-100 placeholder-steel-500 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rounded-lg p-3 text-sm"></textarea>

                        <div class="flex justify-end gap-3 mt-4">
                            <button wire:click="closeRejectModal" type="button"
                                class="px-4 py-2 text-sm font-medium text-steel-300 hover:text-white transition-colors">
                                Cancel
                            </button>
                            <button wire:click="reject" type="button"
                                class="px-4 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                Reject Revision
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
