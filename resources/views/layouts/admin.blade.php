<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-app-background text-app-text">

    <flux:sidebar sticky collapsible="mobile" class="border-r border-app-border bg-app-surface">

        <flux:sidebar.header class="border-b border-app-border">
            <x-app-logo :sidebar="true" href="{{ route('admin.dashboard') }}" wire:navigate />

            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav class="px-2 py-3">
            <flux:sidebar.group :heading="__('Administration')" class="grid">

                @can('dashboard.view')
                    <flux:sidebar.item icon="layout-grid" :href="route('admin.dashboard')"
                        :current="request()->routeIs('admin.dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                @endcan

                @can('content.view')
                    <flux:sidebar.item icon="document-text" :href="route('admin.contents.index')"
                        :current="request()->routeIs('admin.contents.*')" wire:navigate>
                        {{ __('Content') }}
                    </flux:sidebar.item>
                @endcan

                @can('media.view')
                    <flux:sidebar.item icon="photo" :href="route('admin.media.index')"
                        :current="request()->routeIs('admin.media.*')" wire:navigate>
                        {{ __('Media') }}
                    </flux:sidebar.item>
                @endcan

                @can('menus.view')
                    <flux:sidebar.item icon="bars-3" :href="route('admin.menus.index')"
                        :current="request()->routeIs('admin.menus.*')" wire:navigate>
                        {{ __('Menu') }}
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
            <div class="border-t border-app-border p-2">
                <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
            </div>
        @endauth

    </flux:sidebar>

    <flux:header class="border-b border-app-border bg-app-surface lg:hidden">
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
