<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            BranchSeeder::class,
            CategorySeeder::class,
            CustomerSeeder::class,
            EmployeeSeeder::class,
            ProductSeeder::class,
            TableSeeder::class,
            SaleSeeder::class,
            SaleInvoiceSeeder::class,
            SupplierSeeder::class,
            IngredientSeeder::class,
            UtilitySeeder::class,
            PackageSeeder::class,
            PrepareSeeder::class,
            RecipeSeeder::class,
            StockSeeder::class,
            PurchaseSeeder::class,
            MovementSeeder::class,
            KitchenSeeder::class,
        ]);
    }
}
