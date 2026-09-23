<x-layouts::admin>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold">Edit User</h1>
            <p class="text-sm text-zinc-500">
                Perbarui informasi pengguna.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user->uuid) }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <div>
                <label for="name">Nama</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required>

                @error('name')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>

                @error('email')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password">Password Baru</label>
                <input id="password" name="password" type="password">

                @error('password')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation">
                    Konfirmasi Password Baru
                </label>

                <input id="password_confirmation" name="password_confirmation" type="password">
            </div>

            <div>
                <label for="role">Role</label>

                <select id="role" name="role">
                    <option value="">-- Tidak mengubah role --</option>

                    @foreach (['editor', 'author', 'contributor', 'viewer', 'admin', 'super-admin'] as $role)
                        <option value="{{ $role }}" @selected(old('role', $user->roles->first()?->name) === $role)>
                            {{ $role }}
                        </option>
                    @endforeach
                </select>

                @error('role')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button type="submit">Simpan</button>

                <a href="{{ route('admin.users.index') }}">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-layouts::admin>
