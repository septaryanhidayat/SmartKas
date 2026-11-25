<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('renders standard clean login page without exposing demo accounts', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertSee('SmartKas');
    $response->assertSee('Yayasan Generasi Robbani Sumatera Selatan');
    $response->assertDontSee('password123');
    $response->assertDontSee('quick-login');
    $response->assertDontSee('dessy@sitrobbani.sch.id');
    $response->assertDontSee('sughesti@sitrobbani.sch.id');
});

it('does not expose quick-login passwordless route', function () {
    $user = User::where('email', 'sughesti@sitrobbani.sch.id')->firstOrFail();

    $response = $this->post("/quick-login/{$user->id}");

    $response->assertStatus(404);
});

it('authenticates official personnel with valid credentials', function (string $email, string $expectedName) {
    $response = $this->post('/login', [
        'email' => $email,
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
    expect(auth()->user()->name)->toBe($expectedName);
})->with([
    'Ketua Yayasan' => ['sughesti@sitrobbani.sch.id', 'Sughesti Wulandari, S.Pd'],
    'Bendahara Yayasan' => ['dessy@sitrobbani.sch.id', 'Dessy Aprianty, S.E.'],
    'Admin Pusat Pembayaran' => ['anis@sitrobbani.sch.id', 'Anis Monica, S.H.'],
    'Admin TKIT' => ['tk@sitrobbani.sch.id', 'Rojanah, S.E.'],
    'Admin SDIT' => ['sd@sitrobbani.sch.id', 'Risma, S.Pd'],
    'Admin SMPIT' => ['smp@sitrobbani.sch.id', 'Adelia Jesika, S.Pd'],
    'Kepsek TKIT' => ['ani@sitrobbani.sch.id', 'Ani'],
    'Kepsek SDIT' => ['nur@sitrobbani.sch.id', 'Nur'],
    'Kepsek SMPIT' => ['tia@sitrobbani.sch.id', 'Tia'],
]);

it('rejects invalid password on login', function () {
    $response = $this->from('/login')->post('/login', [
        'email' => 'sughesti@sitrobbani.sch.id',
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('logs out the authenticated user properly', function () {
    $user = User::where('email', 'sughesti@sitrobbani.sch.id')->firstOrFail();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/login');
    $this->assertGuest();
});
