<x-app-layout :seo="$seo ?? null">
    <x-slot name="title">Register</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                Register
            </h2>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:mt-4">
            <div class="flex items-center justify-center">
                <div class="w-full sm:max-w-md px-6 py-6 bg-gradient-to-br from-steel-800 to-steel-850 shadow-lg shadow-black/20 overflow-hidden rounded-xl border border-steel-700/50">
                    <form method="POST" action="{{ route('register') }}" class="space-y-6">
                        @csrf

                        <!-- Username -->
                        <div>
                            <x-input-label for="username" :value="__('Username')" class="mb-2" />
                            <x-text-input id="username" type="text" name="username" :value="old('username')" required autofocus autocomplete="username" />
                            <x-input-error :messages="$errors->get('username')" class="mt-2" />
                        </div>

                        <!-- Email Address -->
                        <div>
                            <x-input-label for="email" :value="__('Email')" class="mb-2" />
                            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="email" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <x-honeypot />

                        <!-- Password -->
                        <div>
                            <x-input-label for="password" :value="__('Password')" class="mb-2" />
                            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="mb-2" />
                            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-between">
                            <a class="text-sm text-accent-400 hover:text-accent-300 transition-colors"
                                href="{{ route('login') }}">
                                {{ __('Already registered?') }}
                            </a>

                            <x-primary-button>
                                {{ __('Register') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
