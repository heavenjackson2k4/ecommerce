<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ShoeVariantService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShoeVariantController extends Controller
{
    protected ShoeVariantService $variantService;

    public function __construct(ShoeVariantService $variantService)
    {
        $this->variantService = $variantService;
    }

    public function index(int $productId)
    {
        $variants = $this->variantService->getVariantsByProduct($productId);
        return response()->json(['data' => $variants]);
    }

    public function store(Request $request, int $productId)
    {
        $validated = $request->validate([
            'size' => 'required|string|max:20',
            'color' => 'required|string|max:50',
            'stud_type' => 'required|string|max:20',
            'quantity' => 'nullable|integer|min:0',
            'price_override' => 'nullable|numeric|min:0',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $variant = $this->variantService->createVariant($productId, $validated);
        return response()->json(['message' => 'Variant added', 'data' => $variant], 201);
    }

    public function show(int $id)
    {
        $variant = $this->variantService->getVariantById($id);
        return response()->json(['data' => $variant]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'size' => 'sometimes|string|max:20',
            'color' => 'sometimes|string|max:50',
            'stud_type' => 'sometimes|string|max:20',
            'quantity' => 'nullable|integer|min:0',
            'price_override' => 'nullable|numeric|min:0',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $variant = $this->variantService->updateVariant($id, $validated);
        return response()->json(['message' => 'Variant updated', 'data' => $variant]);
    }

    public function destroy(int $id)
    {
        $this->variantService->deleteVariant($id);
        return response()->json(['message' => 'Variant deleted']);
    }

    public function updateStock(Request $request, int $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $variant = $this->variantService->updateStock($id, $validated['quantity']);
        return response()->json(['message' => 'Stock updated', 'data' => $variant]);
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'price_adjustment' => 'nullable|string',
            'stock_adjustment' => 'nullable|string',
        ]);

        $updated = $this->variantService->bulkUpdate(
            $validated['product_id'],
            [
                'price_adjustment' => $validated['price_adjustment'] ?? null,
                'stock_adjustment' => $validated['stock_adjustment'] ?? null,
            ]
        );

        return response()->json(['message' => 'Bulk update completed', 'data' => $updated]);
    }
}