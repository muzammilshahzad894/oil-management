<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_batch_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('inventory_batch_id')->constrained('inventory_batches')->onDelete('cascade');
            $table->integer('quantity')->comment('Quantity taken from this batch for this sale');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_batch_allocations');
    }
};
