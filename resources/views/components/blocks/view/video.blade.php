@props(['block', 'index' => 0, 'path' => '', 'nested' => false])

@if(!empty($block['data']['url']))
    @php
        $videoUrl = $block['data']['url'];
        $embedUrl = null;

        // YouTube
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $videoUrl, $matches)) {
            $embedUrl = "https://www.youtube.com/embed/{$matches[1]}";
        }
        // Vimeo
        elseif (preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $matches)) {
            $embedUrl = "https://player.vimeo.com/video/{$matches[1]}";
        }
    @endphp

    <div class="{{ $nested ? '' : 'my-6' }}">
        @if($embedUrl)
            <div class="aspect-video rounded-lg overflow-hidden">
                <iframe src="{{ $embedUrl }}"
                    class="w-full h-full"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen></iframe>
            </div>
        @else
            <a href="{{ $videoUrl }}" target="_blank" rel="noopener noreferrer"
                class="flex items-center gap-2 text-accent-400 hover:text-accent-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Watch Video
            </a>
        @endif
        @if(!empty($block['data']['caption']))
            <p class="mt-2 text-sm text-steel-400">{{ $block['data']['caption'] }}</p>
        @endif
    </div>
@endif
