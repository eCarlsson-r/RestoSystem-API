<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'name' => 'Nasi Putih',
                'description' => 'Fragrant steamed rice',
                'category_id' => 2,
                'price' => 5000,
                'cost' => 600,
                'discount' => 0,
                'is_featured' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Nasi Goreng Special',
                'description' => 'Signature fried rice with egg',
                'category_id' => 2,
                'price' => 25000,
                'cost' => 8000,
                'discount' => 0,
                'is_featured' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mie Goreng Jawa',
                'description' => 'Classic Javanese fried noodles',
                'category_id' => 1,
                'price' => 22000,
                'cost' => 7000,
                'discount' => 0,
                'is_featured' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mie Ayam Jamur',
                'description' => 'Chicken and mushroom noodles',
                'category_id' => 1,
                'price' => 28000,
                'cost' => 9000,
                'discount' => 0,
                'is_featured' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Wagyu Beef Slice',
                'description' => 'Premium tender wagyu slices',
                'category_id' => 3,
                'price' => 45000,
                'cost' => 25000,
                'discount' => 0,
                'is_featured' => true,
                'is_featured' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Australian Beef',
                'description' => 'High quality Australian beef',
                'category_id' => 3,
                'price' => 35000,
                'cost' => 18000,
                'discount' => 0,
                'is_featured' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Chicken Teriyaki',
                'description' => 'Succulent chicken with teriyaki sauce',
                'category_id' => 3,
                'price' => 20000,
                'cost' => 10000,
                'discount' => 0,
                'is_featured' => true,
                'is_featured' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Es Teh Manis',
                'description' => 'Refreshing sweet iced tea',
                'category_id' => 4,
                'price' => 8000,
                'cost' => 1000,
                'discount' => 0,
                'is_featured' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lemon Tea',
                'description' => 'Iced tea with a twist of lemon',
                'category_id' => 4,
                'price' => 12000,
                'cost' => 2000,
                'discount' => 0,
                'is_featured' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kentang Goreng',
                'description' => 'Crispy golden french fries',
                'category_id' => 5,
                'price' => 15000,
                'cost' => 5000,
                'discount' => 0,
                'is_featured' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dimsum Combination',
                'description' => 'Assorted steamed dimsum',
                'category_id' => 5,
                'price' => 25000,
                'cost' => 10000,
                'discount' => 0,
                'is_featured' => true,
                'is_featured' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('branch_product')->insert([
            [
                'branch_id' => 1,
                'product_id' => 1,
                'is_active' => true
            ],
            [
                'branch_id' => 1,
                'product_id' => 2,
                'is_active' => true
            ],
            [
                'branch_id' => 1,
                'product_id' => 3,
                'is_active' => true
            ],
            [
                'branch_id' => 1,
                'product_id' => 4,
                'is_active' => true
            ],
            [
                'branch_id' => 1,
                'product_id' => 5,
                'is_active' => true
            ],
            [
                'branch_id' => 1,
                'product_id' => 6,
                'is_active' => true
            ],
            [
                'branch_id' => 1,
                'product_id' => 7,
                'is_active' => true
            ],
            [
                'branch_id' => 1,
                'product_id' => 8,
                'is_active' => true
            ],
            [
                'branch_id' => 1,
                'product_id' => 9,
                'is_active' => true
            ],
            [
                'branch_id' => 1,
                'product_id' => 10,
                'is_active' => true
            ],
            [
                'branch_id' => 1,
                'product_id' => 11,
                'is_active' => true
            ],
        ]);
    }
}
