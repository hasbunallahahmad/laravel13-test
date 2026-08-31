<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">

    <flux:sidebar sticky collapsible="mobile">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('admin.dashboard') }}" wire:navigate />

            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.group :heading="__('Administration')" class="grid">
                @can('dashboard.view')
                    <flux:sidebar.item icon="layout-grid" :href="route('admin.dashboard')"
                        :current="request()->routeIs('admin.dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                @endcan

                @can('settings.view')
                    <flux:sidebar.item icon="cog-6-tooth" :href="route('admin.settings.index')"
                        :current="request()->routeIs('admin.settings.*')" wire:navigate>
                        {{ __('Settings') }}
                    </flux:sidebar.item>
                @endcan
            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:spacer />

        @auth
            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        @endauth
    </flux:sidebar>

    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />
    </flux:header>

    <flux:main>
        {{ $slot }}
    </flux:main>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>

</html>
