<x-layouts::admin :title="__('Menu Management')">
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                    Menu Management
                </h1>

                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Kelola navigasi dan struktur menu website.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.menus.trash') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 6h18M8 6V4h8v2m-9 0 1 14h8l1-14M10 10v6m4-6v6" />
                    </svg>

                    Trash
                </a>

                <a href="{{ route('admin.menus.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>

                    Tambah Menu
                </a>
            </div>
        </div>

        {{-- Menu table --}}
        <div
            class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

            {{-- Table header --}}
            <div
                class="hidden border-b border-zinc-200 bg-zinc-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-400 md:grid md:grid-cols-[minmax(0,1fr)_120px_120px_80px_150px] md:gap-4">
                <div>Menu</div>
                <div>Tipe</div>
                <div>Status</div>
                <div>Urutan</div>
                <div class="text-right">Aksi</div>
            </div>

            {{-- Menu hierarchy --}}
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @if ($menus->isEmpty())
                    <div class="px-6 py-12 text-center">
                        <div
                            class="mx-auto flex size-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-zinc-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </div>

                        <h2 class="mt-4 text-sm font-semibold text-zinc-900 dark:text-white">
                            Belum ada menu
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Tambahkan menu pertama untuk mulai membangun navigasi website.
                        </p>

                        <a href="{{ route('admin.menus.create') }}"
                            class="mt-4 inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                            Tambah Menu
                        </a>
                    </div>
                @else
                    @include('admin.menus._tree', [
                        'menus' => $menus,
                        'level' => 0,
                    ])
                @endif
            </div>
        </div>

    </div>
</x-layouts::admin>
