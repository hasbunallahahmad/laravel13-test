<x-layouts::admin :title="__('Content')">
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-wrap gap-2">

            <flux:button :href="route('admin.contents.trash')" variant="ghost" icon="trash">
                {{ __('Trash') }}
            </flux:button>

            @can('create', \App\Models\Content::class)
                <flux:button :href="route('admin.contents.create')" variant="primary" icon="plus">
                    {{ __('Create Content') }}
                </flux:button>
            @endcan

        </div>

        {{-- Success Message --}}
        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">
                {{ session('success') }}
            </flux:callout>
        @endif

        {{-- Content Table --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">

            @if ($contents->isEmpty())

                {{-- Empty State --}}
                <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                    <flux:icon.document-text class="mb-4 size-12 text-zinc-400" />

                    <flux:heading size="lg">
                        {{ __('No content found') }}
                    </flux:heading>

                    <flux:text class="mt-2 max-w-md">
                        {{ __('There is no content available yet.') }}
                    </flux:text>

                    @can('create', \App\Models\Content::class)
                        <flux:button :href="route('admin.contents.create')" variant="primary" icon="plus" class="mt-6">
                            {{ __('Create your first content') }}
                        </flux:button>
                    @endcan
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
                                    {{ __('Type') }}
                                </th>

                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">
                                    {{ __('Status') }}
                                </th>

                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">
                                    {{ __('Author') }}
                                </th>

                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">
                                    {{ __('Published') }}
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
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="max-w-sm">
                                            <div class="truncate font-medium text-zinc-900 dark:text-white">
                                                {{ $content->title }}
                                            </div>

                                            <div class="mt-1 truncate text-sm text-zinc-500">
                                                /{{ $content->slug }}
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Type --}}
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <flux:badge variant="outline">
                                            {{ $content->type->value }}
                                        </flux:badge>
                                    </td>

                                    {{-- Status --}}
                                    <td class="whitespace-nowrap px-6 py-4">
                                        @php
                                            $statusVariant = match ($content->status) {
                                                \App\Enums\ContentStatus::PUBLISHED => 'success',
                                                \App\Enums\ContentStatus::REVIEW => 'warning',
                                                \App\Enums\ContentStatus::ARCHIVED => 'danger',
                                                default => 'outline',
                                            };
                                        @endphp

                                        <flux:badge :variant="$statusVariant">
                                            {{ ucfirst($content->status->value) }}
                                        </flux:badge>
                                    </td>

                                    {{-- Author --}}
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm text-zinc-900 dark:text-white">
                                            {{ $content->author?->name ?? __('Unknown') }}
                                        </div>
                                    </td>

                                    {{-- Published --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500">
                                        {{ $content->published_at?->format('d M Y H:i') ?? '—' }}
                                    </td>

                                    {{-- Actions --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">

                                            @can('update', $content)
                                                <flux:button :href="route('admin.contents.edit', $content)" variant="ghost"
                                                    size="sm" icon="pencil">
                                                    {{ __('Edit') }}
                                                </flux:button>
                                            @endcan

                                            @can('delete', $content)
                                                <form method="POST"
                                                    action="{{ route('admin.contents.destroy', $content) }}"
                                                    onsubmit="return confirm('{{ __('Are you sure you want to delete this content?') }}')">
                                                    @csrf
                                                    @method('DELETE')

                                                    <flux:button type="submit" variant="ghost" size="sm"
                                                        icon="trash">
                                                        {{ __('Delete') }}
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
