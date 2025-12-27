<section class="hidden w-64 md:flex flex-col items-center p-6 space-y-4 text-white bg-gradient-to-b from-steel-850 to-steel-900 border-r border-steel-700/30">
    <div class="text-center">
        <a href="/profiles/{{ $owner->username }}" class="text-xl text-white font-bold leading-tight hover:text-accent-400 transition-colors">{{ $owner->username }}</a>
        <h4 class="text-steel-400 text-sm mt-1">{{ $owner->usertitle }}</h4>
    </div>

    <div class="relative">
        <x-avatar :avatar-path="$owner->avatar_path" class="ring-4 ring-steel-700 shadow-xl" />
    </div>

    <div class="text-center space-y-1">
        <div class="text-steel-300">
            <span class="font-bold text-white text-lg">
                {{ number_format($owner->posts_count) }}
            </span>
            <span class="text-sm">posts</span>
        </div>
        <div class="text-xs text-steel-500">
            Joined
            @if ($owner->legacy_join_date)
                {{ \Carbon\Carbon::parse($owner->legacy_join_date)->format('M Y') }}
            @else
                {{ \Carbon\Carbon::parse($owner->created_at)->format('M Y') }}
            @endif
        </div>
    </div>
</section>

<section class="md:hidden flex items-center p-4 space-x-4 text-white bg-steel-850 border-b border-steel-700/30">
    <x-avatar size="16" :avatar-path="$owner->avatar_path" class="ring-2 ring-steel-600 shadow-lg" />
    <div>
        <a href="/profiles/{{ $owner->username }}" class="text-lg text-white font-bold leading-tight hover:text-accent-400 transition-colors">{{ $owner->username }}</a>
        <h4 class="text-steel-400 text-sm">{{ $owner->usertitle }}</h4>
        <div class="text-xs text-steel-400 mt-1">
            <span class="font-bold text-steel-200">
                {{ number_format($owner->posts_count) }}
            </span>
            posts
        </div>
    </div>
</section>
