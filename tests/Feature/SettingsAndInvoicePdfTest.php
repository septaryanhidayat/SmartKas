<?php

declare(strict_types=1);

use App\Actions\Billing\GenerateMonthlyInvoicesAction;
use App\Actions\Payment\RecordStudentPaymentAction;
use App\Enums\PaymentMethod;
use App\Enums\UnitCode;
use App\Models\AcademicYear;
use App\Models\CashAccount;
use App\Models\Invoice;
use App\Models\Unit;
use App\Models\User;
use App\Services\AppSettingService;
use App\Services\InvoicePdfService;
use App\Services\ReceiptPdfService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    app(AppSettingService::class)->clearCache();
});

it('uses Jl. Sarjana Blok A No. 25 Timbangan Indralaya Utara Ogan Ilir and Yayasan logo as defaults', function () {
    expect(app_setting('foundation_address'))
        ->toBe('Jl. Sarjana Blok A No. 25 Timbangan, Indralaya Utara, Ogan Ilir');
    expect(app_setting('foundation_logo_path'))
        ->toBe('images/yayasan-logo.png');

    $yayasan = Unit::where('code', UnitCode::YAYASAN)->firstOrFail();
    expect($yayasan->address)
        ->toBe('Jl. Sarjana Blok A No. 25 Timbangan, Indralaya Utara, Ogan Ilir');

    $sughesti = User::where('email', 'sughesti@sitrobbani.sch.id')->firstOrFail();
    $this->actingAs($sughesti)
        ->get(route('dashboard'))
        ->assertStatus(200);

    Auth::logout();

    $this->get(route('login'))
        ->assertStatus(200)
        ->assertSee('Jl. Sarjana Blok A No. 25 Timbangan, Indralaya Utara, Ogan Ilir');
});

it('compacts long transaction codes cleanly and renders embedded Yayasan logo in invoice and receipt PDFs', function () {
    expect(format_tx_code('OCT2026385RUOX5BAX4U8RUYX', 'compact'))->toBe('OCT2026…8RUYX');
    expect(format_tx_code('OCT2026385RUOX5BAX4U8RUYX', 'full'))->toBe('OCT2026385RUOX5BAX4U8RUYX');
    expect(format_ledger_description('Pembayaran 261120HMR893CF7ITE1812 - November 2026'))
        ->toBe('Pembayaran 261120H…E1812 - November 2026');

    $academicYear = AcademicYear::getActive();
    $tkitUnit = Unit::where('code', UnitCode::TKIT)->firstOrFail();
    $sditUnit = Unit::where('code', UnitCode::SDIT)->firstOrFail();
    $sughesti = User::where('email', 'sughesti@sitrobbani.sch.id')->firstOrFail();

    $billingAction = app(GenerateMonthlyInvoicesAction::class);
    $billingAction->execute($tkitUnit->id, $academicYear->id, 11, 2026);
    $billingAction->execute($sditUnit->id, $academicYear->id, 11, 2026);

    $invoice = Invoice::withoutGlobalScope('unit_scope')
        ->where('unit_id', $sditUnit->id)
        ->where('net_amount', '>', 0)
        ->firstOrFail();

    $invoiceHtml = view('pdf.invoice', [
        'invoice' => $invoice->load(['unit', 'student.classroom', 'feeComponent', 'academicYear', 'creator']),
        'unit' => $invoice->unit,
        'student' => $invoice->student,
        'bankAccounts' => collect(),
    ])->render();

    expect($invoiceHtml)
        ->toContain('Jl. Sarjana Blok A No. 25 Timbangan, Indralaya Utara, Ogan Ilir')
        ->toContain('data:image/png;base64,');

    $pdfService = app(InvoicePdfService::class);
    $pdfOutput = $pdfService->generate($invoice)->output();
    expect(str_starts_with($pdfOutput, '%PDF'))->toBeTrue();

    $cashAccount = CashAccount::withoutGlobalScope('unit_scope')->where('unit_id', $sditUnit->id)->firstOrFail();
    $payment = app(RecordStudentPaymentAction::class)->execute(
        studentId: $invoice->student_id,
        cashAccountId: $cashAccount->id,
        paymentMethod: PaymentMethod::CASH,
        items: [['invoice_id' => $invoice->id, 'amount' => (int) $invoice->net_amount]],
        createdByUserId: $sughesti->id
    );

    $receiptHtml = view('pdf.receipt', [
        'payment' => $payment->load(['unit', 'student.classroom', 'cashAccount', 'creator', 'items.invoice.feeComponent']),
        'unit' => $payment->unit,
        'student' => $payment->student,
        'items' => $payment->items,
    ])->render();

    expect($receiptHtml)
        ->toContain('Jl. Sarjana Blok A No. 25 Timbangan, Indralaya Utara, Ogan Ilir')
        ->toContain('data:image/png;base64,');

    $receiptPdf = app(ReceiptPdfService::class)->generate($payment)->output();
    expect(str_starts_with($receiptPdf, '%PDF'))->toBeTrue();

    $this->actingAs($sughesti)
        ->get(route('invoices.download', $invoice))
        ->assertStatus(200)
        ->assertHeader('content-type', 'application/pdf');

    $tkAdmin = User::where('email', 'tk@sitrobbani.sch.id')->firstOrFail();
    $this->actingAs($tkAdmin)
        ->get(route('invoices.download', $invoice))
        ->assertStatus(404);
});

it('restricts application settings to authorized foundation roles and persists branding typography and user updates', function () {
    $tkAdmin = User::where('email', 'tk@sitrobbani.sch.id')->firstOrFail();
    $sughesti = User::where('email', 'sughesti@sitrobbani.sch.id')->firstOrFail();
    $dessy = User::where('email', 'dessy@sitrobbani.sch.id')->firstOrFail();

    $this->actingAs($tkAdmin)->get(route('settings.index'))->assertStatus(403);
    $this->actingAs($dessy)->get(route('settings.index'))->assertStatus(200);

    // Update general branding & logo upload
    $fakeLogo = UploadedFile::fake()->image('custom-logo.png', 200, 80);
    $this->actingAs($sughesti)
        ->post(route('settings.update-general'), [
            'app_name' => 'SmartKas Robbani',
            'app_tagline' => 'Keuangan Sekolah Terpadu',
            'foundation_name' => 'Yayasan Generasi Robbani Sumsel',
            'foundation_short_name' => 'Robbani Sumsel',
            'foundation_address' => 'Jl. Sarjana Blok A No. 25 Timbangan, Indralaya Utara, Ogan Ilir',
            'foundation_phone' => '0711-580999',
            'foundation_email' => 'bendahara@sitrobbani.sch.id',
            'academic_year_label' => '2026/2027',
            'foundation_logo' => $fakeLogo,
        ])
        ->assertRedirect(route('settings.index', ['tab' => 'general']));

    $uploadedPath = app_setting('foundation_logo_path');
    expect(app_setting('app_name'))->toBe('SmartKas Robbani');
    expect($uploadedPath)->toStartWith('uploads/settings/foundation_logo_path_');

    // Clean up test uploaded file
    if ($uploadedPath && is_file(public_path($uploadedPath))) {
        @unlink(public_path($uploadedPath));
    }

    // Update typography settings & verify live CSS injection
    $this->actingAs($sughesti)
        ->post(route('settings.update-appearance'), [
            'font_family' => 'inter',
            'font_size' => '16',
            'table_density' => 'compact',
            'theme_color' => 'emerald',
        ])
        ->assertRedirect(route('settings.index', ['tab' => 'appearance']));

    $this->actingAs($sughesti)
        ->get(route('dashboard'))
        ->assertStatus(200)
        ->assertSee('font-size: 16px;', false)
        ->assertSee("'Inter'", false)
        ->assertSee('data-density="compact"', false);

    // Update invoice settings
    $this->actingAs($sughesti)
        ->post(route('settings.update-invoice'), [
            'invoice_prefix' => 'TAG',
            'receipt_prefix' => 'KWT',
            'default_due_day' => 15,
            'code_display_mode' => 'full',
            'show_foundation_logo_on_pdf' => '1',
            'invoice_footer_note' => 'Catatan footer baru',
            'receipt_signer_title' => 'Bendahara Operasional',
        ])
        ->assertRedirect(route('settings.index', ['tab' => 'invoice']));

    expect(format_tx_code('OCT2026385RUOX5BAX4U8RUYX'))->toBe('OCT2026385RUOX5BAX4U8RUYX');

    // Store & update new admin account + block self deletion
    $this->actingAs($sughesti)
        ->post(route('settings.users.store'), [
            'name' => 'Staf Keuangan Baru',
            'email' => 'staf.baru@sitrobbani.sch.id',
            'phone' => '628120000999',
            'role' => 'finance_admin',
            'unit_id' => Unit::where('code', 'SDIT')->value('id'),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => '1',
        ])
        ->assertRedirect(route('settings.index', ['tab' => 'users']));

    $createdUser = User::where('email', 'staf.baru@sitrobbani.sch.id')->firstOrFail();
    expect($createdUser->hasRole('finance_admin'))->toBeTrue();

    $this->actingAs($sughesti)
        ->delete(route('settings.users.destroy', $sughesti))
        ->assertRedirect(route('settings.index', ['tab' => 'users']))
        ->assertSessionHas('error');

    $this->actingAs($sughesti)
        ->delete(route('settings.users.destroy', $createdUser))
        ->assertRedirect(route('settings.index', ['tab' => 'users']))
        ->assertSessionHas('success');

    expect($createdUser->fresh()->trashed())->toBeTrue();
});
