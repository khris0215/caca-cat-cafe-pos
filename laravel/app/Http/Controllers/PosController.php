<?php

namespace App\Http\Controllers;


use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;


class PosController extends Controller
{
    public function index()
    {
        $categories = Category::with('products')->get();
        $products = Product::with('category')->where('is_available', true)->get();

        return inertia('Dashboard', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }
}