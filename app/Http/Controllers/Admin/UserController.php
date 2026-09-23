<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

final class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with('roles')
            ->latest()
            ->paginate(20);

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        $user->load('roles');

        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
    ): RedirectResponse {
        $data = $request->validated();

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        if (array_key_exists('role', $data) && $data['role'] !== null) {
            $user->syncRoles([$data['role']]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if(Auth::id() === $user->id, Response::HTTP_FORBIDDEN);

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    public function trash(): View
    {
        $users = User::onlyTrashed()
            ->with('roles')
            ->latest('deleted_at')
            ->paginate(20);

        return view('admin.users.trash', [
            'users' => $users,
        ]);
    }

    public function restore(string $user): RedirectResponse
    {
        $deletedUser = User::onlyTrashed()
            ->where('uuid', $user)
            ->firstOrFail();

        $deletedUser->restore();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dipulihkan.');
    }

    public function forceDestroy(string $user): RedirectResponse
    {
        $targetUser = User::withTrashed()
            ->where('uuid', $user)
            ->firstOrFail();

        abort_if(
            Auth::id() === $targetUser->id,
            Response::HTTP_FORBIDDEN,
        );

        abort_unless(
            $targetUser->trashed(),
            Response::HTTP_NOT_FOUND,
        );

        $targetUser->forceDelete();

        return redirect()
            ->route('admin.users.trash')
            ->with('success', 'User berhasil dihapus permanen.');
    }
}
