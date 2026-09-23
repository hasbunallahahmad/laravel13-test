<?php

use App\Models\User;
use App\Services\Security\TurnstileService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->mock(TurnstileService::class, function ($mock): void {
        $mock->shouldReceive('verify')
            ->andReturn(true);
    });
});

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('active users can authenticate with valid credentials and valid turnstile', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
        'cf-turnstile-response' => 'test-valid-token',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('users cannot authenticate with an invalid password', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
        'cf-turnstile-response' => 'test-valid-token',
    ]);

    $response->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('users cannot authenticate with an unknown email address', function () {
    $response = $this->post(route('login.store'), [
        'email' => 'unknown@example.test',
        'password' => 'password',
        'cf-turnstile-response' => 'test-valid-token',
    ]);

    $response->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('inactive users cannot authenticate', function () {
    $user = User::factory()
        ->inactive()
        ->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
        'cf-turnstile-response' => 'test-valid-token',
    ]);

    $response->assertForbidden();

    $this->assertGuest();
});

test('soft deleted users cannot authenticate', function () {
    $user = User::factory()->create();

    $user->delete();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
        'cf-turnstile-response' => 'test-valid-token',
    ]);

    $response->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('authenticated users can logout', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('logout'));

    $response->assertRedirect(route('home'));

    $this->assertGuest();
});
