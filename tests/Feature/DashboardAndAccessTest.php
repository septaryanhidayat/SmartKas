<?php

declare(strict_types=1);

use App\Enums\UnitCode;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('displays consolidated 4 units on dashboard for foundation leadership', function () {
    $sughesti = User::where('email', 'sughesti@sitrobbani.sch.id')->firstOrFail();

    $response = $this->actingAs($sughesti)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertSee('KB/TKIT Robbani');
    $response->assertSee('SDIT Robbani');
    $response->assertSee('SMPIT Robbani');
    $response->assertSee('SMAIT Robbani');
    $response->assertSee('Rojanah, S.E.');
    $response->assertSee('Risma, S.Pd');
    $response->assertSee('Adelia Jesika, S.Pd');
});

it('isolates dashboard unit summary for unit admin', function () {
    $tkAdmin = User::where('email', 'tk@sitrobbani.sch.id')->firstOrFail();

    $response = $this->actingAs($tkAdmin)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertSee('KB/TKIT Robbani');
    $response->assertSee('Rojanah, S.E.');
    $response->assertDontSee('SMPIT Robbani');
    $response->assertDontSee('SMAIT Robbani');
    $response->assertDontSee('Audit Trail');
    $response->assertDontSee('Tutup Buku Bulanan');
});

it('blocks unauthorized users from audit logs and closing periods', function () {
    $tkAdmin = User::where('email', 'tk@sitrobbani.sch.id')->firstOrFail();

    $this->actingAs($tkAdmin)->get(route('audit-logs.index'))->assertStatus(403);
    $this->actingAs($tkAdmin)->get(route('closing-periods.index'))->assertStatus(403);
});

it('renders core financial pages cleanly for authorized users', function () {
    $bendahara = User::where('email', 'dessy@sitrobbani.sch.id')->firstOrFail();

    $this->actingAs($bendahara)->get(route('students.index'))->assertStatus(200);
    $this->actingAs($bendahara)->get(route('invoices.index'))->assertStatus(200);
    $this->actingAs($bendahara)->get(route('payments.cashier'))->assertStatus(200);
    $this->actingAs($bendahara)->get(route('payments.index'))->assertStatus(200);
    $this->actingAs($bendahara)->get(route('expenses.index'))->assertStatus(200);
    $this->actingAs($bendahara)->get(route('reports.cash-ledger'))->assertStatus(200);
    $this->actingAs($bendahara)->get(route('reports.income-expense'))->assertStatus(200);
    $this->actingAs($bendahara)->get(route('reports.arrears'))->assertStatus(200);
    $this->actingAs($bendahara)->get(route('closing-periods.index'))->assertStatus(200);
    $this->actingAs($bendahara)->get(route('audit-logs.index'))->assertStatus(200);
});

it('supports paginated arrears search and unit isolation overrides safely', function () {
    $bendahara = User::where('email', 'dessy@sitrobbani.sch.id')->firstOrFail();
    $tkAdmin = User::where('email', 'tk@sitrobbani.sch.id')->firstOrFail();
    $sditUnit = Unit::where('code', UnitCode::SDIT)->firstOrFail();

    $this->actingAs($bendahara)
        ->get(route('reports.arrears', ['search' => 'NON_EXISTENT_STUDENT_XYZ']))
        ->assertStatus(200)
        ->assertSee('0 Lembar');

    $this->actingAs($bendahara)
        ->get(route('reports.cash-ledger', ['unit_id' => $sditUnit->id]))
        ->assertStatus(200)
        ->assertSee('Buku Kas Umum');

    $this->actingAs($tkAdmin)
        ->get(route('dashboard', ['unit_id' => $sditUnit->id]))
        ->assertStatus(200)
        ->assertSee('KB/TKIT Robbani')
        ->assertDontSee('SDIT Robbani');
});
