<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Decrement product stock in a specific warehouse.
     */
    public function decrementStock(int $productId, int $warehouseId, float $quantity): void
    {
        DB::table('inventories')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->decrement('quantity', $quantity);
    }

    /**
     * Check if enough stock is available.
     */
    public function hasEnoughStock(int $productId, int $warehouseId, float $quantity): bool
    {
        $stock = DB::table('inventories')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->value('quantity') ?? 0;

        return $stock >= $quantity;
    }
}