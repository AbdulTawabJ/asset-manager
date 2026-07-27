<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Demo credentials hint (public demo) -->
    <div class="mb-4 rounded-md border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900">
        <p class="font-semibold">🔎 Demo credentials</p>
        <p class="mt-1"><span class="font-medium">Admin</span> — <code>admin@tmf.demo</code> / <code>password</code></p>
        <p><span class="font-medium">IT Officer</span> — <code>it@tmf.demo</code> / <code>password</code></p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>


        <div class="flex items-center justify-center mt-4 w-full">
            <x-primary-button class="mt-4 w-full bg-yellow-700 hover:bg-yellow-600 justify-center">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
