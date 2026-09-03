<x-layouts::admin :title="__('Settings')">
    <div class="space-y-6">

        {{-- Header --}}
        <div>
            <flux:heading size="xl">
                {{ __('Settings') }}
            </flux:heading>

            <flux:text class="mt-2">
                {{ __('Manage website settings.') }}
            </flux:text>
        </div>

        {{-- General --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">
                {{ __('General') }}
            </flux:heading>

            <flux:text class="mt-1">
                {{ __('Basic information about the website.') }}
            </flux:text>

            <div class="mt-6 space-y-4">
                @forelse ($settings->where('group', 'general') as $setting)
                    <div
                        class="flex items-center justify-between gap-4 border-b border-zinc-200 py-3 last:border-0 dark:border-zinc-700">

                        <div>
                            <div class="font-medium text-zinc-900 dark:text-white">
                                {{ str($setting->key)->replace('_', ' ')->title() }}
                            </div>

                            <div class="text-sm text-zinc-500">
                                @if ($setting->type === 'boolean')
                                    {{ $setting->value ? __('Enabled') : __('Disabled') }}
                                @else
                                    {{ $setting->value ?? __('Not configured') }}
                                @endif
                            </div>
                        </div>

                        @can('settings.update')
                            <flux:button size="sm" variant="ghost" icon="pencil">
                                {{ __('Edit') }}
                            </flux:button>
                        @endcan

                    </div>
                @empty
                    <flux:text>
                        {{ __('No general settings available.') }}
                    </flux:text>
                @endforelse
            </div>
        </div>

        {{-- Appearance --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">
                {{ __('Appearance') }}
            </flux:heading>

            <flux:text class="mt-1">
                {{ __('Manage the visual identity of the website.') }}
            </flux:text>

            <div class="mt-6 space-y-4">
                @forelse ($settings->where('group', 'appearance') as $setting)
                    <div
                        class="flex items-center justify-between gap-4 border-b border-zinc-200 py-3 last:border-0 dark:border-zinc-700">

                        <div>
                            <div class="font-medium text-zinc-900 dark:text-white">
                                {{ str($setting->key)->replace('_', ' ')->title() }}
                            </div>

                            <div class="text-sm text-zinc-500">
                                @if ($setting->type === 'boolean')
                                    {{ $setting->value ? __('Enabled') : __('Disabled') }}
                                @else
                                    {{ $setting->value ?? __('Not configured') }}
                                @endif
                            </div>
                        </div>

                        @can('settings.update')
                            <flux:button size="sm" variant="ghost" icon="pencil">
                                {{ __('Edit') }}
                            </flux:button>
                        @endcan

                    </div>
                @empty
                    <flux:text>
                        {{ __('No appearance settings available.') }}
                    </flux:text>
                @endforelse
            </div>
        </div>

        {{-- Social Media --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">
                {{ __('Social Media') }}
            </flux:heading>

            <flux:text class="mt-1">
                {{ __('Manage social media links.') }}
            </flux:text>

            <div class="mt-6 space-y-4">
                @forelse ($settings->where('group', 'social') as $setting)
                    <div
                        class="flex items-center justify-between gap-4 border-b border-zinc-200 py-3 last:border-0 dark:border-zinc-700">

                        <div>
                            <div class="font-medium text-zinc-900 dark:text-white">
                                {{ str($setting->key)->replace('_', ' ')->title() }}
                            </div>

                            <div class="text-sm text-zinc-500">
                                @if ($setting->type === 'boolean')
                                    {{ $setting->value ? __('Enabled') : __('Disabled') }}
                                @else
                                    {{ $setting->value ?? __('Not configured') }}
                                @endif
                            </div>
                        </div>

                        @can('settings.update')
                            <flux:button size="sm" variant="ghost" icon="pencil">
                                {{ __('Edit') }}
                            </flux:button>
                        @endcan

                    </div>
                @empty
                    <flux:text>
                        {{ __('No social media settings available.') }}
                    </flux:text>
                @endforelse
            </div>
        </div>

        {{-- System --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">
                {{ __('System') }}
            </flux:heading>

            <flux:text class="mt-1">
                {{ __('Manage system configuration.') }}
            </flux:text>

            <div class="mt-6 space-y-4">
                @forelse ($settings->where('group', 'system') as $setting)
                    <div
                        class="flex items-center justify-between gap-4 border-b border-zinc-200 py-3 last:border-0 dark:border-zinc-700">

                        <div>
                            <div class="font-medium text-zinc-900 dark:text-white">
                                {{ str($setting->key)->replace('_', ' ')->title() }}
                            </div>

                            <div class="text-sm text-zinc-500">
                                @if ($setting->type === 'boolean')
                                    {{ $setting->value ? __('Enabled') : __('Disabled') }}
                                @else
                                    {{ $setting->value ?? __('Not configured') }}
                                @endif
                            </div>
                        </div>

                        @can('settings.update')
                            <flux:button size="sm" variant="ghost" icon="pencil">
                                {{ __('Edit') }}
                            </flux:button>
                        @endcan

                    </div>
                @empty
                    <flux:text>
                        {{ __('No system settings available.') }}
                    </flux:text>
                @endforelse
            </div>
        </div>

    </div>

</x-layouts::admin>
