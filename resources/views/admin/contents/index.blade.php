<x-layouts::admin :title="__('Content')">
    <div class="space-y-6">

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <flux:heading size="xl">
                    {{ __('Content') }}
                </flux:heading>

                <flux:text class="mt-2">
                    {{ __('Manage website content and publications.') }}
                </flux:text>
            </div>

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
        </div>

        {{-- Success Message --}}
        @if (session('success'))
            <flux:callout variant="success" icon="check-circle">
                {{ session('success') }}
            </flux:callout>
        @endif

        {{-- Content Table / Empty State --}}
        <div class="overflow-hidden rounded-xl border border-app-border bg-app-surface">

            @if ($contents->isEmpty())

                {{-- Empty State --}}
                <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                    <flux:icon.document-text class="mb-4 size-12 text-app-text-muted" />

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
                {{-- Content Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-app-border">

                        <thead class="bg-app-surface-muted">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-app-text-muted">
                                    {{ __('Content') }}
                                </th>

                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-app-text-muted">
                                    {{ __('Type') }}
                                </th>

                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-app-text-muted">
                                    {{ __('Status') }}
                                </th>

                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-app-text-muted">
                                    {{ __('Author') }}
                                </th>

                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-app-text-muted">
                                    {{ __('Published') }}
                                </th>

                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">
                                        {{ __('Actions') }}
                                    </span>
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-app-border">

                            @foreach ($contents as $content)
                                <tr class="hover:bg-app-surface-muted">

                                    {{-- Content --}}
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="max-w-sm">
                                            <div class="truncate font-medium text-app">
                                                {{ $content->title }}
                                            </div>

                                            <div class="mt-1 truncate text-sm text-app-text-muted">
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
                                        <div class="text-sm text-app">
                                            {{ $content->author?->name ?? __('Unknown') }}
                                        </div>
                                    </td>

                                    {{-- Published --}}
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-app-text-muted">
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
                    <div class="border-t border-app-border px-6 py-4">
                        {{ $contents->links() }}
                    </div>
                @endif

            @endif

        </div>
    </div>
</x-layouts::admin>
