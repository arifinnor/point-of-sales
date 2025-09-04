<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the categories
        $foodsCategory = ProductCategory::where('slug', 'foods')->first();
        $drinksCategory = ProductCategory::where('slug', 'drinks')->first();

        $products = [
            [
                'name' => 'Fried Rice',
                'description' => 'Delicious fried rice with vegetables and choice of protein',
                'sku' => 'FR-001',
                'barcode' => '1234567890123',
                'price' => 8.99,
                'cost_price' => 4.50,
                'stock_quantity' => 50,
                'min_stock_level' => 10,
                'unit' => 'plate',
                'is_active' => true,
                'track_stock' => true,
                'product_category_id' => $foodsCategory->id,
            ],
            [
                'name' => 'Coffee',
                'description' => 'Freshly brewed coffee made from premium beans',
                'sku' => 'CF-001',
                'barcode' => '1234567890124',
                'price' => 3.50,
                'cost_price' => 1.20,
                'stock_quantity' => 100,
                'min_stock_level' => 20,
                'unit' => 'cup',
                'is_active' => true,
                'track_stock' => true,
                'product_category_id' => $drinksCategory->id,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
