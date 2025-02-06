<div>
    <h3 class="text-lg font-semibold text-white mb-4">{{  $this->activeUsers->count() }} {{ \Illuminate\Support\Str::plural('Member', $this->activeUsers->count()) }} Online</h3>

    <div class="space-y-2">
        @forelse($this->activeUsers as $user)
            <div class="flex items-center gap-2">
                <div class="relative">
                    <x-avatar :avatar-path="$user->avatar_path" size="8"/>
{{--                    <div class="absolute bottom-0 right-0 h-3 w-3 bg-green-500 border-2 border-gray-800 rounded-full"></div>--}}
                </div>
                <div class="flex flex-col">
                    <span class="text-white text-sm font-medium">{{ $user->username }}</span>
                    <span class="text-gray-400 text-xs">{{ $user->last_activity?->diffForHumans() }}</span>
                </div>
            </div>
        @empty
            <p class="text-gray-400 text-sm">No users active in the last 30 minutes</p>
        @endforelse
    </div>
</div>
