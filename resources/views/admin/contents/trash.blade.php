<x-layouts::admin :title="__('Deleted Content')">
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">
                    {{ __('Deleted Content') }}
                </flux:heading>

                <flux:text class="mt-2">
                    {{ __('Content yang telah dipindahkan ke trash.') }}
                </flux:text>
            </div>

            <flux:button :href="route('admin.contents.index')" variant="ghost" icon="arrow-left">
                {{ __('Back to Content') }}
            </flux:button>
        </div>

        {{-- Success Message --}}
        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">
                {{ session('success') }}
            </flux:callout>
        @endif

        {{-- Error Message --}}
        @if (session('error'))
            <flux:callout variant="danger" icon="exclamation-triangle">
                {{ session('error') }}
            </flux:callout>
        @endif

        {{-- Trash Table --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">

            @if ($contents->isEmpty())

                {{-- Empty State --}}
                <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                    <flux:icon.trash class="mb-4 size-12 text-zinc-400" />

                    <flux:heading size="lg">
                        {{ __('Trash is empty') }}
                    </flux:heading>

                    <flux:text class="mt-2 max-w-md">
                        {{ __('Tidak ada content yang berada di trash.') }}
                    </flux:text>

                    <flux:button :href="route('admin.contents.index')" variant="primary" icon="arrow-left"
                        class="mt-6">
                        {{ __('Back to Content') }}
                    </flux:button>
                </div>
            @else
                {{-- Desktop Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">

                        <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">
                                    {{ __('Content') }}
                                </th>

                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">
                                    {{ __('Author') }}
                                </th>

                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">
                                    {{ __('Deleted') }}
                                </th>

                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">
                                        {{ __('Actions') }}
                                    </span>
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">

                            @foreach ($contents as $content)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">

                                    {{-- Content --}}
                                    <td class="px-6 py-4">
                                        <div class="max-w-sm">
                                            <div class="truncate font-medium text-zinc-900 dark:text-white">
                                                {{ $content->title }}
                                            </div>

                                            <div class="mt-1 truncate text-sm text-zinc-500">
                                                /{{ $content->slug }}
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Author --}}
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-zinc-900 dark:text-white">
                                            {{ $content->author?->name ?? __('Unknown') }}
                                        </div>
                                    </td>

                                    {{-- Deleted At --}}
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-zinc-500">
                                            {{ $content->deleted_at?->format('d M Y H:i') }}
                                        </div>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">

                                            @can('restore', $content)
                                                <form method="POST"
                                                    action="{{ route('admin.contents.restore', $content) }}">
                                                    @csrf
                                                    @method('PATCH')

                                                    <flux:button type="submit" variant="ghost" size="sm"
                                                        icon="arrow-path">
                                                        {{ __('Restore') }}
                                                    </flux:button>
                                                </form>
                                            @endcan

                                            @can('forceDelete', $content)
                                                <form method="POST"
                                                    action="{{ route('admin.contents.force-destroy', $content) }}"
                                                    onsubmit="return confirm('{{ __('Are you sure you want to permanently delete this content? This action cannot be undone.') }}')">
                                                    @csrf
                                                    @method('DELETE')

                                                    <flux:button type="submit" variant="ghost" size="sm"
                                                        icon="trash">
                                                        {{ __('Delete Permanently') }}
                                                    </flux:button>
                                                </form>
                                            @endcan

                                        </div>
                                    </td>

                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($contents->hasPages())
                    <div class="border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        {{ $contents->links() }}
                    </div>
                @endif

            @endif

        </div>
    </div>
</x-layouts::admin>
