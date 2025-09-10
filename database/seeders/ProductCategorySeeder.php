<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Foods',
                'description' => 'Various food items and meals',
                'slug' => 'foods',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Drinks',
                'description' => 'Beverages and liquid refreshments',
                'slug' => 'drinks',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Others',
                'description' => 'Other products',
                'slug' => 'others',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($categories as $category) {
            ProductCategory::create($category);
        }
    }
}
