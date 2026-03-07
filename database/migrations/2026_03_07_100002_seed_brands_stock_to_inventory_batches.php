<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $brands = DB::table('brands')->where('quantity', '>', 0)->get();
        $now = now()->format('Y-m-d H:i:s');
        $today = now()->format('Y-m-d');
        foreach ($brands as $brand) {
            DB::table('inventory_batches')->insert([
                'brand_id' => $brand->id,
                'quantity' => $brand->quantity,
                'quantity_remaining' => $brand->quantity,
                'cost_per_unit' => $brand->cost_price ?? 0,
                'received_at' => $today,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Optionally restore brands.quantity from sum of batches - leave empty for simplicity
    }
};
