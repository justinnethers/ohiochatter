@props([
    'owner',
    'username' => null,
    'usertitle' => null,
    'avatarPath' => null,
    'archiveAvatarFilename' => null,
    'postsCount' => null,
    'linkProfile' => true,
])

@php
    // Support both User model and manual props
    $displayUsername = $username ?? $owner->username ?? 'Guest';
    $displayUsertitle = $usertitle ?? $owner->usertitle ?? '';
    $displayPostsCount = $postsCount ?? $owner->posts_count ?? null;
    $displayAvatarPath = $avatarPath ?? $owner->avatar_path ?? null;
    $profileUrl = $linkProfile && isset($owner) ? "/profiles/{$owner->username}" : null;
    $joinDate = isset($owner) ? ($owner->legacy_join_date ?? $owner->created_at) : null;
@endphp

{{-- Desktop: User sidebar --}}
<section class="hidden w-64 md:flex flex-col items-center p-8 space-y-4 text-white border-r border-steel-700/30">
    <div class="text-center">
        @if($profileUrl)
            <a href="{{ $profileUrl }}" class="text-xl text-white font-bold leading-tight hover:text-accent-400 transition-colors">{{ $displayUsername }}</a>
        @else
            <h3 class="text-xl text-white font-bold leading-tight">{{ $displayUsername }}</h3>
        @endif
        @if($displayUsertitle)
            <h4 class="text-steel-400 text-sm mt-1">{{ $displayUsertitle }}</h4>
        @endif
    </div>

    @if($archiveAvatarFilename)
        <img class="rounded-full h-24 w-24 object-cover ring-4 ring-steel-700 shadow-lg"
             src="/storage/avatars/archive/{{ $archiveAvatarFilename }}"
             alt="{{ $displayUsername }}'s avatar" />
    @elseif($displayAvatarPath)
        <x-avatar :avatar-path="$displayAvatarPath" class="ring-4 ring-steel-700 shadow-lg" />
    @else
        <div class="rounded-full h-24 w-24 bg-steel-700 flex items-center justify-center ring-4 ring-steel-600 shadow-lg">
            <span class="text-3xl text-steel-400">
                {{ strtoupper(substr($displayUsername, 0, 1)) }}
            </span>
        </div>
    @endif

    <div class="text-center space-y-1">
        @if($displayPostsCount !== null)
            <div class="text-steel-300">
                <span class="font-bold text-white text-lg">{{ number_format($displayPostsCount) }}</span>
                <span class="text-sm">posts</span>
            </div>
        @endif
        @if($joinDate)
            <div class="text-xs text-steel-500">
                Joined {{ \Carbon\Carbon::parse($joinDate)->format('M Y') }}
            </div>
        @endif
    </div>
</section>

{{-- Mobile: User header --}}
<section class="md:hidden flex items-center p-4 space-x-4 text-white border-b border-steel-700/30">
    @if($archiveAvatarFilename)
        <img class="rounded-full h-12 w-12 object-cover ring-2 ring-steel-700"
             src="/storage/avatars/archive/{{ $archiveAvatarFilename }}"
             alt="{{ $displayUsername }}'s avatar" />
    @elseif($displayAvatarPath)
        <x-avatar size="16" :avatar-path="$displayAvatarPath" class="ring-2 ring-steel-600 shadow-lg" />
    @else
        <div class="rounded-full h-12 w-12 bg-steel-700 flex items-center justify-center ring-2 ring-steel-600">
            <span class="text-lg text-steel-400">
                {{ strtoupper(substr($displayUsername, 0, 1)) }}
            </span>
        </div>
    @endif
    <div>
        @if($profileUrl)
            <a href="{{ $profileUrl }}" class="text-lg text-white font-bold leading-tight hover:text-accent-400 transition-colors">{{ $displayUsername }}</a>
        @else
            <h3 class="text-lg text-white font-bold leading-tight">{{ $displayUsername }}</h3>
        @endif
        @if($displayUsertitle)
            <h4 class="text-steel-400 text-sm">{{ $displayUsertitle }}</h4>
        @endif
        @if($displayPostsCount !== null)
            <div class="text-xs text-steel-400 mt-1">
                <span class="font-bold text-steel-200">{{ number_format($displayPostsCount) }}</span>
                posts
            </div>
        @endif
    </div>
</section>
