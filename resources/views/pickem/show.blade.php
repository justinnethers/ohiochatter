<x-app-layout :seo="$seo ?? null">
    <x-slot name="title">{{ $pickem->title }}</x-slot>

    <x-slot name="head">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.30.0/dist/trumbowyg.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.30.0/dist/plugins/upload/trumbowyg.upload.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/ui/trumbowyg.min.css">
        <style>
            .trumbowyg-dark .trumbowyg-box {
                border-color: #475569;
                background: #0f172a;
            }
            .trumbowyg-dark .trumbowyg-box .trumbowyg-editor {
                background: #0f172a;
                color: #e2e8f0;
                min-height: 80px;
            }
            .trumbowyg-dark .trumbowyg-button-pane {
                background: #1e293b;
                border-color: #475569;
            }
            .trumbowyg-dark .trumbowyg-button-pane button {
                color: #94a3b8;
            }
            .trumbowyg-dark .trumbowyg-button-pane button:hover {
                background: #334155;
                color: #e2e8f0;
            }
            .trumbowyg-giphy-button {
                display: flex !important;
                align-items: center;
                justify-content: center;
                font-size: 11px !important;
                font-weight: 700 !important;
                color: #94a3b8 !important;
                text-transform: uppercase;
                width: auto !important;
                padding: 0 8px !important;
            }
            .trumbowyg-giphy-button:hover {
                color: #e2e8f0 !important;
            }
            .trumbowyg-giphy-button svg {
                display: none !important;
            }
        </style>
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-lg md:text-xl font-bold text-white leading-tight flex items-center gap-3 min-w-0">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                <a href="{{ route('pickem.index') }}" class="text-steel-400 hover:text-accent-400 transition-colors">Pick 'Ems</a>
                <span class="text-steel-600">/</span>
                <span class="truncate">{{ $pickem->title }}</span>
            </h2>
            @if (auth()->check() && auth()->user()->is_admin)
                <a href="{{ route('pickem.admin.edit', $pickem) }}"
                   class="px-3 py-1.5 bg-steel-700 hover:bg-steel-600 rounded-lg text-steel-200 text-xs md:text-sm transition-colors shrink-0">
                    Edit
                </a>
            @endif
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:pt-6 md:mt-4">

            {{-- Pickem Info Header --}}
            <div class="mb-6">
                {{-- Stats row --}}
                <div class="flex flex-wrap items-center gap-2 pb-6 border-b border-steel-700/50">
                    @if($pickem->group)
                        <a href="{{ route('pickem.group', $pickem->group) }}"
                           class="inline-flex items-center px-2 md:px-3 py-0.5 md:py-1 bg-accent-500 rounded-full text-xs md:text-sm font-semibold text-white shadow-lg shadow-black/20 hover:bg-accent-600 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200">
                            {{ $pickem->group->name }}
                        </a>
                    @endif

                    <div class="inline-flex items-center gap-1 md:gap-2 px-2 md:px-3 py-0.5 md:py-1 bg-steel-900/70 rounded-full border border-steel-700/50">
                        <span class="font-bold text-steel-100">{{ $pickem->matchups->count() }}</span>
                        <span class="text-sm text-steel-400">{{ Str::plural('matchup', $pickem->matchups->count()) }}</span>
                    </div>

                    <div class="flex-1"></div>

                    @if($pickem->picks_lock_at)
                        @if($pickem->isLocked())
                            <span class="text-sm text-rose-400">Locked</span>
                        @else
                            <span class="text-sm text-emerald-400">Locks in {{ $pickem->picks_lock_at->diffForHumans(null, true) }}</span>
                        @endif
                    @else
                        <span class="text-sm text-accent-400">Open</span>
                    @endif
                </div>

                @if($pickem->body)
                    <div class="mt-4 text-steel-300 prose prose-invert prose-sm max-w-none">
                        {!! $pickem->body !!}
                    </div>
                @endif
            </div>

            {{-- Main Pickem Game Component --}}
            <livewire:pickem-game :pickem="$pickem" />

            {{-- Leaderboard --}}
            <div class="mt-8 pt-8 border-t border-steel-700/50">
                <livewire:pickem-leaderboard :pickem="$pickem" />
            </div>

            {{-- Discussion Section --}}
            <div class="mt-8 pt-8 border-t border-steel-700/50">
                <h3 class="text-lg font-semibold text-white mb-4">Discussion</h3>
                <livewire:pickem-comments :pickem="$pickem" />
            </div>
        </div>
    </div>

    <x-giphy-modal />
</x-app-layout>
