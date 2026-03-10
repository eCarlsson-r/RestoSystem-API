<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class StockService
{
    public function deductStockForProduct($productCode, $qtyOrdered) {
        // 1. Get Product Recipe (e.g., Nasi Soto)
        $recipes = DB::table('recipe')->where('product-code', $productCode)->get();

        foreach ($recipes as $recipe) {
            // 2. Check if the item in the recipe is a Prepared Item
            $prepareItem = DB::table('prepare')->where('precipe-code', $recipe->{'ingr-code'})->first();

            if ($prepareItem) {
                // EXPLode Prepare Recipe: e.g., Nasi uses Beras
                $subIngredients = DB::table('prepare-recipe')
                    ->where('prepare-code', $prepareItem->{'precipe-code'})
                    ->get();

                foreach ($subIngredients as $sub) {
                    // Calculate actual raw usage: (Order Qty * Prepare Ratio)
                    $rawUsage = ($qtyOrdered * $recipe->{'recipe-qty'}) * ($sub->{'ingr-qty'} / $prepareItem->{'precipe-qty'});
                    
                    $this->decrementStock($sub->{'ingr-code'}, $rawUsage);
                }
            } else {
                // Direct Raw Ingredient (e.g., Garnishes)
                $this->decrementStock($recipe->{'ingr-code'}, $qtyOrdered * $recipe->{'recipe-qty'});
            }
        }
    }
}