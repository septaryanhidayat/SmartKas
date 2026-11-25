<?php

declare(strict_types=1);

use App\Models\Student;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('scopes student queries automatically by unit for unit admin', function () {
    $adminSdit = User::where('email', 'sd@sitrobbani.sch.id')->first();
    $this->actingAs($adminSdit);

    $sditStudents = Student::all();
    expect($sditStudents->isNotEmpty())->toBeTrue();
    foreach ($sditStudents as $student) {
        expect($student->unit_id)->toBe($adminSdit->unit_id);
    }
});

it('prevents unit admin from seeing other units students via HTTP', function () {
    $adminSdit = User::where('email', 'sd@sitrobbani.sch.id')->first();
    $smpitStudent = Student::withoutGlobalScope('unit_scope')
        ->where('unit_id', '!=', $adminSdit->unit_id)
        ->first();

    $this->actingAs($adminSdit);
    $response = $this->get("/students/{$smpitStudent->id}");
    $response->assertStatus(404);
});

it('allows superadmin to view students across all units', function () {
    $superadmin = User::where('email', 'sughesti@sitrobbani.sch.id')->first();
    $this->actingAs($superadmin);

    $allStudents = Student::all();
    $unitIds = $allStudents->pluck('unit_id')->unique();
    expect($unitIds->count())->toBeGreaterThan(1);
});
