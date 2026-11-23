<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('expense_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->restrictOnDelete();
            $table->foreignId('cash_account_id')->nullable()->constrained('cash_accounts')->restrictOnDelete();
            $table->string('request_number', 50)->unique();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('expense_type', 30)->default('operational'); // cash_advance | operational | petty_cash
            $table->unsignedBigInteger('requested_amount');
            $table->unsignedBigInteger('approved_amount')->nullable();
            $table->date('transaction_date');
            $table->string('status', 30)->default('draft'); // draft | submitted | approved_principal | approved_treasurer | disbursed | rejected | void
            $table->text('rejection_reason')->nullable();
            $table->string('attachment_path', 255)->nullable();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('principal_approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('principal_approved_at')->nullable();
            $table->foreignId('treasurer_approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('treasurer_approved_at')->nullable();
            $table->foreignId('disbursed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('disbursed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['unit_id', 'status']);
            $table->index(['unit_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_requests');
        Schema::dropIfExists('expense_categories');
    }
};
