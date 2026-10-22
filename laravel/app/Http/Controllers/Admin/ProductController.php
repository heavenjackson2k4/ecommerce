<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class ProductController extends Controller{
    private const SHOE_CATEGORY_PARENT_SLUG = 'giay';
    private const CLOTH_CATEGORY_PARENT_SLUG = 'quan-ao';
    private const SHOE_STUD_TYPES = ['TF', 'FG', 'AG', 'IC', 'SG', 'NONE'];
    private const PRODUCT_STATUSES = [
        'draft' => 'draft',
        'active' => 'active',
        'archived' => 'archived',
    ];

    protected ProductService $productService;
    protected CategoryService $categoryService;

    public function __construct(ProductService $productService, CategoryService $categoryService)
    {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
    }


    public function index(Request $request){
        $filters = $request->only(['category_id', 'status']);

        $product= $this->productService->getAllProducts($filters);

        return response()->json(['data'=>$product]);
    }

    public function show($id){
        $product = $this->productService->getProductById($id);
        return response()->json(['data'=>$product]);
    }

    public function destroy($id){
        $this->productService->deleteProduct($id);
        return response()->json(['message'=>'Sản phẩm đã được xóa']);
    }



    // Hiển thị form nhập giày
        public function createShoe()
        {
            $categories = $this->categoryService->getActiveChildrenByParentSlug(self::SHOE_CATEGORY_PARENT_SLUG);
            $studTypes = self::SHOE_STUD_TYPES;

            return view('admin.nhap-giay', compact('categories', 'studTypes'));
        }

        // Hiển thị form nhập quần áo
        public function createCloth()
        {
            $categories = $this->categoryService->getActiveChildrenByParentSlug(self::CLOTH_CATEGORY_PARENT_SLUG);
            return view('admin.nhap-quan-ao', compact('categories'));
        }

        public function inventoryClothes(Request $request)
        {
            return $this->inventory($request, 'CLOTH');
        }

        public function inventoryShoes(Request $request)
        {
            return $this->inventory($request, 'SHOE');
        }

        private function inventory(Request $request, string $productType)
        {
            $isShoe = $productType === 'SHOE';
            $search = trim((string) $request->string('search')) ?: null;
            $products = $this->productService->getInventoryProducts($productType, $search);

            return view('admin.inventory.index', [
                'products' => $products,
                'type' => $isShoe ? 'shoe' : 'cloth',
                'productStatuses' => self::PRODUCT_STATUSES,
            ]);
        }

    public function updateInventoryProductStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(self::PRODUCT_STATUSES))],
            'current_status' => ['required', Rule::in(array_keys(self::PRODUCT_STATUSES))],
        ]);

        try {
            $product = $this->productService->updateInventoryProductStatus($id, $validated['status'], $validated['current_status']);
        } catch (\DomainException $e) {
            throw \Illuminate\Validation\ValidationException::withMessages(['status' => $e->getMessage()]);
        }

        return response()->json([
            'message' => 'Cập nhật trạng thái sản phẩm thành công.',
            'data' => ['id' => $product->id, 'status' => $product->status],
        ]);
    }


    // app/Http/Controllers/Admin/ProductController.php

public function storeShoe(Request $request)
{
    $categoryIds = $this->categoryService
        ->getActiveChildrenByParentSlug(self::SHOE_CATEGORY_PARENT_SLUG)
        ->pluck('id')
        ->all();

    $this->normalizeShoeStudTypes($request);

    if ($request->has('images') && is_array($request->images)) {
        $images = $request->images;
        foreach ($images as $key => $image) {
            if (isset($image['is_primary'])) {
                $images[$key]['is_primary'] = filter_var($image['is_primary'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            }
        }
        $request->merge(['images' => $images]);
    }
    // dd($request->input('images'));
    $validated = $request->validate([
        'category_id' => ['required', 'integer', Rule::in($categoryIds)],
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255|unique:products,slug',
        'base_price' => 'required|numeric|min:0',
        'description' => 'nullable|string',
        'status' => ['nullable', Rule::in(['draft', 'active', 'archived'])],
        'silo' => 'nullable|string|max:100',

        // Ít nhất phải có variants hoặc (sizes & colors & stud_types)
        'variants' => 'nullable|array',
        'variants.*.size' => 'required_with:variants|string|max:20',
        'variants.*.color' => 'required_with:variants|string|max:50',
        'variants.*.stud_type' => ['required_with:variants', 'string', Rule::in(self::SHOE_STUD_TYPES)],
        'variants.*.quantity' => 'nullable|integer|min:0',
        'variants.*.price_override' => 'nullable|numeric|min:0',
        'variants.*.status' => ['nullable', Rule::in(['active', 'inactive'])],

        'sizes' => 'nullable|array',
        'sizes.*' => 'string|max:20',
        'colors' => 'nullable|array',
        'colors.*' => 'string|max:50',
        'stud_types' => 'nullable|array',
        'stud_types.*' => ['string', Rule::in(self::SHOE_STUD_TYPES)],


        // Trong storeShoe, thêm rules:
        'images' => 'nullable|array|max:20',
        'images.*.image_url' => 'required|string|max:500',
        'images.*.public_id' => 'required|string|max:255',
        'images.*.color' => 'required|string|max:50',
        'images.*.stud_type' => ['nullable', 'string', Rule::in(self::SHOE_STUD_TYPES)],
        'images.*.is_primary' => 'nullable|boolean',
        'images.*.display_order' => 'nullable|integer|min:0',
    ], [
        'category_id.in' => 'Danh mục đã chọn không thuộc nhóm giày.',
        'variants.*.stud_type.in' => 'Loại đinh chỉ được phép là TF, FG, AG, IC, SG hoặc NONE.',
        'stud_types.*.in' => 'Loại đinh chỉ được phép là TF, FG, AG, IC, SG hoặc NONE.',
        'images.*.stud_type.in' => 'Loại đinh của ảnh không hợp lệ.',
    ]);

    if (isset($validated['images'])) {
            $data['images'] = $validated['images'];
        }

    // Kiểm tra nếu không có dữ liệu biến thể nào
    $hasVariants = !empty($validated['variants']);
    $hasAuto = !empty($validated['sizes']) && !empty($validated['colors']) && !empty($validated['stud_types']);

    if ($hasVariants && $this->hasDuplicateShoeVariants($validated['variants'])) {
        return redirect()->back()
            ->withInput()
            ->withErrors(['variants' => 'Danh sách có biến thể trùng size, màu và loại đinh.']);
    }
    
    if (!$hasVariants && !$hasAuto) {
        return redirect()->back()
            ->withInput()
            ->withErrors(['variants' => 'Vui lòng nhập ít nhất một biến thể (tự động hoặc thủ công).']);
    }

    // Chuẩn bị dữ liệu cho service
    $data = [
        'category_id' => $validated['category_id'],
        'name' => $validated['name'],
        'slug' => $validated['slug'],
        'product_type' => 'SHOE',
        'base_price' => $validated['base_price'],
        'description' => $validated['description'] ?? null,
        'status' => $validated['status'] ?? 'active',
        'shoe_detail' => ['silo' => $validated['silo'] ?? null],
    ];

    // ===== THÊM VÀO SAU KHI TẠO $data = [ ... ] =====
    if (isset($validated['images']) && is_array($validated['images'])) {
        // Xử lý is_primary thành boolean (đã làm)
        $images = $validated['images'];
        foreach ($images as $key => $image) {
            if (isset($image['is_primary'])) {
                $images[$key]['is_primary'] = filter_var($image['is_primary'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            }
            // Đảm bảo color không bị null
            if (!isset($image['color']) || empty($image['color'])) {
                // Nếu thiếu color, bỏ qua ảnh này
                unset($images[$key]);
            }
        }
        $data['images'] = array_values($images); // reset index
    }

    if ($hasVariants) {
        $data['variants'] = $validated['variants'];
    } else {
        $data['sizes'] = $validated['sizes'];
        $data['colors'] = $validated['colors'];
        $data['stud_types'] = $validated['stud_types'];
    }

    try {
        $product = $this->productService->createProduct($data);
        
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Shoe created successfully', 'data' => $product], 201);
        }

        return redirect()->route('admin.nhap-giay')->with('success', 'Thêm giày thành công!');
    } catch (\Illuminate\Validation\ValidationException $e) {
        throw $e;
    } catch (\Exception $e) {
        // Log lỗi để debug
        \Log::error('Lỗi tạo giày: ' . $e->getMessage());
        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
    }
}

public function storeCloth(Request $request)
{
    $categoryIds = $this->categoryService
        ->getActiveChildrenByParentSlug(self::CLOTH_CATEGORY_PARENT_SLUG)
        ->pluck('id')
        ->all();

    if ($request->has('images') && is_array($request->images)) {
        $images = $request->images;
        foreach ($images as $key => $image) {
            if (isset($image['is_primary'])) {
                $images[$key]['is_primary'] = filter_var($image['is_primary'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            }
        }
        $request->merge(['images' => $images]);
    }
    $validated = $request->validate([
        'category_id' => ['required', 'integer', Rule::in($categoryIds)],
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255|unique:products,slug',
        'base_price' => 'required|numeric|min:0',
        'description' => 'nullable|string',
        'status' => ['nullable', Rule::in(['draft', 'active', 'archived'])],
        'sleeve_type' => ['required', Rule::in(['SHORT', 'LONG'])],
        // Variants: có thể là mảng hoặc để trống (nếu dùng tự động)
        'variants' => 'nullable|array',
        'variants.*.size' => 'required_with:variants|string|max:20',
        'variants.*.color' => 'required_with:variants|string|max:50',
        'variants.*.quantity' => 'nullable|integer|min:0',
        'variants.*.price_override' => 'nullable|numeric|min:0',
        'variants.*.status' => ['nullable', Rule::in(['active', 'inactive'])],
        // Các trường tạo tự động
        'sizes' => 'nullable|string',
        'colors' => 'nullable|string',


        'images' => 'nullable|array|max:20',
        'images.*.image_url' => 'required|string|max:500',
        'images.*.public_id' => 'required|string|max:255',
        'images.*.color' => 'required|string|max:50',
        'images.*.is_primary' => 'nullable|boolean',
        'images.*.display_order' => 'nullable|integer|min:0',

    ], [
        'category_id.in' => 'Danh mục đã chọn không thuộc nhóm quần áo.',
    ]);

    // Chuẩn bị dữ liệu
    $data = [
        'category_id' => $validated['category_id'],
        'name' => $validated['name'],
        'slug' => $validated['slug'],
        'product_type' => 'CLOTH',
        'base_price' => $validated['base_price'],
        'description' => $validated['description'] ?? null,
        'status' => $validated['status'] ?? 'active',
        'cloth_detail' => ['sleeve_type' => $validated['sleeve_type']],
    ];

    // if (isset($validated['images'])) {
    //     $data['images'] = $validated['images'];
    // }
    // ===== THÊM VÀO SAU KHI TẠO $data = [ ... ] =====
if (isset($validated['images']) && is_array($validated['images'])) {
    // Xử lý is_primary thành boolean (đã làm)
    $images = $validated['images'];
    foreach ($images as $key => $image) {
        if (isset($image['is_primary'])) {
            $images[$key]['is_primary'] = filter_var($image['is_primary'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }
        // Đảm bảo color không bị null
        if (!isset($image['color']) || empty($image['color'])) {
            // Nếu thiếu color, bỏ qua ảnh này
            unset($images[$key]);
        }
    }
    $data['images'] = array_values($images); // reset index
}

    // Xử lý variants
    if (!empty($validated['variants'])) {
        $data['variants'] = $validated['variants'];
    } else {
        // Chuyển chuỗi thành mảng
        $sizes = !empty($validated['sizes']) ? array_map('trim', explode(',', $validated['sizes'])) : [];
        $colors = !empty($validated['colors']) ? array_map('trim', explode(',', $validated['colors'])) : [];
        if (!empty($sizes) && !empty($colors)) {
            $data['sizes'] = $sizes;
            $data['colors'] = $colors;
        } else {
            return redirect()->back()
                ->withInput()
                ->withErrors(['variants' => 'Vui lòng nhập ít nhất một biến thể (tự động hoặc thủ công).']);
        }
    }

    try {
        $product = $this->productService->createProduct($data);
        return redirect()->route('admin.nhap-quan-ao')
            ->with('success', 'Thêm quần áo thành công!');
    } catch (\Illuminate\Validation\ValidationException $e) {
        throw $e;
    } catch (\Exception $e) {
        \Log::error('Lỗi tạo quần áo: ' . $e->getMessage());
        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
    }
}

private function normalizeShoeStudTypes(Request $request): void
{
    $variants = $request->input('variants');
    if (is_array($variants)) {
        foreach ($variants as $key => $variant) {
            if (isset($variant['stud_type']) && is_string($variant['stud_type'])) {
                $variants[$key]['stud_type'] = strtoupper(trim($variant['stud_type']));
            }
        }
        $request->merge(['variants' => $variants]);
    }

    $studTypes = $request->input('stud_types');
    if (is_array($studTypes)) {
        $normalizedStudTypes = [];
        foreach ($studTypes as $studType) {
            $normalizedStudTypes[] = is_string($studType)
                ? strtoupper(trim($studType))
                : $studType;
        }

        if (count(array_filter($normalizedStudTypes, 'is_string')) === count($normalizedStudTypes)) {
            $normalizedStudTypes = array_values(array_unique($normalizedStudTypes));
        }

        $request->merge(['stud_types' => $normalizedStudTypes]);
    }

    $images = $request->input('images');
    if (is_array($images)) {
        foreach ($images as $key => $image) {
            if (isset($image['stud_type']) && is_string($image['stud_type'])) {
                $images[$key]['stud_type'] = strtoupper(trim($image['stud_type']));
            }
        }
        $request->merge(['images' => $images]);
    }
}

private function hasDuplicateShoeVariants(array $variants): bool
{
    $variantKeys = collect($variants)->map(function ($variant) {
        return implode('|', [
            mb_strtolower(trim($variant['size'])),
            mb_strtolower(trim($variant['color'])),
            strtoupper(trim($variant['stud_type'])),
        ]);
    });

    return $variantKeys->duplicates()->isNotEmpty();
}
}
