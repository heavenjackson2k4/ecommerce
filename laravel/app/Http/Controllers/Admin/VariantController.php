<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\VariantService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VariantController extends Controller{
    protected VariantService $variantService;

    public function __construct(VariantService $variantService)
    {
        $this->variantService=  $variantService;
    }

    public function index($productId)
    {
        $variants = $this->variantService->getVariantsByProduct($productId);
        return response()->json(['data' => $variants]);
    }

        public function show($id)
    {
        $variant = $this->variantService->getVariantById($id);
        return response()->json(['data' => $variant]);
    }
        public function destroy($id)
    {
        $this->variantService->deleteVariant($id);
        return response()->json(['message' => 'Variant deleted.']);
    }



    public function store(Request $request, $productId)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:100|unique:product_variants,sku',
            'color' => 'required|string|max:50',
            'size' => 'required|string|max:20',
            'stock_quantity' => 'nullable|integer|min:0',
            'price_override' => 'nullable|numeric|min:0',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $validated['product_id'] = $productId;
        $variant = $this->variantService->createVariant($validated);
        return response()->json(['message' => 'Variant added.', 'data' => $variant], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'sku' => 'sometimes|string|max:100|unique:product_variants,sku,' . $id,
            'color' => 'sometimes|string|max:50',
            'size' => 'sometimes|string|max:20',
            'stock_quantity' => 'nullable|integer|min:0',
            'price_override' => 'nullable|numeric|min:0',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $variant = $this->variantService->updateVariant($id, $validated);
        return response()->json(['message' => 'Variant updated.', 'data' => $variant]);
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'price_adjustment' => 'nullable|string',
            'stock_adjustment' => 'nullable|string',
        ]);

        $variants = $this->variantService->bulkUpdate($validated);
        return response()->json(['message' => 'Bulk update successful.', 'data' => $variants]);
    }

}