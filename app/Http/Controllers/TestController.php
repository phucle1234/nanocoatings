<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test()
    {
        $products = Product::all();
        return response()->json([
            'count' => $products->count(),
            'products' => $products->toArray()
        ]);
    }
}
