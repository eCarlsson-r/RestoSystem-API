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
        $query = Product::with(['category'])->where('category_id', $request->category)->where('soldout', 0);
        
        /*if ($request->has('branch')) {
            $query->where('soldout', 0); // Simplified branch logic for now
        }*/

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

    public function store(Request $request)
    {
        $productCode = $request->input('product-code');
        $categoryId = $request->input('category_id');

        $product = Product::updateOrCreate(
            ['id' => $productCode],
            [
                'name' => $request->input('name'),
                'desc' => $request->input('description'),
                'category_id' => $categoryId,
                'price' => $request->input('price'),
                'cost' => $request->input('cost'),
                'discount' => $request->input('discount')
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
