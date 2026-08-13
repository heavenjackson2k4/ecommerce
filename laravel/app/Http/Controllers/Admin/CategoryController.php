<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        $categories = $this->categoryService->getAllCategories();
        return response()->json(['data' => $categories]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:categories,slug',
            'description' => 'nullable|string',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $category = $this->categoryService->createCategory($validated);
        return response()->json(['message' => 'Category created.', 'data' => $category], 201);
    }

    public function show($id)
    {
        $category = $this->categoryService->getCategoryById($id);
        return response()->json(['data' => $category]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
            'name' => 'sometimes|string|max:100',
            'slug' => 'sometimes|string|max:100|unique:categories,slug,' . $id,
            'description' => 'nullable|string',
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);

        $category = $this->categoryService->updateCategory($id, $validated);
        return response()->json(['message' => 'Category updated.', 'data' => $category]);
    }

    public function destroy($id)
    {
        $this->categoryService->deleteCategory($id);
        return response()->json(['message' => 'Category deleted.']);
    }
}