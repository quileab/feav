<?php

use App\Models\User;
use Filament\Auth\Pages\Login;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Livewire\Livewire;

test('login screen can be rendered', function () {
    $response = $this->get('/admin/login');

    $response->assertOk();
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    Livewire::test(Login::class)
        ->set('data.email', $user->email)
        ->set('data.password', 'password')
        ->call('authenticate')
        ->assertHasNoErrors()
        ->assertRedirect('/admin');

    $this->assertAuthenticatedAs($user);
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    Livewire::test(Login::class)
        ->set('data.email', $user->email)
        ->set('data.password', 'wrong-password')
        ->call('authenticate')
        ->assertHasErrors(['data.email']);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->withoutMiddleware(VerifyCsrfToken::class)
        ->post('/admin/logout');

    $response->assertRedirect('/admin/login');

    $this->assertGuest();
});
