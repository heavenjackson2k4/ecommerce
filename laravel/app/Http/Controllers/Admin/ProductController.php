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



    // app/Http/Controllers/Admin/ProductController.php

public function storeShoe(Request $request)
{
    $validated = $request->validate([
        'category_id' => 'required|exists:categories,id',
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255|unique:products,slug',
        'base_price' => 'required|numeric|min:0',
        'description' => 'nullable|string',
        'status' => ['nullable', Rule::in(['draft', 'active', 'archived'])],
        // Thông tin giày
        'stud_type' => 'required|string|max:20',
        'silo' => 'nullable|string|max:100',
        // Variants: phải có ít nhất 1 variant
        'variants' => 'required|array|min:1',
        'variants.*.sku' => 'required|string|max:100|unique:product_variants,sku',
        'variants.*.color' => 'required|string|max:50',
        'variants.*.size' => 'required|string|max:20', // size giày: 39, 40, 41...
        'variants.*.stock_quantity' => 'nullable|integer|min:0',
        'variants.*.price_override' => 'nullable|numeric|min:0',
        'variants.*.status' => ['nullable', Rule::in(['active', 'inactive'])],
    ]);

    // Chuẩn bị dữ liệu cho service
    $data = [
        'category_id' => $validated['category_id'],
        'name' => $validated['name'],
        'slug' => $validated['slug'],
        'product_type' => 'SHOE',
        'base_price' => $validated['base_price'],
        'description' => $validated['description'] ?? null,
        'status' => $validated['status'] ?? 'active',
        'shoe_detail' => [
            'stud_type' => $validated['stud_type'],
            'silo' => $validated['silo'] ?? null,
        ],
        'variants' => $validated['variants'],
    ];

    $product = $this->productService->createProduct($data);
    return response()->json(['message' => 'Shoe created successfully', 'data' => $product], 201);
}

public function storeCloth(Request $request)
{
    $validated = $request->validate([
        'category_id' => 'required|exists:categories,id',
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255|unique:products,slug',
        'base_price' => 'required|numeric|min:0',
        'description' => 'nullable|string',
        'status' => ['nullable', Rule::in(['draft', 'active', 'archived'])],
        // Thông tin quần áo
        'sleeve_type' => ['required', Rule::in(['SHORT', 'LONG'])],
        // Variants: phải có ít nhất 1 variant
        'variants' => 'required|array|min:1',
        'variants.*.sku' => 'required|string|max:100|unique:product_variants,sku',
        'variants.*.color' => 'required|string|max:50',
        'variants.*.size' => 'required|string|max:20', // size áo: S, M, L, XL...
        'variants.*.stock_quantity' => 'nullable|integer|min:0',
        'variants.*.price_override' => 'nullable|numeric|min:0',
        'variants.*.status' => ['nullable', Rule::in(['active', 'inactive'])],
    ]);

    $data = [
        'category_id' => $validated['category_id'],
        'name' => $validated['name'],
        'slug' => $validated['slug'],
        'product_type' => 'CLOTH',
        'base_price' => $validated['base_price'],
        'description' => $validated['description'] ?? null,
        'status' => $validated['status'] ?? 'active',
        'cloth_detail' => [
            'sleeve_type' => $validated['sleeve_type'],
        ],
        'variants' => $validated['variants'],
    ];

    $product = $this->productService->createProduct($data);
    return response()->json(['message' => 'Cloth created successfully', 'data' => $product], 201);
}
}