<x-layouts::admin :title="__('Menu Trash')">

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
                        Trash
                    </span>

                </div>

                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                    Menu Trash
                </h1>

                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Kelola menu yang telah dihapus dan pulihkan kembali jika diperlukan.
                </p>
            </div>

            <a href="{{ route('admin.menus.index') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-zinc-500/30 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>

                Kembali
            </a>

        </div>

        <div
            class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 px-6 py-5 dark:border-zinc-700">

                <div class="flex items-start gap-3">

                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">

                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m20.25 7.5-.88 11.38a2.25 2.25 0 0 1-2.24 2.12H6.87a2.25 2.25 0 0 1-2.24-2.12L3.75 7.5m3.75 0V5.25A2.25 2.25 0 0 1 9.75 3h4.5a2.25 2.25 0 0 1 2.25 2.25V7.5m-9.75 0h12.5" />
                        </svg>

                    </div>

                    <div>
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-white">
                            Menu Terhapus
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            Daftar menu yang telah dipindahkan ke tempat sampah.
                        </p>
                    </div>

                </div>

            </div>

            @if ($menus->isEmpty())

                <div class="flex flex-col items-center justify-center px-6 py-16 text-center">

                    <div
                        class="flex size-14 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">

                        <svg xmlns="http://www.w3.org/2000/svg" class="size-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 7.5h18M9.75 3h4.5l1.5 4.5h-9L8.25 3Zm-3 4.5h10.5l-.75 12h-8.5l-.75-12Z" />
                        </svg>

                    </div>

                    <h3 class="mt-4 text-sm font-semibold text-zinc-900 dark:text-white">
                        Tidak ada menu terhapus
                    </h3>

                    <p class="mt-1 max-w-sm text-sm text-zinc-500 dark:text-zinc-400">
                        Semua menu masih aktif dalam daftar menu atau belum ada menu yang dipindahkan ke tempat sampah.
                    </p>

                    <a href="{{ route('admin.menus.index') }}"
                        class="mt-5 inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-zinc-500/30 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                        Kembali ke Menu
                    </a>

                </div>
            @else
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">

                    @foreach ($menus as $menu)
                        <div
                            class="flex flex-col gap-4 px-6 py-5 transition hover:bg-zinc-50 sm:flex-row sm:items-center sm:justify-between dark:hover:bg-zinc-800/50">

                            <div class="min-w-0">

                                <div class="flex items-center gap-3">

                                    <div
                                        class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">

                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                        </svg>

                                    </div>

                                    <div class="min-w-0">

                                        <p class="truncate text-sm font-semibold text-zinc-900 dark:text-white">
                                            {{ $menu->label }}
                                        </p>

                                        <p class="mt-0.5 truncate text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $menu->name }}
                                        </p>

                                    </div>

                                </div>

                            </div>


                            <form action="{{ route('admin.menus.restore', $menu) }}" method="POST" class="shrink-0">

                                @csrf

                                @method('PATCH')

                                <button type="submit"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-zinc-500/30 sm:w-auto dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">

                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 15 3 9m0 0 6-6M3 9h11.25a6.75 6.75 0 0 1 6.75 6.75v.75" />
                                    </svg>

                                    Restore

                                </button>

                            </form>

                        </div>
                    @endforeach

                </div>

            @endif

        </div>

    </div>

</x-layouts::admin>
