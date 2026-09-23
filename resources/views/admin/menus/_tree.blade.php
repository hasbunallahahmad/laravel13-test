<ul data-menu-level="{{ $level }}">
    @foreach ($menus as $menu)
        <li @class(['menu-item', 'has-parent' => $menu->parent_id !== null])
            @if ($menu->parent_id !== null) data-parent-id="{{ $menu->parent_id }}" @endif>
            <div class="grid gap-4 border-b border-zinc-100 px-5 py-4 transition hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800/50 md:grid-cols-[minmax(0,1fr)_120px_120px_80px_180px]"
                @if ($level > 0) style="padding-left: {{ $level * 2 + 1.25 }}rem;" @endif>
                {{-- Menu --}}
                <div class="min-w-0">
                    <div class="flex items-center gap-3">
                        @if ($level > 0)
                            <span class="shrink-0 text-zinc-300 dark:text-zinc-600" aria-hidden="true">
                                └─
                            </span>
                        @endif

                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                            @if ($menu->icon)
                                <span class="text-xs font-medium">
                                    {{ $menu->icon }}
                                </span>
                            @else
                                <span class="text-xs">☰</span>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ $menu->label }}
                            </div>

                            <div class="mt-0.5 truncate text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $menu->name }}
                            </div>
                        </div>
                    </div>

                    {{-- Detail --}}
                    <div class="mt-3 space-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                        <div>
                            <span class="font-medium text-zinc-600 dark:text-zinc-300">
                                Route:
                            </span>

                            {{ $menu->route_name ?? $menu->url }}
                        </div>

                        <div>
                            <span class="font-medium text-zinc-600 dark:text-zinc-300">
                                Target:
                            </span>

                            {{ $menu->target }}
                        </div>
                    </div>
                </div>

                {{-- Type --}}
                <div class="text-sm text-zinc-600 dark:text-zinc-300">
                    {{ $menu->type }}
                </div>

                {{-- Status --}}
                <div>
                    <span @class([
                        'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium',
                        'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' =>
                            $menu->is_active,
                        'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' => !$menu->is_active,
                    ])>
                        <span @class([
                            'mr-1.5 size-1.5 rounded-full',
                            'bg-emerald-500' => $menu->is_active,
                            'bg-zinc-400' => !$menu->is_active,
                        ])></span>

                        {{ $menu->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                {{-- Sort order --}}
                <div class="text-sm font-medium text-zinc-600 dark:text-zinc-300">
                    <span data-sort-order="{{ $menu->sort_order }}">
                        {{ $menu->sort_order }}
                    </span>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-start gap-2 md:justify-end">
                    @if ($menu->trashed())
                        @can('menus.restore')
                            <form action="{{ route('admin.menus.restore', $menu) }}" method="POST">
                                @csrf

                                <button type="submit"
                                    class="inline-flex items-center rounded-lg border border-zinc-200 px-3 py-1.5 text-xs font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                                    Restore
                                </button>
                            </form>
                        @endcan
                    @else
                        @can('menus.update')
                            <a href="{{ route('admin.menus.edit', $menu) }}"
                                class="inline-flex items-center rounded-lg border border-zinc-200 px-3 py-1.5 text-xs font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                                Edit
                            </a>
                        @endcan

                        @can('menus.delete')
                            <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                    class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
                                    Delete
                                </button>
                            </form>
                        @endcan
                    @endif
                </div>
            </div>

            {{-- Children --}}
            @if ($menu->children->isNotEmpty())
                @include('admin.menus._tree', [
                    'menus' => $menu->children,
                    'level' => $level + 1,
                ])
            @endif
        </li>
    @endforeach
</ul>
