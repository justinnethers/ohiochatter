<div class="flex w-1/2 md:w-1/3 order-1 md:order-3 justify-around md:justify-end">
    <span class="flex items-center md:block md:mx-2 secondary-text md:w-1/2 md:flex-1 text-center md:text-right">
        <span class="font-bold text-xl md:text-sm">{{ number_format($thread->replies_count) }}</span>
        <span class="hidden md:inline-block">{{ Str::plural('reply', $thread->replies_count) }}</span>
        <span class="md:hidden ml-1">
            <svg class="fill-current" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path d="M12 3c5.514 0 10 3.685 10 8.213 0 5.04-5.146 8.159-9.913 8.159-2.027 0-3.548-.439-4.548-.712l-4.004 1.196 1.252-2.9c-.952-1-2.787-2.588-2.787-5.743 0-4.528 4.486-8.213 10-8.213zm0-2c-6.628 0-12 4.573-12 10.213 0 2.39.932 4.591 2.427 6.164l-2.427 5.623 7.563-2.26c1.585.434 3.101.632 4.523.632 7.098.001 11.914-4.931 11.914-10.159 0-5.64-5.372-10.213-12-10.213z"/></svg>
        </span>
    </span>
</div>
