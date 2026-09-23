<x-layouts::admin>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold">Tambah User</h1>
            <p class="text-sm text-zinc-500">
                Buat akun pengguna baru.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
            @csrf

            <div>
                <label for="name">Nama</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required>

                @error('name')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required>

                @error('email')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>

                @error('password')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation">Konfirmasi Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required>
            </div>

            <div>
                <button type="submit">Simpan</button>
                <a href="{{ route('admin.users.index') }}">Batal</a>
            </div>
        </form>
    </div>
</x-layouts::admin>
