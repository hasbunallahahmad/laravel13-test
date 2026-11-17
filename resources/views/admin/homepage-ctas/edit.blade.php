<x-layouts::admin>
    <x-slot name="title">
        Edit Homepage CTA
    </x-slot>

    @php
        /*
         * Gunakan old input jika validasi sebelumnya gagal.
         * Jika tidak ada old input, gunakan data dari database.
         */
        $buttonInputs = old('buttons');

        if (!is_array($buttonInputs)) {
            $buttonInputs = $homepageCta->buttons
                ->map(
                    fn($button) => [
                        'uuid' => $button->uuid,
                        'label' => $button->label,
                        'type' => $button->type,
                        'url' => $button->url,
                        'route_name' => $button->route_name,
                        'target' => $button->target,
                        'sort_order' => $button->sort_order,
                        'is_active' => $button->is_active,
                    ],
                )
                ->all();
        }
    @endphp

    <div class="space-y-6">
        {{-- Header --}}
        <div>
            <h1 class="text-xl font-semibold">Edit Homepage CTA</h1>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui informasi Homepage CTA beserta tombol navigasinya.
            </p>
        </div>

        {{-- Form utama --}}
        <form method="POST" action="{{ route('admin.homepage-ctas.update', $homepageCta) }}" class="space-y-6">
            @csrf
            @method('PATCH')

            {{-- Informasi utama CTA --}}
            <div class="space-y-4 rounded-lg border p-4">
                <h2 class="text-lg font-semibold">Informasi CTA</h2>

                {{-- Judul --}}
                <div>
                    <label for="title" class="block text-sm font-medium">
                        Judul CTA
                    </label>

                    <input id="title" type="text" name="title" value="{{ old('title', $homepageCta->title) }}" maxlength="255" required class="mt-1 w-full rounded-md border-gray-300" />

                    @error('title')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label for="description" class="block text-sm font-medium">
                        Deskripsi
                    </label>

                    <textarea id="description" name="description" rows="4" class="mt-1 w-full rounded-md border-gray-300">{{ old('description', $homepageCta->description) }}</textarea>

                    @error('description')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Urutan --}}
                <div>
                    <label for="sort_order" class="block text-sm font-medium">
                        Urutan CTA
                    </label>

                    <input id="sort_order" type="number" name="sort_order" min="0" value="{{ old('sort_order', $homepageCta->sort_order) }}" required
                        class="mt-1 w-full rounded-md border-gray-300" />

                    @error('sort_order')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Status --}}
                <div>
                    <input type="hidden" name="is_active" value="0" />

                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $homepageCta->is_active)) />

                        <span class="text-sm font-medium">
                            CTA Aktif
                        </span>
                    </label>

                    @error('is_active')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            {{-- Daftar tombol CTA --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">
                        Daftar Tombol
                    </h2>

                    <button type="button" id="add-homepage-cta-button" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white">
                        Tambah Tombol
                    </button>
                </div>

                <div id="homepage-cta-buttons" class="space-y-4" data-button-index="{{ count($buttonInputs) }}">
                    @foreach ($buttonInputs as $index => $button)
                        <div class="homepage-cta-button space-y-4 rounded-lg border p-4" data-button-item>
                            {{-- UUID tombol lama --}}
                            @if (!empty($button['uuid']))
                                <input type="hidden" name="buttons[{{ $index }}][uuid]" value="{{ $button['uuid'] }}" />
                            @endif

                            {{-- Label --}}
                            <div>
                                <label class="block text-sm font-medium">
                                    Label Tombol
                                </label>

                                <input type="text" name="buttons[{{ $index }}][label]" value="{{ old("buttons.$index.label", $button['label'] ?? '') }}" maxlength="100" required
                                    class="mt-1 w-full rounded-md border-gray-300" />

                                @error("buttons.$index.label")
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Jenis tujuan --}}
                            <div>
                                <label class="block text-sm font-medium">
                                    Jenis Tujuan
                                </label>

                                <select name="buttons[{{ $index }}][type]" required class="mt-1 w-full rounded-md border-gray-300">
                                    <option value="url" @selected(old("buttons.$index.type", $button['type'] ?? 'url') === 'url')>
                                        URL
                                    </option>

                                    <option value="route" @selected(old("buttons.$index.type", $button['type'] ?? 'url') === 'route')>
                                        Route
                                    </option>
                                </select>

                                @error("buttons.$index.type")
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- URL --}}
                            <div>
                                <label class="block text-sm font-medium">
                                    URL
                                </label>

                                <input type="text" name="buttons[{{ $index }}][url]" value="{{ old("buttons.$index.url", $button['url'] ?? '') }}" maxlength="2048"
                                    class="mt-1 w-full rounded-md border-gray-300" />

                                @error("buttons.$index.url")
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Nama route --}}
                            <div>
                                <label class="block text-sm font-medium">
                                    Nama Route
                                </label>

                                <input type="text" name="buttons[{{ $index }}][route_name]" value="{{ old("buttons.$index.route_name", $button['route_name'] ?? '') }}" maxlength="100"
                                    class="mt-1 w-full rounded-md border-gray-300" />

                                @error("buttons.$index.route_name")
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Target --}}
                            <div>
                                <label class="block text-sm font-medium">
                                    Target
                                </label>

                                <select name="buttons[{{ $index }}][target]" required class="mt-1 w-full rounded-md border-gray-300">
                                    <option value="_self" @selected(old("buttons.$index.target", $button['target'] ?? '_self') === '_self')>
                                        Tab yang sama
                                    </option>

                                    <option value="_blank" @selected(old("buttons.$index.target", $button['target'] ?? '_self') === '_blank')>
                                        Tab baru
                                    </option>
                                </select>

                                @error("buttons.$index.target")
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Urutan tombol --}}
                            <div>
                                <label class="block text-sm font-medium">
                                    Urutan Tombol
                                </label>

                                <input type="number" name="buttons[{{ $index }}][sort_order]" min="0" value="{{ old("buttons.$index.sort_order", $button['sort_order'] ?? 0) }}"
                                    required class="mt-1 w-full rounded-md border-gray-300" />

                                @error("buttons.$index.sort_order")
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Status tombol --}}
                            <div>
                                <input type="hidden" name="buttons[{{ $index }}][is_active]" value="0" />

                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="buttons[{{ $index }}][is_active]" value="1" @checked((bool) old("buttons.$index.is_active", $button['is_active'] ?? false)) />

                                    <span class="text-sm font-medium">
                                        Tombol Aktif
                                    </span>
                                </label>

                                @error("buttons.$index.is_active")
                                    <p class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Hapus tombol --}}
                            <button type="button" class="remove-homepage-cta-button rounded-md border px-3 py-2 text-sm">
                                Hapus Tombol
                            </button>
                        </div>
                    @endforeach
                </div>

                @error('buttons')
                    <p class="text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Template tombol baru --}}
            <template id="homepage-cta-button-template">
                <div class="homepage-cta-button space-y-4 rounded-lg border p-4" data-button-item>
                    <div>
                        <label class="block text-sm font-medium">
                            Label Tombol
                        </label>

                        <input type="text" name="buttons[__INDEX__][label]" maxlength="100" required class="mt-1 w-full rounded-md border-gray-300" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium">
                            Jenis Tujuan
                        </label>

                        <select name="buttons[__INDEX__][type]" required class="mt-1 w-full rounded-md border-gray-300">
                            <option value="url">URL</option>
                            <option value="route">Route</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">
                            URL
                        </label>

                        <input type="text" name="buttons[__INDEX__][url]" value="/layanan" maxlength="2048" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium">
                            Nama Route
                        </label>

                        <input type="text" name="buttons[__INDEX__][route_name]" maxlength="100" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium">
                            Target
                        </label>

                        <select name="buttons[__INDEX__][target]" required class="mt-1 w-full rounded-md border-gray-300">
                            <option value="_self">Tab yang sama</option>
                            <option value="_blank">Tab baru</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">
                            Urutan Tombol
                        </label>

                        <input type="number" name="buttons[__INDEX__][sort_order]" value="0" min="0" required class="mt-1 w-full rounded-md border-gray-300" />
                    </div>

                    <div>
                        <input type="hidden" name="buttons[__INDEX__][is_active]" value="0" />

                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="buttons[__INDEX__][is_active]" value="1" checked />

                            <span class="text-sm font-medium">
                                Tombol Aktif
                            </span>
                        </label>
                    </div>

                    <button type="button" class="remove-homepage-cta-button rounded-md border px-3 py-2 text-sm">
                        Hapus Tombol
                    </button>
                </div>
            </template>

            {{-- Aksi form --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.homepage-ctas.index') }}" class="rounded-md border px-4 py-2">
                    Batal
                </a>

                <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-white">
                    Simpan Perubahan
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
                container.dataset.buttonIndex = String(nextIndex);

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
