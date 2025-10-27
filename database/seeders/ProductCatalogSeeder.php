<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class ProductCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            // Set tenant context
            App::instance('current_tenant', $tenant);

            $this->seedProductCatalogForTenant($tenant);
        }
    }

    private function seedProductCatalogForTenant(Tenant $tenant): void
    {
        // Create main product categories
        $categories = $this->createProductCategories($tenant);

        // Create comprehensive product catalog
        $products = $this->getProductCatalogData();

        foreach ($products as $productData) {
            $category = $categories[$productData['category_key']] ?? null;

            $product = Product::firstOrCreate(
                ['tenant_id' => $tenant->id, 'sku' => $productData['sku']],
                [
                    'category_id' => $category?->id,
                    'name' => $productData['name'],
                    'price_incl' => $productData['price'],
                    'tax_rate' => $productData['tax_rate'],
                    'status' => 'active',
                    'description' => $productData['description'] ?? null,
                ]
            );

            foreach ($productData['variants'] as $idx => $variantData) {
                $variantCode = $tenant->code.'-'.$productData['sku'].'-VAR-'.str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT);
                $barcode = $this->generateBarcode($tenant->code, $variantData['barcode']);

                $variant = ProductVariant::firstOrCreate(
                    ['product_id' => $product->id, 'code' => $variantCode],
                    [
                        'barcode' => $barcode,
                        'name' => $variantData['name'],
                        'price_override_incl' => $variantData['price'],
                    ]
                );

                // Create inventory for each outlet
                $outlets = Outlet::where('tenant_id', $tenant->id)->get();
                foreach ($outlets as $outlet) {
                    Inventory::firstOrCreate(
                        ['tenant_id' => $tenant->id, 'variant_id' => $variant->id, 'outlet_id' => $outlet->id],
                        [
                            'on_hand' => $this->generateStockLevel($variantData['name']),
                            'safety_stock' => $this->generateSafetyStock($variantData['name']),
                        ]
                    );
                }
            }
        }
    }

    private function createProductCategories(Tenant $tenant): array
    {
        // Main categories
        $beverages = ProductCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'BEV001'],
            [
                'name' => 'Minuman',
                'status' => 'active',
            ]
        );

        $snacks = ProductCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'SNK001'],
            [
                'name' => 'Camilan',
                'status' => 'active',
            ]
        );

        $food = ProductCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'FOD001'],
            [
                'name' => 'Makanan',
                'status' => 'active',
            ]
        );

        $personalCare = ProductCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'PRC001'],
            [
                'name' => 'Perawatan Diri',
                'status' => 'active',
            ]
        );

        $household = ProductCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'HST001'],
            [
                'name' => 'Kebutuhan Rumah Tangga',
                'status' => 'active',
            ]
        );

        // Subcategories
        $softDrinks = ProductCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'BEV-SD'],
            [
                'parent_id' => $beverages->id,
                'name' => 'Minuman Ringan',
                'status' => 'active',
            ]
        );

        $water = ProductCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'BEV-WTR'],
            [
                'parent_id' => $beverages->id,
                'name' => 'Air Mineral',
                'status' => 'active',
            ]
        );

        $teaCoffee = ProductCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'BEV-TC'],
            [
                'parent_id' => $beverages->id,
                'name' => 'Teh & Kopi',
                'status' => 'active',
            ]
        );

        $instantNoodles = ProductCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'FOD-MI'],
            [
                'parent_id' => $food->id,
                'name' => 'Mie Instan',
                'status' => 'active',
            ]
        );

        $cookies = ProductCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'SNK-BSK'],
            [
                'parent_id' => $snacks->id,
                'name' => 'Biskuit',
                'status' => 'active',
            ]
        );

        $chips = ProductCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'SNK-CHP'],
            [
                'parent_id' => $snacks->id,
                'name' => 'Keripik',
                'status' => 'active',
            ]
        );

        return [
            'soft_drinks' => $softDrinks,
            'water' => $water,
            'tea_coffee' => $teaCoffee,
            'instant_noodles' => $instantNoodles,
            'cookies' => $cookies,
            'chips' => $chips,
            'personal_care' => $personalCare,
            'household' => $household,
        ];
    }

    private function getProductCatalogData(): array
    {
        return [
            // Soft Drinks
            [
                'category_key' => 'soft_drinks',
                'name' => 'Coca Cola',
                'sku' => 'SKU-COKE',
                'price' => 6500,
                'tax_rate' => 11.00,
                'description' => 'Minuman berkarbonasi rasa cola',
                'variants' => [
                    ['name' => '330ml', 'barcode' => '8991001234567', 'price' => 6500],
                    ['name' => '600ml', 'barcode' => '8991001234574', 'price' => 9500],
                    ['name' => '1.5L', 'barcode' => '8991001234581', 'price' => 14500],
                ],
            ],
            [
                'category_key' => 'soft_drinks',
                'name' => 'Sprite',
                'sku' => 'SKU-SPRITE',
                'price' => 6500,
                'tax_rate' => 11.00,
                'description' => 'Minuman berkarbonasi rasa lemon-lime',
                'variants' => [
                    ['name' => '330ml', 'barcode' => '8991002234567', 'price' => null],
                    ['name' => '600ml', 'barcode' => '8991002234574', 'price' => 9500],
                ],
            ],
            [
                'category_key' => 'soft_drinks',
                'name' => 'Fanta',
                'sku' => 'SKU-FANTA',
                'price' => 6500,
                'tax_rate' => 11.00,
                'description' => 'Minuman berkarbonasi rasa jeruk',
                'variants' => [
                    ['name' => '330ml', 'barcode' => '8991003234568', 'price' => null],
                    ['name' => '600ml', 'barcode' => '8991003234575', 'price' => 9500],
                ],
            ],

            // Water
            [
                'category_key' => 'water',
                'name' => 'Aqua',
                'sku' => 'SKU-AQUA',
                'price' => 3500,
                'tax_rate' => 11.00,
                'description' => 'Air mineral kemasan',
                'variants' => [
                    ['name' => '330ml', 'barcode' => '8991004234567', 'price' => null],
                    ['name' => '600ml', 'barcode' => '8991004234574', 'price' => 4500],
                    ['name' => '1.5L', 'barcode' => '8991004234581', 'price' => 8500],
                ],
            ],
            [
                'category_key' => 'water',
                'name' => 'Le Minerale',
                'sku' => 'SKU-LEMIN',
                'price' => 3500,
                'tax_rate' => 11.00,
                'description' => 'Air mineral kemasan',
                'variants' => [
                    ['name' => '600ml', 'barcode' => '8991005234568', 'price' => null],
                    ['name' => '1.5L', 'barcode' => '8991005234575', 'price' => 8500],
                ],
            ],

            // Tea & Coffee
            [
                'category_key' => 'tea_coffee',
                'name' => 'Teh Botol Sosro',
                'sku' => 'SKU-TEH-BTL',
                'price' => 4500,
                'tax_rate' => 11.00,
                'description' => 'Teh botol siap minum',
                'variants' => [
                    ['name' => '350ml', 'barcode' => '8991006234568', 'price' => null],
                    ['name' => '500ml', 'barcode' => '8991006234574', 'price' => 6000],
                ],
            ],
            [
                'category_key' => 'tea_coffee',
                'name' => 'Good Day Cappuccino',
                'sku' => 'SKU-GDCAP',
                'price' => 3500,
                'tax_rate' => 11.00,
                'description' => 'Minuman kopi siap minum',
                'variants' => [
                    ['name' => '250ml', 'barcode' => '8991007234568', 'price' => null],
                ],
            ],
            [
                'category_key' => 'tea_coffee',
                'name' => 'Kopi ABC Susu',
                'sku' => 'SKU-KOPI-ABC',
                'price' => 2500,
                'tax_rate' => 11.00,
                'description' => 'Kopi instan dengan susu',
                'variants' => [
                    ['name' => '20g', 'barcode' => '8991008234568', 'price' => null],
                ],
            ],

            // Instant Noodles
            [
                'category_key' => 'instant_noodles',
                'name' => 'Indomie Goreng',
                'sku' => 'SKU-INDO-GOR',
                'price' => 3500,
                'tax_rate' => 11.00,
                'description' => 'Mie instan rasa goreng',
                'variants' => [
                    ['name' => 'Regular', 'barcode' => '8991009234568', 'price' => null],
                ],
            ],
            [
                'category_key' => 'instant_noodles',
                'name' => 'Indomie Soto',
                'sku' => 'SKU-INDO-SOT',
                'price' => 3500,
                'tax_rate' => 11.00,
                'description' => 'Mie instan rasa soto',
                'variants' => [
                    ['name' => 'Regular', 'barcode' => '8991010234567', 'price' => null],
                ],
            ],
            [
                'category_key' => 'instant_noodles',
                'name' => 'Mie Sedap Goreng',
                'sku' => 'SKU-SEDAP-GOR',
                'price' => 3500,
                'tax_rate' => 11.00,
                'description' => 'Mie instan rasa goreng',
                'variants' => [
                    ['name' => 'Regular', 'barcode' => '8991011234567', 'price' => null],
                ],
            ],

            // Cookies
            [
                'category_key' => 'cookies',
                'name' => 'Oreo',
                'sku' => 'SKU-OREO',
                'price' => 8500,
                'tax_rate' => 11.00,
                'description' => 'Biskuit coklat dengan krim',
                'variants' => [
                    ['name' => 'Original', 'barcode' => '8991012234567', 'price' => null],
                    ['name' => 'Vanilla', 'barcode' => '8991012234574', 'price' => null],
                ],
            ],
            [
                'category_key' => 'cookies',
                'name' => 'Biskuat',
                'sku' => 'SKU-BISKUAT',
                'price' => 7500,
                'tax_rate' => 11.00,
                'description' => 'Biskuit dengan susu',
                'variants' => [
                    ['name' => 'Susu', 'barcode' => '8991013234567', 'price' => null],
                ],
            ],

            // Chips
            [
                'category_key' => 'chips',
                'name' => 'Chitato',
                'sku' => 'SKU-CHITATO',
                'price' => 12500,
                'tax_rate' => 11.00,
                'description' => 'Keripik kentang',
                'variants' => [
                    ['name' => 'Original', 'barcode' => '8991014234567', 'price' => null],
                    ['name' => 'BBQ', 'barcode' => '8991014234574', 'price' => null],
                    ['name' => 'Cheese', 'barcode' => '8991014234581', 'price' => null],
                ],
            ],
            [
                'category_key' => 'chips',
                'name' => 'Tango',
                'sku' => 'SKU-TANGO',
                'price' => 10000,
                'tax_rate' => 11.00,
                'description' => 'Wafer coklat',
                'variants' => [
                    ['name' => 'Coklat', 'barcode' => '8991015234567', 'price' => null],
                    ['name' => 'Vanilla', 'barcode' => '8991015234574', 'price' => null],
                ],
            ],

            // Personal Care
            [
                'category_key' => 'personal_care',
                'name' => 'Lifebuoy Sabun',
                'sku' => 'SKU-LIFEBUOY',
                'price' => 8500,
                'tax_rate' => 11.00,
                'description' => 'Sabun mandi antibakteri',
                'variants' => [
                    ['name' => '90g', 'barcode' => '8991016234567', 'price' => null],
                ],
            ],
            [
                'category_key' => 'personal_care',
                'name' => 'Pepsodent Pasta Gigi',
                'sku' => 'SKU-PEPSODENT',
                'price' => 12000,
                'tax_rate' => 11.00,
                'description' => 'Pasta gigi dengan fluoride',
                'variants' => [
                    ['name' => '75g', 'barcode' => '8991017234567', 'price' => null],
                ],
            ],

            // Household
            [
                'category_key' => 'household',
                'name' => 'Sunlight Sabun Cuci',
                'sku' => 'SKU-SUNLIGHT',
                'price' => 15000,
                'tax_rate' => 11.00,
                'description' => 'Sabun cuci piring',
                'variants' => [
                    ['name' => '500ml', 'barcode' => '8991018234567', 'price' => null],
                ],
            ],
            [
                'category_key' => 'household',
                'name' => 'Rinso Detergen',
                'sku' => 'SKU-RINSO',
                'price' => 18000,
                'tax_rate' => 11.00,
                'description' => 'Detergen bubuk',
                'variants' => [
                    ['name' => '400g', 'barcode' => '8991019234567', 'price' => null],
                ],
            ],
        ];
    }

    private function generateBarcode(string $tenantCode, string $baseBarcode): string
    {
        // Prefix barcode with tenant code for multi-tenancy
        return $tenantCode.'-'.$baseBarcode;
    }

    private function generateStockLevel(string $variantName): int
    {
        // Generate realistic stock levels based on variant type
        if (str_contains(strtolower($variantName), 'ml') || str_contains(strtolower($variantName), 'l')) {
            // Beverages - higher stock
            return rand(20, 150);
        } elseif (str_contains(strtolower($variantName), 'g')) {
            // Small items - medium stock
            return rand(15, 80);
        } else {
            // Regular items
            return rand(10, 100);
        }
    }

    private function generateSafetyStock(string $variantName): int
    {
        // Generate safety stock based on variant type
        if (str_contains(strtolower($variantName), 'ml') || str_contains(strtolower($variantName), 'l')) {
            // Beverages - higher safety stock
            return rand(5, 20);
        } elseif (str_contains(strtolower($variantName), 'g')) {
            // Small items - medium safety stock
            return rand(3, 15);
        } else {
            // Regular items
            return rand(2, 10);
        }
    }
}
