<x-layouts::admin :title="__('Admin Dashboard')">
    <div class="space-y-6">
        {{-- Page Header --}}
        <div>
            <flux:heading size="xl">
                {{ __('Admin Dashboard') }}
            </flux:heading>

            <flux:text class="mt-2">
                {{ __('Manage the website and its content.') }}
            </flux:text>
        </div>

        {{-- Statistics --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Users --}}
            <div data-statistic="users" class="rounded-xl border border-app-border bg-app-surface p-5">
                <div class="flex items-center justify-between gap-4">
                    <flux:text class="text-sm font-medium">
                        {{ __('Users') }}
                    </flux:text>

                    <flux:icon name="users" class="size-5 text-app-text-muted" />
                </div>

                <div class="mt-3">
                    <flux:heading size="xl">
                        {{ $statistics['users'] }}
                    </flux:heading>
                </div>
            </div>

            {{-- Content --}}
            <div data-statistic="contents" class="rounded-xl border border-app-border bg-app-surface p-5">
                <div class="flex items-center justify-between gap-4">
                    <flux:text class="text-sm font-medium">
                        {{ __('Content') }}
                    </flux:text>

                    <flux:icon name="document-text" class="size-5 text-app-text-muted" />
                </div>

                <div class="mt-3">
                    <flux:heading size="xl">
                        {{ $statistics['contents'] }}
                    </flux:heading>
                </div>
            </div>

            {{-- Media --}}
            <div data-statistic="media" class="rounded-xl border border-app-border bg-app-surface p-5">
                <div class="flex items-center justify-between gap-4">
                    <flux:text class="text-sm font-medium">
                        {{ __('Media') }}
                    </flux:text>

                    <flux:icon name="photo" class="size-5 text-app-text-muted" />
                </div>

                <div class="mt-3">
                    <flux:heading size="xl">
                        {{ $statistics['media'] }}
                    </flux:heading>
                </div>
            </div>

            {{-- Menu --}}
            <div data-statistic="menus" class="rounded-xl border border-app-border bg-app-surface p-5">
                <div class="flex items-center justify-between gap-4">
                    <flux:text class="text-sm font-medium">
                        {{ __('Menu') }}
                    </flux:text>

                    <flux:icon name="bars-3" class="size-5 text-app-text-muted" />
                </div>

                <div class="mt-3">
                    <flux:heading size="xl">
                        {{ $statistics['menus'] }}
                    </flux:heading>
                </div>
            </div>
        </div>
    </div>
</x-layouts::admin>
