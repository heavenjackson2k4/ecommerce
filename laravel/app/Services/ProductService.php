<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ShoesVariant;
use App\Models\ClothesVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Shoe;
use App\Models\Cloth;
use Illuminate\Validation\ValidationException;


class ProductService
{
    private const MAX_IMAGES_PER_COLOR = 3;

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

    public function getInventoryProducts(string $productType, ?string $search = null, int $perPage = 10)
    {
        $isShoe = $productType === 'SHOE';
        $relations = $isShoe
            ? ['category', 'shoe.images', 'shoesVariants']
            : ['category', 'cloth.images', 'clothesVariants'];

        $products = Product::query()
            ->with($relations)
            ->where('product_type', $productType)
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return $products->through(function ($product) use ($isShoe) {
            $detail = $isShoe ? $product->shoe : $product->cloth;
            $variants = $isShoe ? $product->shoesVariants : $product->clothesVariants;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category?->name ?? 'Chưa phân loại',
                'status' => $product->status,
                'image' => ($detail?->images->firstWhere('is_primary', true) ?? $detail?->images->first())?->image_url,
                'variants' => $variants->map(fn ($variant) => [
                    'id' => $variant->id,
                    'size' => $variant->size,
                    'color' => $variant->color,
                    'stud_type' => $isShoe ? $variant->stud_type : null,
                    'quantity' => $variant->quantity,
                    'price_override' => $variant->price_override !== null ? (float) $variant->price_override : null,
                    'price' => (float) ($variant->price_override ?? $product->base_price),
                ])->values()->all(),
                'base_price' => (float) $product->base_price,
            ];
        });
    }

    public function createProduct(array $data): Product
    {
        if (isset($data['images'])) {
            $data['images'] = $this->prepareImages($data['images']);
        }

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

    private function prepareImages(array $images): array
    {
        $prepared = [];
        foreach (collect($images)->groupBy('color') as $color => $colorImages) {
            if ($colorImages->count() > self::MAX_IMAGES_PER_COLOR) {
                throw ValidationException::withMessages([
                    'images' => "Màu {$color} chỉ được tối đa " . self::MAX_IMAGES_PER_COLOR . ' ảnh.',
                ]);
            }

            $colorImages = $colorImages->sortBy(fn ($image) => $image['display_order'] ?? 0)->values();
            $primaryImages = $colorImages->filter(fn ($image) => filter_var($image['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN));
            if ($primaryImages->count() > 1) {
                throw ValidationException::withMessages([
                    'images' => "Màu {$color} chỉ được chọn một ảnh chính.",
                ]);
            }

            $primaryIndex = $primaryImages->keys()->first() ?? 0;
            foreach ($colorImages as $index => $image) {
                $image['is_primary'] = $index === $primaryIndex;
                $image['display_order'] = $index;
                $prepared[] = $image;
            }
        }

        return $prepared;
    }

    private function saveShoeImages(Shoe $shoe, array $images): void
    {
        if (empty($images)) return;

        $timestamp = now();
        $rows = array_map(fn ($imageData) => [
            'shoe_id' => $shoe->id,
            'image_url' => $imageData['image_url'],
            'public_id' => $imageData['public_id'],
            'color' => $imageData['color'],
            'stud_type' => $imageData['stud_type'] ?? null,
            'is_primary' => $imageData['is_primary'],
            'display_order' => $imageData['display_order'],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ], $images);

        $shoe->images()->insert($rows);
    }

    private function saveClothImages(Cloth $cloth, array $images): void
    {
        if (empty($images)) return;

        $timestamp = now();
        $rows = array_map(fn ($imageData) => [
            'cloth_id' => $cloth->id,
            'image_url' => $imageData['image_url'],
            'public_id' => $imageData['public_id'],
            'color' => $imageData['color'],
            'is_primary' => $imageData['is_primary'],
            'display_order' => $imageData['display_order'],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ], $images);

        $cloth->images()->insert($rows);
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

    public function updateInventoryProductStatus(int $id, string $status, string $currentStatus): Product
    {
        return DB::transaction(function () use ($id, $status, $currentStatus) {
            $product = Product::query()
                ->whereIn('product_type', ['SHOE', 'CLOTH'])
                ->lockForUpdate()
                ->findOrFail($id);

            if ($product->status !== $currentStatus) {
                throw new \DomainException('Trạng thái sản phẩm đã được thay đổi. Vui lòng tải lại trang trước khi cập nhật.');
            }

            $product->update(['status' => $status]);

            return $product;
        });
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
