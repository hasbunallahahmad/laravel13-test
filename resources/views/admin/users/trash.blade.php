<x-layouts::admin>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold">User Terhapus</h1>
            <p class="text-sm text-zinc-500">
                Daftar user yang telah dipindahkan ke tempat sampah.
            </p>
        </div>

        @if ($users->isEmpty())
            <p>Tidak ada user terhapus.</p>
        @else
            <div class="space-y-4">
                @foreach ($users as $user)
                    <div>
                        <div>{{ $user->name }}</div>
                        <div>{{ $user->email }}</div>

                        @if ($user->roles->isNotEmpty())
                            <div>
                                {{ $user->roles->pluck('name')->join(', ') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.users.restore', $user->uuid) }}">
                            @csrf
                            @method('PATCH')

                            <button type="submit">
                                Pulihkan
                            </button>

                            @can('users.force-delete')
                                <form method="POST" action="{{ route('admin.users.force-destroy', $user->uuid) }}"
                                    onsubmit="return confirm('Yakin ingin menghapus user ini secara permanen? Data tidak dapat dipulihkan setelah dihapus permanen.')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit">
                                        Hapus Permanen
                                    </button>
                                </form>
                            @endcan
                        </form>
                    </div>
                @endforeach
            </div>

            {{ $users->links() }}
        @endif

        <a href="{{ route('admin.users.index') }}">
            Kembali ke User
        </a>
    </div>
</x-layouts::admin>
