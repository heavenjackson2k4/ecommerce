<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class ProductController extends Controller{
    protected ProductService $productService;
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
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
            $categories = \App\Models\Category::where('status', 'active')->get();
            return view('admin.nhap-giay', compact('categories'));
        }

        // Hiển thị form nhập quần áo
        public function createCloth()
        {
            $categories = \App\Models\Category::where('status', 'active')->get();
            return view('admin.nhap-quan-ao', compact('categories'));
        }


    // app/Http/Controllers/Admin/ProductController.php

public function storeShoe(Request $request)
{

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
        'category_id' => 'required|exists:categories,id',
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
        'variants.*.stud_type' => 'required_with:variants|string|max:20',
        'variants.*.quantity' => 'nullable|integer|min:0',
        'variants.*.price_override' => 'nullable|numeric|min:0',
        'variants.*.status' => ['nullable', Rule::in(['active', 'inactive'])],

        'sizes' => 'nullable|array',
        'sizes.*' => 'string|max:20',
        'colors' => 'nullable|array',
        'colors.*' => 'string|max:50',
        'stud_types' => 'nullable|array',
        'stud_types.*' => 'string|max:20',


        // Trong storeShoe, thêm rules:
        'images' => 'nullable|array|max:20',
        'images.*.image_url' => 'required|string|max:500',
        'images.*.public_id' => 'required|string|max:255',
        'images.*.color' => 'required|string|max:50',
        'images.*.stud_type' => 'nullable|string|max:20',
        'images.*.is_primary' => 'nullable|boolean',
        'images.*.display_order' => 'nullable|integer|min:0',
    ]);

    if (isset($validated['images'])) {
            $data['images'] = $validated['images'];
        }

    // Kiểm tra nếu không có dữ liệu biến thể nào
    $hasVariants = !empty($validated['variants']);
    $hasAuto = !empty($validated['sizes']) && !empty($validated['colors']) && !empty($validated['stud_types']);
    
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
        'category_id' => 'required|exists:categories,id',
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
    } catch (\Exception $e) {
        \Log::error('Lỗi tạo quần áo: ' . $e->getMessage());
        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
    }
}
}