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
        Schema::create('fee_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->string('code', 30);
            $table->string('name', 100);
            $table->string('billing_type', 20)->default('monthly');
            $table->unsignedBigInteger('default_amount')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['unit_id', 'billing_type']);
            $table->index(['unit_id', 'academic_year_id']);
        });

        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->nullable()->constrained('units')->restrictOnDelete();
            $table->string('name', 100);
            $table->string('type', 20)->default('percentage'); // percentage | fixed
            $table->unsignedBigInteger('value');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('student_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('discount_id')->constrained('discounts')->restrictOnDelete();
            $table->foreignId('fee_component_id')->nullable()->constrained('fee_components')->restrictOnDelete();
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'fee_component_id']);
        });

        Schema::create('cash_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->nullable()->constrained('units')->restrictOnDelete();
            $table->string('name', 100);
            $table->string('type', 20)->default('cash'); // bank | cash | petty_cash
            $table->string('account_number', 50)->nullable();
            $table->string('bank_name', 50)->nullable();
            $table->bigInteger('current_balance')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['unit_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_accounts');
        Schema::dropIfExists('student_discounts');
        Schema::dropIfExists('discounts');
        Schema::dropIfExists('fee_components');
    }
};
