<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ShoesVariant;
use App\Models\ClothesVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Shoe;
use App\Models\Cloth;


class ProductService
{
    public function getAllProducts(array $filters = [])
    {
        $query = Product::query()->with(['category', 'shoesVariants', 'clothesVariants']);

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['product_type'])) {
            $query->where('product_type', $filters['product_type']);
        }

        return $query->orderBy('id', 'desc')->get();
    }

    public function getProductById(int $id): Product
    {
        return Product::with([
            'category',
            'shoe',
            'cloth',
            'shoesVariants',
            'clothesVariants'
        ])->findOrFail($id);
    }

    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'slug' => $data['slug'],
                'product_type' => $data['product_type'],
                'base_price' => $data['base_price'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'active',
            ]);

            // Tạo chi tiết giày hoặc quần áo
            if ($data['product_type'] === 'SHOE' && isset($data['shoe_detail'])) {
                $product->shoe()->create($data['shoe_detail']);
            } elseif ($data['product_type'] === 'CLOTH' && isset($data['cloth_detail'])) {
                $product->cloth()->create($data['cloth_detail']);
            }

            // Tạo biến thể
            $this->createVariants($product, $data);

            // ===== THÊM VÀO SAU KHI TẠO VARIANTS =====
            if ($data['product_type'] === 'SHOE' && isset($data['images'])) {
                $this->saveShoeImages($product->shoe, $data['images']);
            } elseif ($data['product_type'] === 'CLOTH' && isset($data['images'])) {
                $this->saveClothImages($product->cloth, $data['images']);
            }

            return $product->load(['shoesVariants', 'clothesVariants', 'shoe', 'cloth']);
        });
    }

    private function saveShoeImages(Shoe $shoe, array $images): void
    {
        foreach($images as $imageData){
            $shoe->images()->create([
            'image_url' => $imageData['image_url'],
            'public_id' => $imageData['public_id'],
            'color' => $imageData['color'],
            'stud_type' => $imageData['stud_type'] ?? null,
            'is_primary' => filter_var($imageData['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'display_order' => $imageData['display_order'] ?? 0,
            ]);
        }
    }

    private function saveClothImages(Cloth $cloth, array $images): void
    {
        foreach($images as $imageData){
            $cloth->images()->create([
            'image_url' => $imageData['image_url'],
            'public_id' => $imageData['public_id'],
            'color' => $imageData['color'],
            'is_primary' => filter_var($imageData['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'display_order' => $imageData['display_order'] ?? 0,
            ]);
        }
    }

    /**
     * Tạo biến thể cho sản phẩm
     */
    private function createVariants(Product $product, array $data): void
    {
        if ($product->product_type === 'SHOE') {
            $this->createShoeVariants($product, $data);
        } elseif ($product->product_type === 'CLOTH') {
            $this->createClothVariants($product, $data);
        }
    }

    /**
     * Tạo biến thể cho giày
     */
    private function createShoeVariants(Product $product, array $data): void
    {
        // Nếu có danh sách variants cụ thể, ưu tiên dùng
        if (isset($data['variants']) && is_array($data['variants']) && count($data['variants']) > 0) {
            foreach ($data['variants'] as $variant) {
                ShoesVariant::create([
                    'product_id' => $product->id,
                    'size' => $variant['size'],
                    'color' => $variant['color'],
                    'stud_type' => $variant['stud_type'],
                    'price_override' => $variant['price_override'] ?? null,
                    'quantity' => $variant['quantity'] ?? 0,
                    'status' => $variant['status'] ?? 'active',
                ]);
            }
            return;
        }

        // Nếu không có variants cụ thể, tạo từ các mảng sizes, colors, stud_types
        if (isset($data['sizes']) && isset($data['colors']) && isset($data['stud_types'])) {
            foreach ($data['sizes'] as $size) {
                foreach ($data['colors'] as $color) {
                    foreach ($data['stud_types'] as $studType) {
                        ShoesVariant::create([
                            'product_id' => $product->id,
                            'size' => $size,
                            'color' => $color,
                            'stud_type' => $studType,
                            'price_override' => null,
                            'quantity' => 0,
                            'status' => 'active',
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Tạo biến thể cho quần áo
     */
    private function createClothVariants(Product $product, array $data): void
    {
        // Nếu có danh sách variants cụ thể, ưu tiên dùng
        if (isset($data['variants']) && is_array($data['variants']) && count($data['variants']) > 0) {
            foreach ($data['variants'] as $variant) {
                ClothesVariant::create([
                    'product_id' => $product->id,
                    'size' => $variant['size'],
                    'color' => $variant['color'],
                    'price_override' => $variant['price_override'] ?? null,
                    'quantity' => $variant['quantity'] ?? 0,
                    'status' => $variant['status'] ?? 'active',
                ]);
            }
            return;
        }

        // Nếu không có variants cụ thể, tạo từ các mảng sizes và colors
        if (isset($data['sizes']) && isset($data['colors'])) {
            foreach ($data['sizes'] as $size) {
                foreach ($data['colors'] as $color) {
                    ClothesVariant::create([
                        'product_id' => $product->id,
                        'size' => $size,
                        'color' => $color,
                        'price_override' => null,
                        'quantity' => 0,
                        'status' => 'active',
                    ]);
                }
            }
        }
    }

    public function updateProduct(int $id, array $data): Product
    {
        $product = Product::findOrFail($id);
        DB::transaction(function () use ($product, $data) {
            $product->update([
                'category_id' => $data['category_id'] ?? $product->category_id,
                'name' => $data['name'] ?? $product->name,
                'slug' => $data['slug'] ?? $product->slug,
                'base_price' => $data['base_price'] ?? $product->base_price,
                'description' => $data['description'] ?? $product->description,
                'status' => $data['status'] ?? $product->status,
            ]);

            if (isset($data['shoe_detail']) && $product->product_type === 'SHOE') {
                $product->shoe()->updateOrCreate(
                    ['product_id' => $product->id],
                    $data['shoe_detail']
                );
            }

            if (isset($data['cloth_detail']) && $product->product_type === 'CLOTH') {
                $product->cloth()->updateOrCreate(
                    ['product_id' => $product->id],
                    $data['cloth_detail']
                );
            }
        });

        return $product->fresh(['shoesVariants', 'clothesVariants', 'shoe', 'cloth']);
    }

    public function deleteProduct(int $id): bool
    {
        $product = Product::findOrFail($id);
        return DB::transaction(function () use ($product) {
            return $product->delete();
        });
    }
}