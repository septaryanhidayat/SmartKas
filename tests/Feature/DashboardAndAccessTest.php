<?php

declare(strict_types=1);

use App\Enums\InvoiceStatus;
use App\Enums\UnitCode;
use App\Models\AcademicYear;
use App\Models\FeeComponent;
use App\Models\Invoice;
use App\Models\Student;
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

it('renders arrears report safely even when unpaid invoices belong to soft-deleted students', function () {
    $bendahara = User::where('email', 'dessy@sitrobbani.sch.id')->firstOrFail();
    $student = Student::withoutGlobalScope('unit_scope')->firstOrFail();
    $component = FeeComponent::withoutGlobalScope('unit_scope')->where('unit_id', $student->unit_id)->firstOrFail();
    $year = AcademicYear::getActive();

    Invoice::create([
        'unit_id' => $student->unit_id,
        'student_id' => $student->id,
        'fee_component_id' => $component->id,
        'academic_year_id' => $year->id,
        'invoice_number' => 'INV-ORPHAN-0001',
        'billing_month' => 11,
        'billing_year' => 2026,
        'due_date' => '2026-11-10',
        'gross_amount' => 300000,
        'discount_amount' => 0,
        'net_amount' => 300000,
        'paid_amount' => 0,
        'status' => InvoiceStatus::UNPAID,
        'created_by' => $bendahara->id,
    ]);

    $student->delete();

    $this->actingAs($bendahara)
        ->get(route('reports.arrears', ['search' => 'INV-ORPHAN-0001']))
        ->assertStatus(200)
        ->assertSee($student->name)
        ->assertSee('1 Lembar');

    $this->actingAs($bendahara)
        ->get(route('invoices.index', ['search' => 'INV-ORPHAN-0001']))
        ->assertStatus(200)
        ->assertSee($student->name);
});
