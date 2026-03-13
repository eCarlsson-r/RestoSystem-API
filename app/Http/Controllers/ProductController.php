<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController
{
    public function index(Request $request)
    {
        $query = Product::with(['category'])->where('soldout', 0);
        
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->has('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $products = $query->get();

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $products
        ]);
    }

    public function show($id)
    {
        $product = Product::with(['category'])->findOrFail($id)->first();

        if (!$product) {
            return response()->json(['err' => 1, 'msg' => 'product-not-exist']);
        }

        if ($product->soldout) {
            return response()->json(['err' => 1, 'msg' => 'product-soldout']);
        }

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => [$product]
        ]);
    }

    public function store(Request $request) {
        return DB::transaction(function() use ($request) {
            // 1. Save or Update the Main Item
            $product = Product::updateOrCreate(
                ['id' => $request->id],
                $request->only(['name', 'category_id', 'price', 'soldout'])
            );

            // 2. Clear existing recipe
            $product->ingredients()->delete();

            // 3. Insert new recipe rows
            if ($request->has('recipe')) {
                foreach ($request->recipe as $ingr) {
                    if ($ingr['ingredient_id'] && $ingr['qty'] > 0) {
                        $product->ingredients()->create([
                            'ingredient_code' => $ingr['ingredient_id'],
                            'quantity' => $ingr['qty'],
                            'unit' => $ingr['unit']
                        ]);
                    }
                }
            }

            return response()->json(['status' => 'success', 'data' => $product]);
        });
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        if ($product) {
            $product->update(['category_id' => null]); // Legacy "remove" just unsets category
            return response()->json(['err' => 0, 'msg' => 'Product removed']);
        }
        return response()->json(['err' => 1, 'msg' => 'Product not found']);
    }

    public function getRecipe($productCode)
    {
        $recipes = Recipe::with('ingredient')
            ->where('product_id', $productCode)
            ->get();

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $recipes
        ]);
    }
}
