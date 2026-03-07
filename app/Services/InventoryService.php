<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\InventoryBatch;
use App\Models\Sale;
use App\Models\SaleBatchAllocation;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Get total available stock for a brand (sum of quantity_remaining across FIFO batches).
     */
    public static function availableStock(Brand $brand): int
    {
        return (int) InventoryBatch::where('brand_id', $brand->id)
            ->sum('quantity_remaining');
    }

    /**
     * Allocate quantity from brand's batches using FIFO. Deducts from batches and returns
     * total cost (for setting sale cost_at_sale). Caller must create Sale and SaleBatchAllocation records.
     *
     * @return array{total_cost: float, allocations: array<int, int>} Map of batch_id => quantity allocated
     */
    public static function allocateFifo(Brand $brand, int $quantity): array
    {
        $batches = InventoryBatch::where('brand_id', $brand->id)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $remaining = $quantity;
        $totalCost = 0.0;
        $allocations = [];

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }
            $take = min($batch->quantity_remaining, $remaining);
            if ($take <= 0) {
                continue;
            }
            $cost = $take * (float) $batch->cost_per_unit;
            $totalCost += $cost;
            $allocations[$batch->id] = $take;
            $remaining -= $take;
            $batch->decrement('quantity_remaining', $take);
        }

        if ($remaining > 0) {
            throw new \RuntimeException('Insufficient stock to allocate. Required: ' . $quantity . ', could only allocate: ' . ($quantity - $remaining));
        }

        return ['total_cost' => $totalCost, 'allocations' => $allocations];
    }

    /**
     * Suggested sale price for selling given quantity of a brand (FIFO: sum of batch sale_price × taken).
     * Used when creating/editing a sale to pre-fill the amount field.
     */
    public static function suggestedSalePrice(Brand $brand, int $quantity): ?float
    {
        $batches = InventoryBatch::where('brand_id', $brand->id)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $remaining = $quantity;
        $total = 0.0;

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }
            $take = min($batch->quantity_remaining, $remaining);
            if ($take > 0 && $batch->sale_price !== null) {
                $total += $take * (float) $batch->sale_price;
            }
            $remaining -= $take;
        }

        if ($remaining > 0) {
            return null; // Not enough stock
        }
        return $total > 0 ? round($total, 2) : null;
    }

    /**
     * Return stock from a sale back to batches (reverse of allocate). Call when editing or deleting a sale.
     * For legacy sales (no allocation records), adds quantity back to the oldest batch of the brand.
     */
    public static function returnAllocation(Sale $sale): void
    {
        $allocations = SaleBatchAllocation::where('sale_id', $sale->id)->get();
        if ($allocations->isNotEmpty()) {
            foreach ($allocations as $allocation) {
                InventoryBatch::withTrashed()
                    ->where('id', $allocation->inventory_batch_id)
                    ->increment('quantity_remaining', $allocation->quantity);
            }
            SaleBatchAllocation::where('sale_id', $sale->id)->delete();
            return;
        }
        // Legacy sale (created before FIFO): return quantity to oldest batch of this brand
        $batch = InventoryBatch::where('brand_id', $sale->brand_id)
            ->orderBy('created_at')->orderBy('id')->first();
        if ($batch && $sale->quantity > 0) {
            $batch->increment('quantity_remaining', $sale->quantity);
        }
    }

    /**
     * Allocate for a sale and persist allocations. Sets cost_at_sale on sale and creates SaleBatchAllocation records.
     * Call after Sale::create() so sale has an id.
     */
    public static function allocateForSale(Sale $sale, Brand $brand, int $quantity): void
    {
        $result = self::allocateFifo($brand, $quantity);
        $totalCost = $result['total_cost'];
        $costPerUnit = $quantity > 0 ? $totalCost / $quantity : 0;
        $sale->update(['cost_at_sale' => round($costPerUnit, 2)]);

        foreach ($result['allocations'] as $batchId => $qty) {
            SaleBatchAllocation::create([
                'sale_id' => $sale->id,
                'inventory_batch_id' => $batchId,
                'quantity' => $qty,
            ]);
        }
    }
}
