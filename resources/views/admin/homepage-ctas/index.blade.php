<x-layouts::admin>
    <x-slot name="title">
        Homepage CTA
    </x-slot>

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">
                    Homepage CTA
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    Kelola Call to Action pada halaman utama website.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @can('homepage-ctas.view')
                    <a href="{{ route('admin.homepage-ctas.trash') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Sampah
                    </a>
                @endcan

                @can('homepage-ctas.create')
                    <a href="{{ route('admin.homepage-ctas.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Tambah Homepage CTA
                    </a>
                @endcan
            </div>
        </div>

        {{-- Daftar Homepage CTA --}}
        <div class="rounded-xl border border-gray-200 p-6">
            @forelse ($homepageCtas as $homepageCta)
                <div class="border-b border-gray-200 py-4 last:border-b-0">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="font-medium">
                                {{ $homepageCta->title }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ $homepageCta->description ?: 'Tidak ada deskripsi.' }}
                            </p>

                            <p class="mt-2 text-xs text-gray-500">
                                {{ $homepageCta->buttons->count() }} tombol
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            @can('homepage-ctas.update')
                                <a href="{{ route('admin.homepage-ctas.edit', $homepageCta->uuid) }}"
                                    class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Edit
                                </a>
                            @endcan
                            @can('homepage-ctas.delete')
                                <form method="POST" action="{{ route('admin.homepage-ctas.destroy', $homepageCta->uuid) }}"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus Homepage CTA ini?');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">
                                        Hapus
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">
                    Belum ada Homepage CTA.
                </p>
            @endforelse
        </div>

        {{-- Pagination --}}
        {{ $homepageCtas->links() }}
    </div>
</x-layouts::admin>
