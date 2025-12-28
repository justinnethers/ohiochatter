<x-app-layout>
    <x-slot name="title">Login</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                Login
            </h2>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:mt-4">
            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <div class="flex items-center justify-center">
                <div class="w-full sm:max-w-md px-6 py-6 bg-gradient-to-br from-steel-800 to-steel-850 shadow-lg shadow-black/20 overflow-hidden rounded-xl border border-steel-700/50">
                    <form method="POST" action="{{ route('login') }}" class="space-y-6">
                        @csrf

                        <!-- Username -->
                        <div>
                            <x-input-label for="username" :value="__('Username')" class="mb-2" />
                            <x-text-input id="username" type="text" name="username" :value="old('username')" required autofocus autocomplete="username" />
                            <x-input-error :messages="$errors->get('username')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div>
                            <x-input-label for="password" :value="__('Password')" class="mb-2" />
                            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Remember Me -->
                        <div>
                            <label for="remember_me" class="inline-flex items-center text-steel-200 cursor-pointer">
                                <input id="remember_me" type="checkbox" name="remember"
                                    class="rounded border-steel-600 bg-steel-800 text-accent-500 focus:ring-2 focus:ring-accent-500/20 focus:ring-offset-steel-900">
                                <span class="ml-3 font-medium">{{ __('Remember me') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            @if (Route::has('password.request'))
                                <a class="text-sm text-accent-400 hover:text-accent-300 transition-colors"
                                    href="{{ route('password.request') }}">
                                    {{ __('Forgot your password?') }}
                                </a>
                            @endif

                            <x-primary-button>
                                {{ __('Log in') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
