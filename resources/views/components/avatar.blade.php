@props(['size' => 28])
{{--<div class="w-8 h-8 w-28 h-28 h-16 w-16 h-12 w-12 h-20 w-20 h-24 w-24"></div>--}}
<div class="w-{{ $size }} h-{{ $size }} rounded-full shadow-xl" style="background-image: url({{ $avatarPath }}); background-size: cover"></div>
