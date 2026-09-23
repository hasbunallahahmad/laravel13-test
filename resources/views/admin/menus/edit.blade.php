<x-layouts::admin :title="__('Edit Menu')">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                    <a href="{{ route('admin.menus.index') }}"
                        class="transition hover:text-zinc-900 dark:hover:text-white">
                        Menu Management
                    </a>

                    <span>/</span>

                    <span class="text-zinc-700 dark:text-zinc-300">
                        Edit
                    </span>
                </div>

                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                    Edit Menu
                </h1>

                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Perbarui konfigurasi menu
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">
                        {{ $menu->label }}
                    </span>.
                </p>
            </div>

            <a href="{{ route('admin.menus.index') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                Kembali
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-500/30 dark:bg-red-500/10">
                <div class="flex gap-3">
                    <div class="mt-0.5 shrink-0 text-red-600 dark:text-red-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m0 3.75h.008M10.29 3.86l-7.1 12.28A2 2 0 004.92 19h14.16a2 2 0 001.73-2.86L13.71 3.86a2 2 0 00-3.42 0z" />
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-red-800 dark:text-red-300">
                            Terdapat kesalahan
                        </h2>

                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700 dark:text-red-400">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.menus.update', $menu) }}" method="POST" data-menu-form>
            @csrf
            @method('PATCH')

            <div
                class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-6 py-5 dark:border-zinc-700">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-white">
                        Informasi Menu
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Informasi dasar yang digunakan untuk mengidentifikasi menu.
                    </p>
                </div>

                <div class="grid gap-6 p-6 md:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Name
                        </label>

                        <input id="name" type="text" name="name" value="{{ old('name', $menu->name) }}"
                            class="mt-2 block w-full rounded-lg border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-zinc-500 focus:ring-2 focus:ring-zinc-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">

                        @error('name')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="label" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Label
                        </label>

                        <input id="label" type="text" name="label" value="{{ old('label', $menu->label) }}"
                            class="mt-2 block w-full rounded-lg border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-zinc-500 focus:ring-2 focus:ring-zinc-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">

                        @error('label')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Type
                        </label>

                        <select id="type" name="type" data-menu-type
                            class="mt-2 block w-full rounded-lg border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-zinc-500 focus:ring-2 focus:ring-zinc-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                            <option value="route" @selected(old('type', $menu->type) === 'route')>
                                Route
                            </option>

                            <option value="url" @selected(old('type', $menu->type) === 'url')>
                                URL
                            </option>
                        </select>

                        @error('type')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="icon" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Icon
                        </label>

                        <input id="icon" type="text" name="icon" value="{{ old('icon', $menu->icon) }}"
                            class="mt-2 block w-full rounded-lg border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-zinc-500 focus:ring-2 focus:ring-zinc-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">

                        @error('icon')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div class="border-t border-zinc-200 px-6 py-5 dark:border-zinc-700">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-white">
                        Destination
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Tentukan tujuan yang akan dibuka ketika menu dipilih.
                    </p>
                </div>

                <div class="grid gap-6 p-6 md:grid-cols-2">
                    <div data-menu-route-field>
                        <label for="route_name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Route Name
                        </label>

                        <input id="route_name" type="text" name="route_name"
                            value="{{ old('route_name', $menu->route_name) }}" data-menu-route-input
                            class="mt-2 block w-full rounded-lg border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-zinc-500 focus:ring-2 focus:ring-zinc-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">

                        @error('route_name')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div data-menu-url-field>
                        <label for="url" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            URL
                        </label>

                        <input id="url" type="url" name="url" value="{{ old('url', $menu->url) }}"
                            data-menu-url-input
                            class="mt-2 block w-full rounded-lg border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-zinc-500 focus:ring-2 focus:ring-zinc-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">

                        @error('url')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="target" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Target
                        </label>

                        <select id="target" name="target"
                            class="mt-2 block w-full rounded-lg border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-zinc-500 focus:ring-2 focus:ring-zinc-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                            <option value="_self" @selected(old('target', $menu->target) === '_self')>
                                Same Window
                            </option>

                            <option value="_blank" @selected(old('target', $menu->target) === '_blank')>
                                New Window
                            </option>
                        </select>

                        @error('target')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div class="border-t border-zinc-200 px-6 py-5 dark:border-zinc-700">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-white">
                        Struktur Menu
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Atur posisi menu dalam struktur navigasi.
                    </p>
                </div>

                <div class="grid gap-6 p-6 md:grid-cols-2">
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Parent Menu
                        </label>

                        <select id="parent_id" name="parent_id"
                            class="mt-2 block w-full rounded-lg border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-zinc-500 focus:ring-2 focus:ring-zinc-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                            <option value="">-- No Parent --</option>

                            @foreach ($parentMenus as $parentMenu)
                                @if (!in_array($parentMenu->id, $descendantIds, true))
                                    <option value="{{ $parentMenu->id }}" @selected(old('parent_id', $menu->parent_id) == $parentMenu->id)>
                                        {{ $parentMenu->label }}
                                    </option>
                                @endif
                            @endforeach
                        </select>

                        @error('parent_id')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Sort Order
                        </label>

                        <input id="sort_order" type="number" name="sort_order" min="0"
                            value="{{ old('sort_order', $menu->sort_order) }}"
                            class="mt-2 block w-full rounded-lg border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-zinc-500 focus:ring-2 focus:ring-zinc-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">

                        @error('sort_order')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="is_active" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Status
                        </label>

                        @php
                            $activeValue = old('is_active', $menu->is_active ? '1' : '0');
                        @endphp

                        <select id="is_active" name="is_active"
                            class="mt-2 block w-full rounded-lg border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-zinc-500 focus:ring-2 focus:ring-zinc-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                            <option value="1" @selected((string) $activeValue === '1')>
                                Aktif
                            </option>

                            <option value="0" @selected((string) $activeValue === '0')>
                                Tidak Aktif
                            </option>
                        </select>

                        @error('is_active')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div
                    class="flex flex-col-reverse gap-3 border-t border-zinc-200 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-800/40 sm:flex-row sm:justify-end">
                    <a href="{{ route('admin.menus.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                        Batal
                    </a>

                    <button type="submit">Simpan</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        (() => {
            const form = document.querySelector('[data-menu-form]');

            if (!form) {
                return;
            }

            const type = form.querySelector('[data-menu-type]');
            const routeField = form.querySelector('[data-menu-route-field]');
            const routeInput = form.querySelector('[data-menu-route-input]');
            const urlField = form.querySelector('[data-menu-url-field]');
            const urlInput = form.querySelector('[data-menu-url-input]');

            const syncDestinationFields = () => {
                const isRoute = type?.value === 'route';

                routeField?.classList.toggle('hidden', !isRoute);
                urlField?.classList.toggle('hidden', isRoute);

                if (routeInput) {
                    routeInput.disabled = !isRoute;
                }

                if (urlInput) {
                    urlInput.disabled = isRoute;
                }
            };

            type?.addEventListener('change', syncDestinationFields);

            syncDestinationFields();
        })();
    </script>
</x-layouts::admin>
