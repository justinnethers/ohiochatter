@guest
    @php
        $pageViews = session('guest_page_views', 0);
        $dismissed = session('signup_modal_dismissed', false);
        $onAuthPage = request()->routeIs('login', 'register', 'password.*', 'verification.*');
    @endphp

    @if($pageViews > 3 && !$dismissed && !$onAuthPage)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-cloak
            class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            {{-- Backdrop --}}
            <div
                class="fixed inset-0 bg-steel-950/80 backdrop-blur-sm"
                x-on:click="show = false; fetch('{{ route('dismiss-signup-modal') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })"
            ></div>

            {{-- Modal Content --}}
            <div class="relative min-h-full flex items-center justify-center">
                <div
                    x-show="show"
                    class="relative bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl overflow-hidden shadow-2xl shadow-black/50 border border-steel-700/50 w-full max-w-md mx-auto"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    x-on:click.stop
                >
                    {{-- Close Button --}}
                    <button
                        x-on:click="show = false; fetch('{{ route('dismiss-signup-modal') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })"
                        class="absolute top-4 right-4 text-steel-400 hover:text-white transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <div class="p-6 sm:p-8 text-center">
                        {{-- Icon --}}
                        <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-scarlet-500 to-scarlet-600 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>

                        {{-- Heading --}}
                        <h2 class="text-2xl font-bold text-white mb-2">Join the Conversation!</h2>
                        <p class="text-steel-300 mb-6">
                            Create a free account to post replies, start discussions, and connect with fellow Ohioans.
                        </p>

                        {{-- CTA Buttons --}}
                        <div class="space-y-3">
                            <a
                                href="{{ route('register') }}"
                                x-on:click.prevent="fetch('{{ route('dismiss-signup-modal') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).then(() => window.location.href = '{{ route('register') }}')"
                                class="block w-full px-6 py-3 bg-gradient-to-r from-scarlet-600 to-scarlet-700 hover:from-scarlet-500 hover:to-scarlet-600 text-white font-semibold rounded-lg transition-all duration-200 shadow-lg shadow-scarlet-900/30"
                            >
                                Create Free Account
                            </a>
                            <a
                                href="{{ route('login') }}"
                                x-on:click.prevent="fetch('{{ route('dismiss-signup-modal') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).then(() => window.location.href = '{{ route('login') }}')"
                                class="block w-full px-6 py-3 bg-steel-700 hover:bg-steel-600 text-white font-medium rounded-lg transition-colors border border-steel-600"
                            >
                                Already have an account? Sign In
                            </a>
                        </div>

                        {{-- Dismiss Link --}}
                        <button
                            x-on:click="show = false; fetch('{{ route('dismiss-signup-modal') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })"
                            class="mt-4 text-sm text-steel-400 hover:text-steel-300 transition-colors"
                        >
                            Maybe later
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endguest
