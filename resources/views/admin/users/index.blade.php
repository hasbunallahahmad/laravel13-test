<x-layouts::admin>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold">
                Pengguna
            </h1>

            <p class="mt-1 text-sm text-zinc-500">
                Kelola pengguna yang memiliki akses ke sistem.
            </p>
        </div>

        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-zinc-50 text-left">
                            <th class="px-6 py-3 font-medium">
                                Nama
                            </th>

                            <th class="px-6 py-3 font-medium">
                                Email
                            </th>

                            <th class="px-6 py-3 font-medium">
                                Role
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-6 py-4">
                                    {{ $user->name }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $user->email }}
                                </td>

                                <td class="px-6 py-4">
                                    @forelse ($user->roles as $role)
                                        <span>
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-zinc-500">
                                            Tidak ada role
                                        </span>
                                    @endforelse
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-zinc-500">
                                    Belum ada pengguna.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t px-6 py-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-layouts::admin>
