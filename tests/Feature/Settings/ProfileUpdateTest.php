<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get(route('profile.edit'))
        ->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    expect(
        $user->refresh()->email_verified_at
    )->not->toBeNull();
});

test('user can soft delete their account with correct password', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser')
        ->assertHasNoErrors();

    /*
    |--------------------------------------------------------------------------
    | SECURITY CHECK
    |--------------------------------------------------------------------------
    |
    | User tidak boleh ditemukan melalui query normal.
    |
    */

    expect(
        User::find($user->id)
    )->toBeNull();

    /*
    |--------------------------------------------------------------------------
    | SOFT DELETE CHECK
    |--------------------------------------------------------------------------
    |
    | Record harus tetap berada di database.
    |
    */

    $deletedUser = User::withTrashed()
        ->find($user->id);

    expect($deletedUser)->not->toBeNull();

    expect($deletedUser->trashed())
        ->toBeTrue();

    /*
    |--------------------------------------------------------------------------
    | SESSION SECURITY CHECK
    |--------------------------------------------------------------------------
    */

    expect(Auth::check())
        ->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'wrong-password')
        ->call('deleteUser')
        ->assertHasErrors(['password']);

    /*
    |--------------------------------------------------------------------------
    | WORST CASE PROTECTION
    |--------------------------------------------------------------------------
    |
    | Password salah tidak boleh menghapus user.
    |
    */

    expect(
        User::find($user->id)
    )->not->toBeNull();

    expect(
        User::withTrashed()->find($user->id)->trashed()
    )->toBeFalse();

    expect(Auth::check())
        ->toBeTrue();
});
