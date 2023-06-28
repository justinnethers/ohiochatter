<section class="hidden w-64 md:flex flex-col items-center p-8 space-y-4 text-white">
    <div class="text-center">
        <h3 class="text-2xl text-gray-200 font-bold leading-tight">{{ $owner->username }}</h3>
        <h4>{{ $owner->usertitle }}</h4>
    </div>

    <x-avatar :avatar-path="$owner->avatar_path" />

    <div class="text-center">
        <div>
            <span class="font-bold italic">
                {{ number_format($owner->post_count + $owner->posts_old) }}
            </span>
            posts
        </div>
        <div class="text-sm secondary-text">
            Joined
            @if ($owner->legacy_join_date)
                {{ \Carbon\Carbon::parse($owner->legacy_join_date)->format('M Y') }}
            @else
                {{ \Carbon\Carbon::parse($owner->created_at)->format('M Y') }}
            @endif
        </div>
    </div>
</section>

<section class="md:hidden flex items-center p-4 space-x-4 text-white">
    <x-avatar size="20" :avatar-path="$owner->avatar_path" />
    <div>
        <h3 class="text-2xl text-gray-200 font-bold leading-tight">{{ $owner->username }}</h3>
        <h4>{{ $owner->usertitle }}</h4>
    </div>
</section>
