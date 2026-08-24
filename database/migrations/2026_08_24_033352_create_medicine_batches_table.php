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
        Schema::dropIfExists('medicine_batches');

        Schema::create('medicine_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained('medicines')->onDelete('cascade');
            $table->string('batch_number', 50)->nullable()->comment('Số lô sản xuất hoặc mã lô nhập');
            $table->unsignedInteger('quantity')->default(0)->comment('Số lượng nhập ban đầu');
            $table->unsignedInteger('remaining_quantity')->default(0)->comment('Số lượng còn lại trong lô');
            $table->decimal('import_price', 12, 2)->default(0)->comment('Giá nhập của lô');
            $table->date('expiry_date')->nullable()->comment('Hạn sử dụng của riêng lô này');
            $table->timestamps();

            $table->index(['medicine_id', 'remaining_quantity', 'expiry_date'], 'med_batch_lookup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_batches');
    }
};
