<?php

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
        Schema::create('expense_vouchers', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['medicine_import', 'operational', 'other']);
            $table->decimal('amount', 12, 2);
            $table->text('description');
            $table->date('voucher_date');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_vouchers');
    }
};
