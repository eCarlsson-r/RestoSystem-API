<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Recipe;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController
{
    private $notificationService;

    public function __construct(NotificationService $notificationService) {
        $this->notificationService = $notificationService;
    }
    
    public function index(Request $request)
    {
        $query = Product::with(['category']);
        
        if (!$request->has('all')) {
            $query->where('soldout', 0);
        }
        
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

    public function toggleSoldOut(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->soldout = $request->soldout;
        $product->save();

        $this->notificationService->notifyProductStatusChanged($product->id, $product->soldout);

        return response()->json([
            'err' => 0,
            'msg' => 'Product status updated',
            'data' => $product
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
                $request->only(['name', 'category_id', 'price', 'cost', 'soldout'])
            );

            // 2. Clear existing recipes
            $product->recipes()->delete();

            // 3. Insert new recipe rows
            if ($request->has('recipe')) {
                foreach ($request->recipe as $ingr) {
                    if (isset($ingr['ingredient_id']) && (float)$ingr['quantity'] > 0) {
                        $product->recipes()->create([
                            'item_type' => $ingr['item_type'] ?? 'INGR',
                            'item_code' => $ingr['ingredient_id'],
                            'qty' => $ingr['quantity'],
                            'purchase_price' => $ingr['purchase_price'] ?? 0
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
        $recipes = Recipe::with(['item'])
            ->where('product_id', $productCode)
            ->get()
            ->map(function($r) {
                // Ensure frontend compatibility
                $r->ingredient_id = $r->item_code;
                $r->quantity = $r->qty;
                $r->unit = $r->item->unit ?? 'pcs';
                return $r;
            });

        return response()->json([
            'err' => 0,
            'msg' => '',
            'data' => $recipes
        ]);
    }
}
