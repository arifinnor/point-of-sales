<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo tenants
        $tenants = [
            [
                'code' => 'TNT001',
                'name' => 'Main Store Jakarta',
                'timezone' => 'Asia/Jakarta',
                'settings' => [
                    'allow_negative_stock' => false,
                    'cash_rounding' => 100,
                    'price_includes_tax' => true,
                    'default_tax_rate' => 11.0,
                ],
            ],
            [
                'code' => 'TNT002',
                'name' => 'Branch Store Bandung',
                'timezone' => 'Asia/Jakarta',
                'settings' => [
                    'allow_negative_stock' => false,
                    'cash_rounding' => 100,
                    'price_includes_tax' => true,
                    'default_tax_rate' => 11.0,
                ],
            ],
            [
                'code' => 'TNT003',
                'name' => 'Outlet Store Surabaya',
                'timezone' => 'Asia/Jakarta',
                'settings' => [
                    'allow_negative_stock' => false,
                    'cash_rounding' => 100,
                    'price_includes_tax' => true,
                    'default_tax_rate' => 11.0,
                ],
            ],
        ];

        foreach ($tenants as $tenantData) {
            Tenant::create($tenantData);
        }

        $this->command->info('Created '.count($tenants).' demo tenants.');
    }
}
