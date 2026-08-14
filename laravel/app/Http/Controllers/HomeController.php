<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $newProducts = Product::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        $popularProducts = Product::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        $categories = Category::where('status', 'active')->get();

        return view('customer.home', compact('newProducts', 'popularProducts', 'categories'));
    }
}