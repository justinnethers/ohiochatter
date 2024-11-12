<header class="flex justify-between items-center bg-gray-700 text-gray-100 px-4 py-2 md:rounded-bl-lg md:rounded-tr-lg">
    <div class="font-body font-bold text-lg">{{ Carbon\Carbon::parse($date)->toDayDateTimeString() }}</div>
    <div>
        {{ $slot }}
    </div>
</header>
