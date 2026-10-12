<?php

namespace App\Services;

use App\Models\ClothesVariant;
use App\Models\Product;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ClothVariantService
{
    private const MAX_INVENTORY_QUANTITY = 1000000;
    private const MAX_VARIANT_PRICE = 9999999999.99;

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

    public function updateInventory(int $id, array $data): ClothesVariant
    {
        return DB::transaction(function () use ($id, $data) {
            $variant = ClothesVariant::query()->lockForUpdate()->findOrFail($id);
            $updates = [];

            if (array_key_exists('quantity', $data)) {
                $updates['quantity'] = $data['quantity'];
            }

            if (array_key_exists('price_override', $data)) {
                $updates['price_override'] = $data['price_override'];
            }

            $variant->update($updates);

            return $variant->fresh();
        });
    }

    public function bulkUpdateInventory(
        int $productId,
        array $items,
        string $mode,
        ?array $priceAdjustment = null
    ): Collection
    {
        return DB::transaction(function () use ($productId, $items, $mode, $priceAdjustment) {
            $variantIds = collect($items)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $product = $priceAdjustment
                ? Product::query()->select(['id', 'base_price'])->lockForUpdate()->findOrFail($productId)
                : null;

            $variants = ClothesVariant::query()
                ->where('product_id', $productId)
                ->whereIn('id', $variantIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($variants->count() !== $variantIds->count()) {
                throw new DomainException('Có biến thể không thuộc sản phẩm đang cập nhật.');
            }

            $updatedAt = now();
            $updates = collect($items)->map(function ($item) use ($variants, $mode, $priceAdjustment, $product, $updatedAt) {
                $variant = $variants->get((int) $item['id']);
                $quantity = $mode === 'increment'
                    ? $variant->quantity + (int) $item['quantity']
                    : (int) $item['quantity'];

                if ($quantity > self::MAX_INVENTORY_QUANTITY) {
                    throw new DomainException('Số lượng sau cập nhật vượt quá giới hạn cho phép.');
                }

                $priceOverride = $priceAdjustment
                    ? $this->calculatePriceOverride(
                        (float) ($variant->price_override ?? $product->base_price),
                        $priceAdjustment
                    )
                    : $variant->price_override;

                return [
                    'id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'size' => $variant->size,
                    'color' => $variant->color,
                    'price_override' => $priceOverride,
                    'quantity' => $quantity,
                    'status' => $variant->status,
                    'created_at' => $variant->created_at,
                    'updated_at' => $updatedAt,
                ];
            })->all();

            ClothesVariant::upsert($updates, ['id'], ['quantity', 'price_override', 'updated_at']);

            return ClothesVariant::query()
                ->where('product_id', $productId)
                ->whereIn('id', $variantIds)
                ->get();
        });
    }

    private function calculatePriceOverride(float $currentPrice, array $adjustment): ?float
    {
        $value = (float) ($adjustment['value'] ?? 0);

        $price = match ($adjustment['mode']) {
            'set' => round($value, 2),
            'increase_amount' => round($currentPrice + $value, 2),
            'decrease_amount' => round(max(0, $currentPrice - $value), 2),
            'increase_percent' => round($currentPrice * (1 + $value / 100), 2),
            'decrease_percent' => round(max(0, $currentPrice * (1 - $value / 100)), 2),
            'base' => null,
        };

        if ($price !== null && (!is_finite($price) || $price > self::MAX_VARIANT_PRICE)) {
            throw new DomainException('Giá bán sau điều chỉnh vượt quá giới hạn cho phép.');
        }

        return $price;
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