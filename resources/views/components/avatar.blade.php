@props(['size' => 28])
{{--<div class="w-8 h-8 w-28 h-28"></div>--}}
<div class="w-{{ $size }} h-{{ $size }} rounded-full shadow-xl" style="background-image: url({{ $avatarPath }}); background-size: cover"></div>
