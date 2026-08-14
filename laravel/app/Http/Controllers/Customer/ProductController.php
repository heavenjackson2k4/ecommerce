<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('status', 'active');

        if ($request->has('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        if ($request->has('price')) {
            $range = explode('-', $request->price);
            if (count($range) == 2) {
                $query->whereBetween('base_price', [$range[0], $range[1]]);
            } elseif (str_ends_with($request->price, '+')) {
                $min = (int) rtrim($request->price, '+');
                $query->where('base_price', '>=', $min);
            }
        }

        if ($request->has('sizes')) {
            $sizes = explode(',', $request->sizes);
            $query->whereHas('variants', function ($q) use ($sizes) {
                $q->whereIn('size', $sizes);
            });
        }

        if ($request->has('stud_types')) {
            $studTypes = explode(',', $request->stud_types);
            $query->whereHas('shoesVariants', function ($q) use ($studTypes) {
                $q->whereIn('stud_type', $studTypes);
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_asc': $query->orderBy('base_price', 'asc'); break;
            case 'price_desc': $query->orderBy('base_price', 'desc'); break;
            default: $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12);
        $categories = Category::where('status', 'active')->get();
        $selectedCategory = $request->category;

        return view('customer.products.index', compact('products', 'categories', 'selectedCategory'));
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->where('status', 'active')
            ->with(['category', 'shoesVariants', 'clothesVariants'])
            ->firstOrFail();

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->limit(4)
            ->get();

        return view('customer.products.show', compact('product', 'relatedProducts'));
    }
}