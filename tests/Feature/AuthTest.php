<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('renders the modern login page with official personnel names', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertSee('YAYASAN GENERASI ROBBANI');
    $response->assertSee('Sughesti Wulandari, S.Pd');
    $response->assertSee('Dessy Aprianty, S.E.');
    $response->assertSee('Anis Monica, S.H.');
    $response->assertSee('Rojanah, S.E.');
    $response->assertSee('Risma, S.Pd');
    $response->assertSee('Adelia Jesika, S.Pd');
    $response->assertSee('Admin Keuangan SMAIT Robbani');
});

it('authenticates user with valid credentials', function () {
    $response = $this->post('/login', [
        'email' => 'superadmin@sit.sch.id',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
});

it('allows one-click quick login for official personnel', function () {
    $treasurer = User::where('email', 'bendahara@sit.sch.id')->firstOrFail();

    $response = $this->post("/quick-login/{$treasurer->id}");

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($treasurer);
    $response->assertSessionHas('success', "Selamat datang, {$treasurer->name}!");
});

it('logs out the authenticated user properly', function () {
    $user = User::where('email', 'superadmin@sit.sch.id')->firstOrFail();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/login');
    $this->assertGuest();
});
