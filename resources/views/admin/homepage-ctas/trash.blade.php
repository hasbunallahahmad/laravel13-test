<x-layouts::admin>
    <x-slot name="title">
        Sampah Homepage CTA
    </x-slot>

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">
                    Sampah Homepage CTA
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Daftar Homepage CTA yang telah dihapus.
                </p>
            </div>

            @can('homepage-ctas.view')
                <a href="{{ route('admin.homepage-ctas.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Kembali ke Homepage CTA
                </a>
            @endcan
        </div>

        {{-- Daftar CTA Terhapus --}}
        @forelse ($homepageCtas as $homepageCta)
            <div class="rounded-lg border border-gray-200 p-4">
                <h2 class="font-semibold">
                    {{ $homepageCta->title }}
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    {{ $homepageCta->description ?: 'Tidak ada deskripsi.' }}
                </p>

                {{-- Tombol CTA --}}
                @if ($homepageCta->buttons->isNotEmpty())
                    <ul class="mt-3 space-y-2">
                        @foreach ($homepageCta->buttons as $button)
                            <li class="text-sm text-gray-600">
                                {{ $button->label }}
                            </li>
                        @endforeach
                    </ul>
                @endif

                {{-- Aksi --}}
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    @can('homepage-ctas.restore')
                        <form method="POST" action="{{ route('admin.homepage-ctas.restore', $homepageCta->uuid) }}">
                            @csrf
                            @method('PATCH')

                            <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                                Pulihkan
                            </button>
                        </form>
                    @endcan

                    @can('homepage-ctas.force-delete')
                        <form method="POST" action="{{ route('admin.homepage-ctas.force-delete', $homepageCta->uuid) }}"
                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus Homepage CTA ini secara permanen? Tindakan ini tidak dapat dibatalkan.');">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                                Hapus Permanen
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-gray-200 p-6">
                <p class="text-sm text-gray-500">
                    Belum ada Homepage CTA di tempat sampah.
                </p>
            </div>
        @endforelse

        {{-- Pagination --}}
        {{ $homepageCtas->links() }}
    </div>
</x-layouts::admin>
