<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('redirects unauthenticated guests to login page', function () {
    $response = $this->get('/');
    $response->assertRedirect('/login');
});

it('allows authenticated users to view dashboard', function () {
    $user = User::where('email', 'superadmin@sit.sch.id')->first();
    $response = $this->actingAs($user)->get('/dashboard');
    $response->assertStatus(200);
    $response->assertSee('Dashboard Ringkasan Keuangan');
});
