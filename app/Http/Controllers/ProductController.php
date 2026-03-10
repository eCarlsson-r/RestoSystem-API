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
        $query = Product::with(['category', 'file']);
        
        if ($request->has('branch')) {
            $query->where('soldout', 0); // Simplified branch logic for now
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
        $product = Product::with(['category'])->where('code', $id)->first();

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

    public function store(Request $request)
    {
        $productCode = $request->input('product-code');
        $categoryId = $request->input('category_id');
        
        // Logical code prefixing from legacy
        $finalCode = $categoryId . str_pad($productCode, 3, '0', STR_PAD_LEFT);

        $product = Product::updateOrCreate(
            ['code' => $finalCode],
            [
                'name' => $request->input('product-name'),
                'desc' => $request->input('product-desc'),
                'category_id' => $categoryId,
                'price' => $request->input('product-price'),
                'cost' => $request->input('product-cost'),
                'discount' => $request->input('product-discount'),
                'img_no' => $request->input('product-img-no', 0)
            ]
        );

        if ($request->has('recipe-items')) {
            $items = json_decode($request->input('recipe-items'), true);
            foreach ($items as $item) {
                Recipe::updateOrCreate(
                    [
                        'product_id' => $product->code,
                        'ingredient_id' => $item['ingredient_id']
                    ],
                    [
                        'qty' => $item['recipe-qty'],
                        'purchase_price' => $item['purchase-price']
                    ]
                );
            }
        }

        return response()->json([
            'err' => 0,
            'msg' => 'Product saved successfully',
            'data' => $product->code
        ]);
    }

    public function destroy($id)
    {
        $product = Product::where('code', $id)->first();
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
