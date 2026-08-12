<?php

namespace App\Services;

use App\Models\ProductVariant;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Product;


class VariantService{

    public function getVariantsByProduct($productId){
        return ProductVariant::where('product_id', $productId)->get();
    }

    public function getVariantById($id){
        return ProductVariant::with('product')->findOrFail($id);
    }

    public function createVariant(array $data){
        $product = Product::find($data['product_id']);

        if(!$product){
            throw new Exception("Không tìm thấy sản phẩm");
        }

        if(ProductVariant::where('sku', $data['sku'])->exists()){
            throw new \Exception('Mã Sku đã tồn tại');
        }

        return ProductVariant::create([
            'product_id' => $data['product_id'],
            'sku' => $data['sku'],
            'color' => $data['color'],
            'size' => $data['size'],
            'stock_quantity' => $data['stock_quantity'] ?? 0,
            'price_override' => $data['price_override'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);
    }

    public function updateVariant($id, array $data){
        $variant = ProductVariant::findOrFail($id);
        $variant->update([
            'sku' => $data['sku'] ?? $variant->sku,
            'color' => $data['color'] ?? $variant->color,
            'size' => $data['size'] ?? $variant->size,
            'stock_quantity' => $data['stock_quantity'] ?? $variant->stock_quantity,
            'price_override' => $data['price_override'] ?? $variant->price_override,
            'status' => $data['status'] ?? $variant->status,
        ]);
        return $variant;
    }

    public function updateStock($id, int $newStock)
    {
        $variant = ProductVariant::findOrFail($id);
        $variant->update(['stock_quantity' => $newStock]);
        return $variant;
    }

    public function deleteVariant($id)
    {
        $variant = ProductVariant::findOrFail($id);
        return $variant->delete();
    }


        public function bulkUpdate(array $data)
    {
        // data: { product_id, price_adjustment, stock_adjustment }
        // price_adjustment: "+10%" hoặc "+50000" hoặc "-5%"
        // stock_adjustment: "+5" hoặc "-3"
        $productId = $data['product_id'];
        $variants = ProductVariant::where('product_id', $productId)->get();

        $priceAdj = $data['price_adjustment'] ?? null;
        $stockAdj = $data['stock_adjustment'] ?? null;

        DB::beginTransaction();
        try {
            foreach ($variants as $variant) {
                if ($priceAdj) {
                    $currentPrice = $variant->price_override ?? 0;
                    if ($currentPrice == 0) {
                        // Nếu không có price_override thì lấy base_price từ product
                        $product = $variant->product;
                        $currentPrice = $product->base_price;
                    }
                    $newPrice = $this->applyAdjustment($currentPrice, $priceAdj);
                    $variant->price_override = $newPrice;
                }
                if ($stockAdj) {
                    $newStock = $this->applyStockAdjustment($variant->stock_quantity, $stockAdj);
                    $variant->stock_quantity = $newStock;
                }
                $variant->save();
            }
            DB::commit();
            return $variants;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk update failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function applyAdjustment($current, $adjustment)
    {
        if (strpos($adjustment, '%') !== false) {
            $percent = (float) str_replace(['+', '-', '%'], '', $adjustment);
            $sign = strpos($adjustment, '-') === 0 ? -1 : 1;
            return $current * (1 + ($sign * $percent / 100));
        } else {
            // số tiền cố định
            return $current + (float) $adjustment;
        }
    }

    private function applyStockAdjustment($current, $adjustment)
    {
        return $current + (int) $adjustment;
    }


}