<div class="relative" x-data="{ open: false }">
    <button
        @click="open = !open"
        class="relative p-2 text-steel-400 hover:text-white transition-colors rounded-lg hover:bg-steel-700/50"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        @if($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 h-4 w-4 rounded-full bg-red-500 text-white text-xs flex items-center justify-center font-bold ring-2 ring-steel-800">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="absolute right-0 mt-2 w-80 bg-gradient-to-b from-steel-800 to-steel-900 rounded-xl shadow-2xl shadow-black/40 border border-steel-700/50 overflow-hidden z-50"
        style="display: none;"
    >
        <div class="flex items-center justify-between px-4 py-3 border-b border-steel-700/50">
            <h3 class="font-semibold text-white text-sm">Notifications</h3>
            @if($unreadCount > 0)
                <button
                    wire:click="markAllAsRead"
                    class="text-xs text-accent-400 hover:text-accent-300 transition-colors"
                >
                    Mark all read
                </button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse($notifications as $notification)
                <a
                    href="{{ $notification->data['url'] ?? '#' }}"
                    wire:click="markAsRead('{{ $notification->id }}')"
                    class="block px-4 py-3 hover:bg-steel-700/50 border-b border-steel-700/30 transition-colors"
                >
                    @if(($notification->data['type'] ?? '') === 'mention')
                        <div class="flex items-start gap-3">
                            <x-avatar :avatar-path="$notification->data['mentioned_by_avatar'] ?? null" :size="8" />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-white">
                                    <span class="font-medium">{{ $notification->data['mentioned_by_username'] ?? 'Someone' }}</span>
                                    <span class="text-steel-400">mentioned you in</span>
                                </p>
                                <p class="text-xs text-steel-400 truncate mt-0.5">
                                    {{ $notification->data['thread_title'] ?? 'a discussion' }}
                                </p>
                                <p class="text-xs text-steel-500 mt-1">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="text-sm text-white">
                            New notification
                        </div>
                    @endif
                </a>
            @empty
                <div class="px-4 py-8 text-center text-steel-400 text-sm">
                    No new notifications
                </div>
            @endforelse
        </div>
    </div>
</div>
