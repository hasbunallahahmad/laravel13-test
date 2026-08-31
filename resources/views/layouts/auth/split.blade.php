<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')

    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>

<body class="min-h-screen bg-white antialiased dark:bg-neutral-950">

    <div class="grid min-h-screen lg:grid-cols-2">

        {{-- ============================================
             LEFT / VISUAL PANEL
        ============================================= --}}
        <section class="relative hidden overflow-hidden lg:block">

            {{-- Background Image --}}
            <div class="absolute inset-0 bg-cover bg-center"
                style="
                    background-image:
                    linear-gradient(
                        rgba(8, 18, 35, 0.80),
                        rgba(8, 18, 35, 0.88)
                    ),
                    url('{{ asset('images/auth/login-cover.jpg') }}');
                ">
            </div>

            {{-- Content --}}
            <div class="relative z-10 flex h-full flex-col justify-between p-12 text-white">

                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 backdrop-blur">
                        <x-app-logo-icon class="h-7 w-7 fill-current text-white" />
                    </div>

                    <div>
                        <div class="text-lg font-semibold">
                            {{ config('app.name') }}
                        </div>

                        <div class="text-xs text-white/60">
                            Content Management Platform
                        </div>
                    </div>
                </a>


                {{-- Main Marketing Content --}}
                <div class="max-w-xl">

                    <h1 class="text-5xl font-semibold leading-tight tracking-tight">
                        Manage your digital content
                        <span class="text-white/60">
                            without limits.
                        </span>
                    </h1>

                    <p class="mt-6 max-w-lg text-lg leading-8 text-white/70">
                        A flexible digital platform designed to manage
                        content, media, users and experiences from one
                        powerful dashboard.
                    </p>

                </div>


                {{-- Footer --}}
                <div class="flex items-center justify-between text-sm text-white/50">

                    <span>
                        © {{ date('Y') }}
                        {{ config('app.name') }}
                    </span>

                    <span>
                        Secure Content Platform
                    </span>

                </div>

            </div>

        </section>


        {{-- ============================================
             RIGHT / LOGIN PANEL
        ============================================= --}}
        <section class="flex min-h-screen items-center justify-center px-6 py-12 sm:px-12 lg:px-20">

            <div class="w-full max-w-md">

                {{-- Mobile Logo --}}
                <a href="{{ route('home') }}" class="mb-10 flex items-center gap-3 lg:hidden">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-neutral-900 text-white dark:bg-white dark:text-neutral-900">
                        <x-app-logo-icon class="h-7 w-7 fill-current" />
                    </div>

                    <div class="font-semibold">
                        {{ config('app.name') }}
                    </div>
                </a>


                {{ $slot }}

            </div>

        </section>

    </div>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts

</body>

</html>
