@props(['size' => 28])
{{--<div class="w-8 h-8 w-28 h-28 h-16 w-16 h-12 w-12 h-20 w-20 h-24 w-24 h-10 w-10"></div>--}}
<div
    class="w-{{ $size }} h-{{ $size }} rounded-full shadow-xl {{ $attributes->get('class') }}"
    style="background-image: url({{ $avatarPath }}); background-size: cover"
    {{ $attributes }}
></div>
