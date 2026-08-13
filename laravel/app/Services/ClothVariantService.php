<?php

namespace App\Services;

use App\Models\ClothesVariant;
use Illuminate\Support\Facades\DB;

class ClothVariantService
{
    public function getVariantsByProduct(int $productId)
    {
        return ClothesVariant::where('product_id', $productId)->get();
    }

    public function getVariantById(int $id): ClothesVariant
    {
        return ClothesVariant::with('product')->findOrFail($id);
    }

    public function createVariant(int $productId, array $data): ClothesVariant
    {
        $data['product_id'] = $productId;
        return DB::transaction(function () use ($data) {
            return ClothesVariant::create($data);
        });
    }

    public function updateVariant(int $id, array $data): ClothesVariant
    {
        $variant = ClothesVariant::findOrFail($id);
        DB::transaction(function () use ($variant, $data) {
            $variant->update($data);
        });
        return $variant->fresh();
    }

    public function deleteVariant(int $id): bool
    {
        $variant = ClothesVariant::findOrFail($id);
        return DB::transaction(function () use ($variant) {
            return $variant->delete();
        });
    }

    public function updateStock(int $id, int $quantity): ClothesVariant
    {
        $variant = ClothesVariant::findOrFail($id);
        DB::transaction(function () use ($variant, $quantity) {
            $variant->update(['quantity' => $quantity]);
        });
        return $variant->fresh();
    }

    public function bulkUpdate(int $productId, array $adjustments): array
    {
        $variants = ClothesVariant::where('product_id', $productId)->get();
        if ($variants->isEmpty()) {
            throw new \Exception('Không tìm thấy variants nào cho sản phẩm này.');
        }

        $updated = [];
        DB::transaction(function () use ($variants, $adjustments, &$updated) {
            foreach ($variants as $variant) {
                if (isset($adjustments['price_adjustment'])) {
                    $price = $variant->price_override ?? $variant->product->base_price;
                    $newPrice = $this->applyAdjustment($price, $adjustments['price_adjustment']);
                    $variant->price_override = $newPrice;
                }

                if (isset($adjustments['stock_adjustment'])) {
                    $newStock = $variant->quantity + (int) $adjustments['stock_adjustment'];
                    if ($newStock < 0) $newStock = 0;
                    $variant->quantity = $newStock;
                }

                $variant->save();
                $updated[] = $variant->fresh();
            }
        });

        return $updated;
    }

    private function applyAdjustment(float $currentPrice, string $adjustment): float
    {
        $adjustment = trim($adjustment);
        if (str_ends_with($adjustment, '%')) {
            $percent = (float) rtrim($adjustment, '%');
            return $currentPrice * (1 + $percent / 100);
        } else {
            $value = (float) $adjustment;
            return max(0, $currentPrice + $value);
        }
    }
}