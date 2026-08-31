<x-layouts::auth.split :title="__('Sign in')">

    <div class="flex flex-col gap-6">

        <div>
            <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">
                Dynamic CMS
            </p>

            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-neutral-900 dark:text-white">
                Welcome back
            </h1>

            <p class="mt-2 text-sm leading-6 text-neutral-600 dark:text-neutral-400">
                Sign in to access your dashboard and manage your digital content.
            </p>
        </div>

        {{-- Session Status --}}
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

            {{-- Email --}}
            <flux:input name="email" label="Email address" :value="old('email')" type="email" required autofocus
                autocomplete="email" placeholder="name@example.com" />

            {{-- Password --}}
            <div class="flex flex-col gap-2">

                <div class="flex items-center justify-between">
                    <label for="password" class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
                        Password
                    </label>

                    @if (Route::has('password.request'))
                        <flux:link href="{{ route('password.request') }}" class="text-sm" wire:navigate>
                            Forgot password?
                        </flux:link>
                    @endif
                </div>

                <flux:input id="password" name="password" type="password" required autocomplete="current-password"
                    placeholder="Enter your password" viewable />

            </div>

            {{-- Cloudflare Turnstile --}}
            <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>

            @error('cf-turnstile-response')
                <p class="text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror

            {{-- Remember --}}
            <flux:checkbox name="remember" label="Remember me" :checked="old('remember')" />

            {{-- Submit --}}
            <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                Sign in
            </flux:button>

        </form>

    </div>

</x-layouts::auth.split>
