<header class="font-body font-bold text-lg bg-gray-700 text-gray-100 px-4 py-2 md:rounded-bl-lg md:rounded-tr-lg">
    {{ Carbon\Carbon::parse($date)->toDayDateTimeString() }}
</header>
