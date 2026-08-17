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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit');
            $table->integer('stock_quantity')->default(0);
            $table->decimal('import_price', 12, 2);
            $table->decimal('sell_price', 12, 2);
            $table->date('expiry_date')->nullable();
            $table->integer('low_stock_threshold')->default(10);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
