<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Permission::findOrCreate('users.view');
});

it('redirects guest from user index', function (): void {
    $response = $this->get(route('admin.users.index'));

    $response->assertRedirect();
});

it('forbids user without users view permission', function (): void {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.users.index'));

    $response->assertForbidden();
});

it('allows user with users view permission to access user index', function (): void {
    $user = User::factory()->create();

    $user->givePermissionTo('users.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.users.index'));

    $response->assertOk();
});

it('displays users on the index page', function (): void {
    $admin = User::factory()->create();

    $admin->givePermissionTo('users.view');

    $targetUser = User::factory()->create([
        'name' => 'User Test',
        'email' => 'user@test.local',
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.users.index'));

    $response
        ->assertOk()
        ->assertSee('User Test')
        ->assertSee('user@test.local');
});

it('eager loads roles for users', function (): void {
    $admin = User::factory()->create();

    $admin->givePermissionTo('users.view');

    User::factory()->count(5)->create();

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.users.index'));

    $response->assertOk();

    $users = $response->viewData('users');

    expect($users->items())->not->toBeEmpty();

    foreach ($users->items() as $user) {
        expect($user->relationLoaded('roles'))->toBeTrue();
    }
});

it('paginates users by twenty records', function (): void {
    $admin = User::factory()->create();

    $admin->givePermissionTo('users.view');

    User::factory()->count(25)->create();

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.users.index'));

    $response->assertOk();

    $users = $response->viewData('users');

    expect($users->perPage())->toBe(20)
        ->and($users->total())->toBe(26);
});

test('user management displays assigned roles', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('users.view');

    $role = Role::findOrCreate('Editor', 'web');

    $managedUser = User::factory()->create([
        'name' => 'Editor User',
    ]);

    $managedUser->assignRole($role);

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Editor User')
        ->assertSee('Editor');
});

// test('user with users create permission can access create form', function () {
//     $user = User::factory()->create();

//     $user->givePermissionTo('users.create');

//     $this->actingAs($user)
//         ->get(route('admin.users.create'))
//         ->assertOk()
//         ->assertViewIs('admin.users.create');
// });

// test('user without users create permission cannot access create form', function () {
//     $user = User::factory()->create();

//     $this->actingAs($user)
//         ->get(route('admin.users.create'))
//         ->assertForbidden();
// });

// test('user can create a new user', function () {
//     $user = User::factory()->create();

//     $user->givePermissionTo('users.create');

//     $response = $this
//         ->actingAs($user)
//         ->post(route('admin.users.store'), [
//             'name' => 'New User',
//             'email' => 'new-user@example.com',
//             'password' => 'SecurePassword123!',
//             'password_confirmation' => 'SecurePassword123!',
//         ]);

//     $response
//         ->assertRedirect(route('admin.users.index'))
//         ->assertSessionHas('success');

//     $createdUser = User::query()
//         ->where('email', 'new-user@example.com')
//         ->first();

//     expect($createdUser)->not->toBeNull()
//         ->and($createdUser->name)->toBe('New User')
//         ->and($createdUser->password)->not->toBe('SecurePassword123!');
// });

// test('user without users create permission cannot create a new user', function () {
//     $user = User::factory()->create();

//     $response = $this
//         ->actingAs($user)
//         ->post(route('admin.users.store'), [
//             'name' => 'Unauthorized User',
//             'email' => 'unauthorized@example.com',
//             'password' => 'SecurePassword123!',
//             'password_confirmation' => 'SecurePassword123!',
//         ]);

//     $response->assertForbidden();

//     expect(
//         User::query()
//             ->where('email', 'unauthorized@example.com')
//             ->exists()
//     )->toBeFalse();
// });

test('user with users create permission can access create form', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('users.create');

    $this->actingAs($user)
        ->get(route('admin.users.create'))
        ->assertOk()
        ->assertViewIs('admin.users.create');
});

test('user without users create permission cannot access create form', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.users.create'))
        ->assertForbidden();
});

test('user can create a new user', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('users.create');

    $response = $this
        ->actingAs($user)
        ->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'new-user@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

    $response
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success');

    $createdUser = User::query()
        ->where('email', 'new-user@example.com')
        ->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser->name)->toBe('New User')
        ->and($createdUser->password)->not->toBe('SecurePassword123!');
});

test('user without users create permission cannot create a new user', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('admin.users.store'), [
            'name' => 'Unauthorized User',
            'email' => 'unauthorized@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

    $response->assertForbidden();

    expect(
        User::query()
            ->where('email', 'unauthorized@example.com')
            ->exists()
    )->toBeFalse();
});

test('user creation requires a name', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('users.create');

    $response = $this
        ->actingAs($user)
        ->post(route('admin.users.store'), [
            'name' => '',
            'email' => 'validation-name@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

    $response
        ->assertSessionHasErrors('name');

    expect(
        User::query()
            ->where('email', 'validation-name@example.com')
            ->exists()
    )->toBeFalse();
});

test('user creation requires a valid email address', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('users.create');

    $response = $this
        ->actingAs($user)
        ->post(route('admin.users.store'), [
            'name' => 'Invalid Email User',
            'email' => 'not-an-email',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

    $response
        ->assertSessionHasErrors('email');

    expect(
        User::query()
            ->where('email', 'not-an-email')
            ->exists()
    )->toBeFalse();
});

test('user creation rejects duplicate email address', function () {
    $user = User::factory()->create();
    $existingUser = User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    $user->givePermissionTo('users.create');

    $response = $this
        ->actingAs($user)
        ->post(route('admin.users.store'), [
            'name' => 'Duplicate Email User',
            'email' => $existingUser->email,
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

    $response
        ->assertSessionHasErrors('email');

    expect(
        User::query()
            ->where('email', $existingUser->email)
            ->count()
    )->toBe(1);
});

test('user creation requires a password with minimum length', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('users.create');

    $response = $this
        ->actingAs($user)
        ->post(route('admin.users.store'), [
            'name' => 'Short Password User',
            'email' => 'short-password@example.com',
            'password' => 'Short123!',
            'password_confirmation' => 'Short123!',
        ]);

    $response
        ->assertSessionHasErrors('password');

    expect(
        User::query()
            ->where('email', 'short-password@example.com')
            ->exists()
    )->toBeFalse();
});

test('user creation requires matching password confirmation', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('users.create');

    $response = $this
        ->actingAs($user)
        ->post(route('admin.users.store'), [
            'name' => 'Mismatch Password User',
            'email' => 'mismatch-password@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'DifferentPassword123!',
        ]);

    $response
        ->assertSessionHasErrors('password');

    expect(
        User::query()
            ->where('email', 'mismatch-password@example.com')
            ->exists()
    )->toBeFalse();
});

test('user creation does not allow uuid to be controlled by request', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('users.create');

    $response = $this
        ->actingAs($user)
        ->post(route('admin.users.store'), [
            'name' => 'UUID Security User',
            'email' => 'uuid-security@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'uuid' => '00000000-0000-0000-0000-000000000000',
        ]);

    $response->assertRedirect(route('admin.users.index'));

    $createdUser = User::query()
        ->where('email', 'uuid-security@example.com')
        ->firstOrFail();

    expect($createdUser->uuid)
        ->not->toBe('00000000-0000-0000-0000-000000000000');
});

test('user creation does not allow deleted_at to be controlled by request', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('users.create');

    $response = $this
        ->actingAs($user)
        ->post(route('admin.users.store'), [
            'name' => 'Deleted At Security User',
            'email' => 'deleted-at-security@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'deleted_at' => now()->toDateTimeString(),
        ]);

    $response->assertRedirect(route('admin.users.index'));

    $createdUser = User::withTrashed()
        ->where('email', 'deleted-at-security@example.com')
        ->firstOrFail();

    expect($createdUser->deleted_at)->toBeNull();
});

test('user with users update permission can access edit form', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('users.update');

    $managedUser = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.users.edit', $managedUser->uuid))
        ->assertOk()
        ->assertViewIs('admin.users.edit');
});

test('user without users update permission cannot access edit form', function () {
    $user = User::factory()->create();

    $managedUser = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.users.edit', $managedUser->uuid))
        ->assertForbidden();
});

test('user with users update permission can update another user', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('users.update');

    $managedUser = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old-email@example.com',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(route('admin.users.update', $managedUser->uuid), [
            'name' => 'Updated Name',
            'email' => 'updated-email@example.com',
        ]);

    $response
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success');

    $managedUser->refresh();

    expect($managedUser->name)->toBe('Updated Name')
        ->and($managedUser->email)->toBe('updated-email@example.com');
});

test('user without users update permission cannot update another user', function () {
    $user = User::factory()->create();

    $managedUser = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(route('admin.users.update', $managedUser->uuid), [
            'name' => 'Unauthorized Name',
            'email' => 'unauthorized@example.com',
        ]);

    $response->assertForbidden();

    $managedUser->refresh();

    expect($managedUser->name)->toBe('Original Name')
        ->and($managedUser->email)->toBe('original@example.com');
});

test('user with users update permission can assign a role to another user', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('users.update');

    $managedUser = User::factory()->create();

    expect($managedUser->roles)->toBeEmpty();

    $response = $this
        ->actingAs($user)
        ->patch(route('admin.users.update', $managedUser->uuid), [
            'name' => $managedUser->name,
            'email' => $managedUser->email,
            'role' => 'editor',
        ]);

    $response
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success');

    expect($managedUser->fresh()->hasRole('editor'))->toBeTrue();
});

test('updating a user replaces the existing role', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('users.update');

    $managedUser = User::factory()->create();

    $managedUser->assignRole('author');

    expect($managedUser->fresh()->hasRole('author'))->toBeTrue();

    $response = $this
        ->actingAs($user)
        ->patch(route('admin.users.update', $managedUser->uuid), [
            'name' => $managedUser->name,
            'email' => $managedUser->email,
            'role' => 'editor',
        ]);

    $response
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success');

    $managedUser->refresh();

    expect($managedUser->hasRole('editor'))->toBeTrue()
        ->and($managedUser->hasRole('author'))->toBeFalse();
});

test('admin cannot assign admin role to another user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $managedUser = User::factory()->create();

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.users.update', $managedUser->uuid), [
            'name' => $managedUser->name,
            'email' => $managedUser->email,
            'role' => 'admin',
        ]);

    $response->assertForbidden();

    expect($managedUser->fresh()->hasRole('admin'))->toBeFalse();
});

test('admin cannot assign super admin role to another user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $managedUser = User::factory()->create();

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.users.update', $managedUser->uuid), [
            'name' => $managedUser->name,
            'email' => $managedUser->email,
            'role' => 'super-admin',
        ]);

    $response->assertForbidden();

    expect($managedUser->fresh()->hasRole('super-admin'))->toBeFalse();
});

test('super admin can assign admin role to another user', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    $managedUser = User::factory()->create();

    $response = $this
        ->actingAs($superAdmin)
        ->patch(route('admin.users.update', $managedUser->uuid), [
            'name' => $managedUser->name,
            'email' => $managedUser->email,
            'role' => 'admin',
        ]);

    $response
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success');

    expect($managedUser->fresh()->hasRole('admin'))->toBeTrue();
});

test('cannot assign a non existing role', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('users.update');

    $managedUser = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('admin.users.update', $managedUser->uuid), [
            'name' => $managedUser->name,
            'email' => $managedUser->email,
            'role' => 'role-yang-tidak-ada',
        ]);

    $response
        ->assertSessionHasErrors('role');

    expect($managedUser->fresh()->roles)->toBeEmpty();
});

test('user with users delete permission can delete another user', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('users.delete');

    $managedUser = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.users.destroy', $managedUser->uuid));

    $response
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success');

    expect($managedUser->fresh()->trashed())->toBeTrue();
});

test('user without users delete permission cannot delete another user', function () {
    $user = User::factory()->create();

    $managedUser = User::factory()->create([
        'name' => 'Protected User',
        'email' => 'protected@example.com',
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.users.destroy', $managedUser->uuid));

    $response->assertForbidden();

    expect($managedUser->fresh()->trashed())->toBeFalse();
});

test('user delete route uses uuid instead of numeric id', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('users.delete');

    $managedUser = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.users.destroy', $managedUser->uuid));

    $response->assertRedirect(route('admin.users.index'));

    expect($managedUser->fresh()->trashed())->toBeTrue();
});

test('soft deleted user cannot be deleted again', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('users.delete');

    $managedUser = User::factory()->create();
    $managedUser->delete();

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.users.destroy', $managedUser->uuid));

    $response->assertNotFound();

    expect(
        User::withTrashed()
            ->find($managedUser->id)
            ->trashed()
    )->toBeTrue();
});

test('user cannot delete their own account through user management', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('users.delete');

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.users.destroy', $user->uuid));

    $response->assertForbidden();

    expect($user->fresh()->trashed())->toBeFalse();
});

test('user with users view permission can access user trash', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('users.view');

    $managedUser = User::factory()->create();
    $managedUser->delete();

    $this->actingAs($user)
        ->get(route('admin.users.trash'))
        ->assertOk()
        ->assertViewIs('admin.users.trash');
});
test('user without users view permission cannot access user trash', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.users.trash'))
        ->assertForbidden();
});

test('user trash only displays soft deleted users', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('users.view');

    $activeUser = User::factory()->create([
        'name' => 'Active User',
    ]);

    $deletedUser = User::factory()->create([
        'name' => 'Deleted User',
    ]);

    $deletedUser->delete();

    $this->actingAs($user)
        ->get(route('admin.users.trash'))
        ->assertOk()
        ->assertSee('Deleted User')
        ->assertDontSee('Active User');
});
test('user without users update permission cannot restore a deleted user', function () {
    $user = User::factory()->create();

    $managedUser = User::factory()->create();
    $managedUser->delete();

    $response = $this
        ->actingAs($user)
        ->patch(route('admin.users.restore', $managedUser->uuid));

    $response->assertForbidden();

    expect($managedUser->fresh()->trashed())->toBeTrue();
});
test('active user cannot be restored', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('users.update');

    $managedUser = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('admin.users.restore', $managedUser->uuid));

    $response->assertNotFound();

    expect($managedUser->fresh()->trashed())->toBeFalse();
});
test('restoring a user preserves their existing role', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('users.update');

    $managedUser = User::factory()->create();
    $managedUser->assignRole('editor');
    $managedUser->delete();

    $this
        ->actingAs($user)
        ->patch(route('admin.users.restore', $managedUser->uuid))
        ->assertRedirect(route('admin.users.index'));

    $restoredUser = $managedUser->fresh();

    expect($restoredUser->trashed())->toBeFalse()
        ->and($restoredUser->hasRole('editor'))->toBeTrue();
});

test('user with users force delete permission can permanently delete a deleted user', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('users.force-delete');

    $managedUser = User::factory()->create();
    $managedUser->delete();

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.users.force-destroy', $managedUser->uuid));

    $response
        ->assertRedirect(route('admin.users.trash'))
        ->assertSessionHas('success');

    expect(
        User::withTrashed()->where('id', $managedUser->id)->exists()
    )->toBeFalse();
});

test('user without users force delete permission cannot permanently delete a deleted user', function () {
    $user = User::factory()->create();

    $managedUser = User::factory()->create();
    $managedUser->delete();

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.users.force-destroy', $managedUser->uuid));

    $response->assertForbidden();

    expect(
        User::withTrashed()->where('id', $managedUser->id)->exists()
    )->toBeTrue();
});

test('active user cannot be permanently deleted', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('users.force-delete');

    $managedUser = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.users.force-destroy', $managedUser->uuid));

    $response->assertNotFound();

    expect(
        User::withTrashed()->where('id', $managedUser->id)->exists()
    )->toBeTrue();
});

test('user cannot permanently delete their own account', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('users.force-delete');

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.users.force-destroy', $user->uuid));

    $response->assertForbidden();

    expect(
        User::withTrashed()->where('id', $user->id)->exists()
    )->toBeTrue();
});

test('force delete route uses uuid instead of numeric id', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('users.force-delete');

    $managedUser = User::factory()->create();
    $managedUser->delete();

    $response = $this
        ->actingAs($user)
        ->delete(
            route('admin.users.force-destroy', $managedUser->uuid)
        );

    $response->assertRedirect(route('admin.users.trash'));

    expect(
        User::withTrashed()->where('id', $managedUser->id)->exists()
    )->toBeFalse();
});

test('user with force delete permission can see force delete action in trash', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'users.view',
        'users.force-delete',
    ]);

    $deletedUser = User::factory()->create([
        'name' => 'Deleted User',
    ]);

    $deletedUser->delete();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.users.trash'));

    $response
        ->assertOk()
        ->assertSee('Hapus Permanen')
        ->assertSee(
            route('admin.users.force-destroy', $deletedUser->uuid),
            false,
        );
});

test('user without force delete permission cannot see force delete action in trash', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('users.view');

    $deletedUser = User::factory()->create([
        'name' => 'Deleted User',
    ]);

    $deletedUser->delete();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.users.trash'));

    $response
        ->assertOk()
        ->assertDontSee('Hapus Permanen')
        ->assertDontSee(
            route('admin.users.force-destroy', $deletedUser->uuid),
            false,
        );
});
