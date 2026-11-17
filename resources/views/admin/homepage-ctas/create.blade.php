<x-layouts::admin>
    <x-slot name="title">
        Buat Homepage CTA
    </x-slot>

    <div class="space-y-6">
        <div>
            <h1 class="text-xl font-semibold">Tambah Homepage CTA</h1>
            <p class="mt-1 text-sm text-gray-500">
                Buat bagian Call to Action beserta tombol navigasinya.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.homepage-ctas.store') }}" class="space-y-6">
            @csrf

            {{-- Informasi utama CTA --}}
            <div class="rounded-lg border p-4 space-y-4">
                <h2 class="text-lg font-semibold">Informasi CTA</h2>

                <div>
                    <label for="title" class="block text-sm font-medium">
                        Judul CTA
                    </label>

                    <input id="title" type="text" name="title" value="{{ old('title') }}" maxlength="255" required class="mt-1 block w-full rounded-md border-gray-300">

                    @error('title')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium">
                        Deskripsi
                    </label>

                    <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300">{{ old('description') }}</textarea>

                    @error('description')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sort-order" class="block text-sm font-medium">
                        Urutan CTA
                    </label>

                    <input id="sort-order" type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" required class="mt-1 block w-full rounded-md border-gray-300">

                    @error('sort_order')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <input type="hidden" name="is_active" value="0">

                    <label for="is-active" class="inline-flex items-center gap-2">
                        <input id="is-active" type="checkbox" name="is_active" value="1" @checked(old('is_active', '1') === '1')>

                        CTA Aktif
                    </label>

                    @error('is_active')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Tombol CTA --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Tombol CTA</h2>

                    <button type="button" id="add-homepage-cta-button" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white">
                        Tambah Tombol
                    </button>
                </div>

                <div id="homepage-cta-buttons" class="space-y-4" data-button-index="0">
                    {{-- Tombol pertama --}}
                    <div class="homepage-cta-button rounded-lg border p-4 space-y-4" data-button-item>
                        <div>
                            <label for="button-label" class="block text-sm font-medium">
                                Label Tombol
                            </label>

                            <input id="button-label" type="text" name="buttons[0][label]" value="{{ old('buttons.0.label') }}" maxlength="100" required
                                class="mt-1 block w-full rounded-md border-gray-300">

                            @error('buttons.0.label')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="button-type" class="block text-sm font-medium">
                                Jenis Tujuan
                            </label>

                            <select id="button-type" name="buttons[0][type]" required class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="url" @selected(old('buttons.0.type', 'url') === 'url')>
                                    URL
                                </option>

                                <option value="route" @selected(old('buttons.0.type') === 'route')>
                                    Route
                                </option>
                            </select>

                            @error('buttons.0.type')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="button-url" class="block text-sm font-medium">
                                URL
                            </label>

                            <input id="button-url" type="text" name="buttons[0][url]" value="{{ old('buttons.0.url', '/layanan') }}" maxlength="2048"
                                class="mt-1 block w-full rounded-md border-gray-300">

                            @error('buttons.0.url')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="button-route-name" class="block text-sm font-medium">
                                Nama Route
                            </label>

                            <input id="button-route-name" type="text" name="buttons[0][route_name]" value="{{ old('buttons.0.route_name') }}" maxlength="100"
                                class="mt-1 block w-full rounded-md border-gray-300">

                            @error('buttons.0.route_name')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="button-target" class="block text-sm font-medium">
                                Target
                            </label>

                            <select id="button-target" name="buttons[0][target]" required class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="_self" @selected(old('buttons.0.target', '_self') === '_self')>
                                    Tab yang sama
                                </option>

                                <option value="_blank" @selected(old('buttons.0.target') === '_blank')>
                                    Tab baru
                                </option>
                            </select>

                            @error('buttons.0.target')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="button-sort-order" class="block text-sm font-medium">
                                Urutan Tombol
                            </label>

                            <input id="button-sort-order" type="number" name="buttons[0][sort_order]" value="{{ old('buttons.0.sort_order', 0) }}" min="0" required
                                class="mt-1 block w-full rounded-md border-gray-300">

                            @error('buttons.0.sort_order')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <input type="hidden" name="buttons[0][is_active]" value="0">

                            <label for="button-is-active" class="inline-flex items-center gap-2">
                                <input id="button-is-active" type="checkbox" name="buttons[0][is_active]" value="1" @checked(old('buttons.0.is_active', '1') === '1')>

                                Tombol Aktif
                            </label>
                        </div>

                        <button type="button" class="remove-homepage-cta-button rounded-md border px-3 py-2 text-sm" disabled>
                            Hapus Tombol
                        </button>
                    </div>
                </div>
            </div>

            {{-- Template untuk tombol tambahan --}}
            <template id="homepage-cta-button-template">
                <div class="homepage-cta-button rounded-lg border p-4 space-y-4" data-button-item>
                    <div>
                        <label class="block text-sm font-medium">Label Tombol</label>

                        <input type="text" name="buttons[__INDEX__][label]" maxlength="100" required class="mt-1 block w-full rounded-md border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Jenis Tujuan</label>

                        <select name="buttons[__INDEX__][type]" required class="mt-1 block w-full rounded-md border-gray-300">
                            <option value="url">URL</option>
                            <option value="route">Route</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">URL</label>

                        <input type="text" name="buttons[__INDEX__][url]" maxlength="2048" value="/layanan" class="mt-1 block w-full rounded-md border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Nama Route</label>

                        <input type="text" name="buttons[__INDEX__][route_name]" maxlength="100" class="mt-1 block w-full rounded-md border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Target</label>

                        <select name="buttons[__INDEX__][target]" required class="mt-1 block w-full rounded-md border-gray-300">
                            <option value="_self">Tab yang sama</option>
                            <option value="_blank">Tab baru</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Urutan Tombol</label>

                        <input type="number" name="buttons[__INDEX__][sort_order]" value="0" min="0" required class="mt-1 block w-full rounded-md border-gray-300">
                    </div>

                    <div>
                        <input type="hidden" name="buttons[__INDEX__][is_active]" value="0">

                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="buttons[__INDEX__][is_active]" value="1" checked>

                            Tombol Aktif
                        </label>
                    </div>

                    <button type="button" class="remove-homepage-cta-button rounded-md border px-3 py-2 text-sm">
                        Hapus Tombol
                    </button>
                </div>
            </template>

            {{-- Aksi form --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.homepage-ctas.index') }}" class="rounded-md border px-4 py-2 text-sm">
                    Batal
                </a>

                <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white">
                    Simpan
                </button>
            </div>
        </form>
    </div>

    {{-- JavaScript tambah dan hapus tombol --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('homepage-cta-buttons');
            const template = document.getElementById('homepage-cta-button-template');
            const addButton = document.getElementById('add-homepage-cta-button');

            if (!container || !template || !addButton) {
                return;
            }

            let nextIndex = Number(container.dataset.buttonIndex || 0);

            const updateRemoveButtons = () => {
                const items = container.querySelectorAll('[data-button-item]');

                items.forEach((item) => {
                    const removeButton = item.querySelector('.remove-homepage-cta-button');

                    if (removeButton) {
                        removeButton.disabled = items.length <= 1;
                    }
                });
            };

            addButton.addEventListener('click', () => {
                const html = template.innerHTML.replaceAll(
                    '__INDEX__',
                    String(nextIndex)
                );

                container.insertAdjacentHTML('beforeend', html);

                nextIndex++;
                container.dataset.buttonIndex = String(nextIndex - 1);

                updateRemoveButtons();
            });

            container.addEventListener('click', (event) => {
                const removeButton = event.target.closest(
                    '.remove-homepage-cta-button'
                );

                if (!removeButton) {
                    return;
                }

                const items = container.querySelectorAll('[data-button-item]');

                if (items.length <= 1) {
                    return;
                }

                removeButton.closest('[data-button-item]').remove();

                updateRemoveButtons();
            });

            updateRemoveButtons();
        });
    </script>
</x-layouts::admin>
