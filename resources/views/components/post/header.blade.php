<header class="flex justify-between items-center bg-gradient-to-r from-steel-700 to-steel-750 text-steel-200 px-4 py-2.5 md:rounded-bl-xl">
    <div class="font-body font-medium text-sm md:text-base flex items-center gap-2">
        <svg class="w-4 h-4 text-steel-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        {{ Carbon\Carbon::parse($date)->toDayDateTimeString() }}
    </div>
    <div>
        {{ $slot }}
    </div>
</header>
