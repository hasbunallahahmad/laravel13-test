@props(['value' => ''])

<flux:field>
    <flux:label for="body">{{ __('Body') }}</flux:label>

    <div data-content-editor data-media-picker-url="{{ route('admin.media.picker') }}"
        class="relative overflow-visible rounded-lg border border-app-border bg-app-surface">
        <div class="flex flex-wrap items-center gap-1 border-b border-app-border bg-app-surface-muted p-2" role="toolbar"
            aria-label="{{ __('Rich text editor toolbar') }}">
            {{-- Text formatting --}}
            <button type="button" data-editor-command="bold" aria-label="Bold" aria-pressed="false" title="Bold"
                class="px-2 py-1 text-sm font-semibold">
                B
            </button>

            <button type="button" data-editor-command="italic" aria-label="Italic" aria-pressed="false" title="Italic"
                class="px-2 py-1 text-sm italic">
                I
            </button>

            <button type="button" data-editor-command="bullet-list" aria-label="Bullet list" aria-pressed="false"
                title="Bullet list" class="px-2 py-1 text-sm">
                •
            </button>

            <button type="button" data-editor-command="ordered-list" aria-label="Ordered list" aria-pressed="false"
                title="Ordered list" class="px-2 py-1 text-sm">
                1.
            </button>

            <div class="mx-1 h-6 w-px bg-app-border" aria-hidden="true"></div>

            {{-- Media --}}
            <button type="button" data-editor-command="image" aria-label="Insert image" title="Insert image"
                class="px-2 py-1 text-sm">
                🖼
            </button>

            <div class="mx-1 h-6 w-px bg-app-border" aria-hidden="true"></div>

            {{-- Block formatting --}}
            <button type="button" data-editor-command="paragraph" aria-label="Paragraph" aria-pressed="false"
                title="Paragraph" class="px-2 py-1 text-sm">
                P
            </button>

            <button type="button" data-editor-command="heading-2" aria-label="Heading 2" aria-pressed="false"
                title="Heading 2" class="px-2 py-1 text-sm font-semibold">
                H2
            </button>

            <button type="button" data-editor-command="heading-3" aria-label="Heading 3" aria-pressed="false"
                title="Heading 3" class="px-2 py-1 text-sm font-semibold">
                H3
            </button>

            <div class="mx-1 h-6 w-px bg-app-border" aria-hidden="true"></div>

            {{-- Additional formatting --}}
            <button type="button" data-editor-command="underline" aria-label="Underline" aria-pressed="false"
                title="Underline" class="px-2 py-1 text-sm underline">
                U
            </button>

            <button type="button" data-editor-command="strike" aria-label="Strikethrough" aria-pressed="false"
                title="Strikethrough" class="px-2 py-1 text-sm line-through">
                S
            </button>

            <button type="button" data-editor-command="clear" aria-label="Clear formatting" title="Clear formatting"
                class="px-2 py-1 text-sm">
                Tx
            </button>

            <button type="button" data-editor-command="blockquote" aria-label="Blockquote" aria-pressed="false"
                title="Blockquote" class="px-2 py-1 text-sm">
                “
            </button>

            <div class="mx-1 h-6 w-px bg-app-border" aria-hidden="true"></div>

            {{-- Links --}}
            <button type="button" data-editor-command="link" aria-label="Insert or edit link" aria-pressed="false"
                title="Insert / edit link" class="px-2 py-1 text-sm">
                🔗
            </button>

            <button type="button" data-editor-command="unlink" aria-label="Remove link" title="Remove link"
                class="px-2 py-1 text-sm">
                ✕
            </button>

            <div class="mx-1 h-6 w-px bg-app-border" aria-hidden="true"></div>

            {{-- History --}}
            <button type="button" data-editor-command="undo" aria-label="Undo" title="Undo"
                class="px-2 py-1 text-sm">
                ↶
            </button>

            <button type="button" data-editor-command="redo" aria-label="Redo" title="Redo"
                class="px-2 py-1 text-sm">
                ↷
            </button>
        </div>

        {{-- Link popover --}}
        <div data-link-popover
            class="absolute left-2 top-14 z-50 hidden w-80 rounded-lg border border-app-border bg-app-surface p-4 shadow-lg"
            role="dialog" aria-label="Link settings">
            <div class="space-y-3">
                <div>
                    <label for="editor-link-url" class="mb-1 block text-sm font-medium text-app-text">
                        URL tautan
                    </label>

                    <input type="url" id="editor-link-url" data-link-url
                        class="w-full rounded-md border border-app-border bg-app-surface px-3 py-2 text-sm text-app-text outline-none focus:ring-2 focus:ring-app-accent"
                        placeholder="https://example.com" autocomplete="off">
                </div>

                <label class="flex items-center gap-2 text-sm text-app-text">
                    <input type="checkbox" data-link-target class="rounded border-app-border">

                    <span>Buka di tab baru</span>
                </label>

                <div class="flex justify-end gap-2">
                    <button type="button" data-link-cancel
                        class="rounded-md px-3 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700">
                        Batal
                    </button>

                    <button type="button" data-link-save
                        class="rounded-md px-3 py-2 text-sm font-medium hover:bg-zinc-100 dark:hover:bg-zinc-700">
                        Simpan
                    </button>
                </div>
            </div>
        </div>

        {{-- Media picker --}}
        <div data-media-picker-modal class="absolute inset-0 z-50 hidden bg-black/40 p-4" role="dialog"
            aria-modal="true" aria-label="Pilih gambar">
            <div
                class="mx-auto flex max-h-full w-full max-w-5xl flex-col overflow-hidden rounded-xl border border-app-border bg-app-surface shadow-xl">
                <div class="flex items-center justify-between border-b border-app-border px-5 py-4">
                    <div>
                        <h2 class="text-base font-semibold text-app-text">
                            Pilih Gambar
                        </h2>

                        <p class="mt-1 text-sm text-app-text-muted">
                            Pilih gambar dari Media Management.
                        </p>
                    </div>

                    <button type="button" data-media-picker-close aria-label="Tutup"
                        class="rounded-md px-3 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700">
                        ✕
                    </button>
                </div>

                <div data-media-picker-content class="min-h-0 flex-1 overflow-y-auto p-5">
                    <div class="flex items-center justify-center py-12">
                        <p class="text-sm text-app-text-muted">
                            Memuat gambar...
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Editor --}}
        <div data-tiptap-editor class="min-h-64 p-4 text-sm text-app-text outline-none"></div>

        {{-- Form value --}}
        <textarea name="body" id="body" class="hidden" required>{{ $value }}</textarea>
    </div>

    <flux:error name="body" />
</flux:field>
