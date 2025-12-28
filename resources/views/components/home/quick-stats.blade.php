@props(['stats'])

<div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl border border-steel-700/50 p-4">
    <h3 class="text-sm font-semibold text-white flex items-center gap-2 mb-3">
        <span class="w-1 h-4 bg-accent-500 rounded-full"></span>
        Community Stats
    </h3>

    <div class="grid grid-cols-3 gap-2 text-center">
        <div class="bg-steel-900/50 rounded-lg p-2">
            <div class="text-lg font-bold text-white">{{ number_format($stats['members']) }}</div>
            <div class="text-xs text-steel-400">Members</div>
        </div>
        <div class="bg-steel-900/50 rounded-lg p-2">
            <div class="text-lg font-bold text-white">{{ number_format($stats['threads']) }}</div>
            <div class="text-xs text-steel-400">Threads</div>
        </div>
        <div class="bg-steel-900/50 rounded-lg p-2">
            <div class="text-lg font-bold text-white">{{ number_format($stats['replies']) }}</div>
            <div class="text-xs text-steel-400">Replies</div>
        </div>
    </div>
</div>
