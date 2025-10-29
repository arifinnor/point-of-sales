<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\Register;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tenant 1: Warung Makan Sederhana (Restaurant)
        $tenant1 = Tenant::factory()->create([
            'code' => 'WMSD',
            'name' => 'Warung Makan Sederhana',
            'timezone' => 'Asia/Jakarta',
            'settings' => [
                'default_mode' => 'restaurant',
                'multi_currency' => false,
                'receipt_footer' => 'Terima kasih atas kunjungan Anda',
                'allow_negative_stock' => false,
                'cash_rounding' => 100,
                'price_includes_tax' => true,
                'default_tax_rate' => 11.0,
            ],
        ]);

        // Outlet 1.1: Cabang Sudirman
        $outlet1_1 = Outlet::factory()->restaurant()->create([
            'tenant_id' => $tenant1->id,
            'code' => 'OUT001',
            'name' => 'Cabang Sudirman',
            'address' => 'Jl. Jenderal Sudirman No. 123, Jakarta Pusat, DKI Jakarta 10220',
            'settings' => [
                'table_count' => 20,
                'kitchen_display' => true,
                'service_charge_percent' => 10,
            ],
        ]);

        // Registers for Outlet 1.1
        Register::factory()->create([
            'outlet_id' => $outlet1_1->id,
            'name' => 'Kasir Utama',
            'printer_profile_id' => 'EPSON-TM-T82',
            'settings' => [
                'receipt_printer' => 'EPSON-TM-T82',
                'cash_drawer' => true,
                'card_reader' => true,
            ],
        ]);

        Register::factory()->create([
            'outlet_id' => $outlet1_1->id,
            'name' => 'Kasir 2',
            'printer_profile_id' => 'EPSON-TM-T82',
            'settings' => [
                'receipt_printer' => 'EPSON-TM-T82',
                'cash_drawer' => true,
                'card_reader' => false,
            ],
        ]);

        // Outlet 1.2: Cabang Thamrin
        $outlet1_2 = Outlet::factory()->restaurant()->create([
            'tenant_id' => $tenant1->id,
            'code' => 'OUT002',
            'name' => 'Cabang Thamrin',
            'address' => 'Jl. M.H. Thamrin Kav. 28-30, Jakarta Pusat, DKI Jakarta 10350',
            'settings' => [
                'table_count' => 15,
                'kitchen_display' => true,
                'service_charge_percent' => 10,
            ],
        ]);

        // Registers for Outlet 1.2
        Register::factory()->create([
            'outlet_id' => $outlet1_2->id,
            'name' => 'Kasir Utama',
            'printer_profile_id' => 'EPSON-TM-T82',
            'settings' => [
                'receipt_printer' => 'EPSON-TM-T82',
                'cash_drawer' => true,
                'card_reader' => true,
            ],
        ]);

        // Tenant 2: Minimarket Segar (Minimarket)
        $tenant2 = Tenant::factory()->create([
            'code' => 'MSGS',
            'name' => 'Minimarket Segar',
            'timezone' => 'Asia/Jakarta',
            'settings' => [
                'default_mode' => 'minimarket',
                'multi_currency' => false,
                'receipt_footer' => 'Terima kasih telah berbelanja',
                'allow_negative_stock' => false,
                'cash_rounding' => 100,
                'price_includes_tax' => true,
                'default_tax_rate' => 11.0,
            ],
        ]);

        // Outlet 2.1: Outlet Gatot Subroto
        $outlet2_1 = Outlet::factory()->minimarket()->create([
            'tenant_id' => $tenant2->id,
            'code' => 'OUT001',
            'name' => 'Outlet Gatot Subroto',
            'address' => 'Jl. Gatot Subroto No. 45, Jakarta Selatan, DKI Jakarta 12930',
            'settings' => [
                'barcode_scanner' => true,
                'weight_scale_integration' => true,
                'express_lane_count' => 2,
            ],
        ]);

        // Registers for Outlet 2.1
        Register::factory()->create([
            'outlet_id' => $outlet2_1->id,
            'name' => 'Kasir Express 1',
            'printer_profile_id' => 'EPSON-TM-T20',
            'settings' => [
                'receipt_printer' => 'EPSON-TM-T20',
                'cash_drawer' => true,
                'card_reader' => true,
                'express_lane' => true,
            ],
        ]);

        Register::factory()->create([
            'outlet_id' => $outlet2_1->id,
            'name' => 'Kasir Express 2',
            'printer_profile_id' => 'EPSON-TM-T20',
            'settings' => [
                'receipt_printer' => 'EPSON-TM-T20',
                'cash_drawer' => true,
                'card_reader' => true,
                'express_lane' => true,
            ],
        ]);

        // Outlet 2.2: Outlet Kuningan
        $outlet2_2 = Outlet::factory()->minimarket()->create([
            'tenant_id' => $tenant2->id,
            'code' => 'OUT002',
            'name' => 'Outlet Kuningan',
            'address' => 'Jl. HR Rasuna Said, Kuningan, Jakarta Selatan, DKI Jakarta 12940',
            'settings' => [
                'barcode_scanner' => true,
                'weight_scale_integration' => true,
                'express_lane_count' => 1,
            ],
        ]);

        // Registers for Outlet 2.2
        Register::factory()->create([
            'outlet_id' => $outlet2_2->id,
            'name' => 'Kasir Utama',
            'printer_profile_id' => 'EPSON-TM-T20',
            'settings' => [
                'receipt_printer' => 'EPSON-TM-T20',
                'cash_drawer' => true,
                'card_reader' => true,
            ],
        ]);

        // Tenant 3: Toko Elektronik Jaya (POS)
        $tenant3 = Tenant::factory()->create([
            'code' => 'TEJY',
            'name' => 'Toko Elektronik Jaya',
            'timezone' => 'Asia/Jakarta',
            'settings' => [
                'default_mode' => 'pos',
                'multi_currency' => false,
                'receipt_footer' => 'Terima kasih atas kepercayaan Anda',
                'allow_negative_stock' => false,
                'cash_rounding' => 100,
                'price_includes_tax' => true,
                'default_tax_rate' => 11.0,
            ],
        ]);

        // Outlet 3.1: Toko Pusat
        $outlet3_1 = Outlet::factory()->pos()->create([
            'tenant_id' => $tenant3->id,
            'code' => 'OUT001',
            'name' => 'Toko Pusat',
            'address' => 'Jl. Jenderal Sudirman No. 123, Jakarta Pusat, DKI Jakarta 10220',
            'settings' => [
                'counter_count' => 3,
                'warranty_tracking' => true,
                'installment_support' => true,
            ],
        ]);

        // Registers for Outlet 3.1
        Register::factory()->create([
            'outlet_id' => $outlet3_1->id,
            'name' => 'Counter 1',
            'printer_profile_id' => 'EPSON-TM-T82',
            'settings' => [
                'receipt_printer' => 'EPSON-TM-T82',
                'cash_drawer' => true,
                'card_reader' => true,
                'warranty_printer' => true,
            ],
        ]);

        Register::factory()->create([
            'outlet_id' => $outlet3_1->id,
            'name' => 'Counter 2',
            'printer_profile_id' => 'EPSON-TM-T82',
            'settings' => [
                'receipt_printer' => 'EPSON-TM-T82',
                'cash_drawer' => true,
                'card_reader' => true,
                'warranty_printer' => true,
            ],
        ]);

        Register::factory()->create([
            'outlet_id' => $outlet3_1->id,
            'name' => 'Counter 3',
            'printer_profile_id' => 'EPSON-TM-T82',
            'settings' => [
                'receipt_printer' => 'EPSON-TM-T82',
                'cash_drawer' => true,
                'card_reader' => true,
                'warranty_printer' => true,
            ],
        ]);

        // Outlet 3.2: Cabang Bandung
        $outlet3_2 = Outlet::factory()->pos()->create([
            'tenant_id' => $tenant3->id,
            'code' => 'OUT002',
            'name' => 'Cabang Bandung',
            'address' => 'Jl. Merdeka No. 56, Bandung, Jawa Barat 40111',
            'settings' => [
                'counter_count' => 1,
                'warranty_tracking' => true,
                'installment_support' => true,
            ],
        ]);

        // Registers for Outlet 3.2
        Register::factory()->create([
            'outlet_id' => $outlet3_2->id,
            'name' => 'Counter 1',
            'printer_profile_id' => 'EPSON-TM-T82',
            'settings' => [
                'receipt_printer' => 'EPSON-TM-T82',
                'cash_drawer' => true,
                'card_reader' => true,
                'warranty_printer' => true,
            ],
        ]);

        // Tenant 4: Cafe Kopi Nusantara (Restaurant)
        $tenant4 = Tenant::factory()->create([
            'code' => 'CKN',
            'name' => 'Cafe Kopi Nusantara',
            'timezone' => 'Asia/Jakarta',
            'settings' => [
                'default_mode' => 'restaurant',
                'multi_currency' => false,
                'receipt_footer' => 'Selamat menikmati kopi terbaik',
                'allow_negative_stock' => false,
                'cash_rounding' => 100,
                'price_includes_tax' => true,
                'default_tax_rate' => 11.0,
            ],
        ]);

        // Outlet 4.1: Cafe Menteng
        $outlet4_1 = Outlet::factory()->restaurant()->create([
            'tenant_id' => $tenant4->id,
            'code' => 'OUT001',
            'name' => 'Cafe Menteng',
            'address' => 'Jl. Menteng Raya No. 12, Jakarta Pusat, DKI Jakarta 10310',
            'settings' => [
                'table_count' => 12,
                'kitchen_display' => true,
                'service_charge_percent' => 5,
                'coffee_machine_integration' => true,
            ],
        ]);

        // Registers for Outlet 4.1
        Register::factory()->create([
            'outlet_id' => $outlet4_1->id,
            'name' => 'Kasir Counter',
            'printer_profile_id' => 'EPSON-TM-T82',
            'settings' => [
                'receipt_printer' => 'EPSON-TM-T82',
                'cash_drawer' => true,
                'card_reader' => true,
                'coffee_order_display' => true,
            ],
        ]);

        // Outlet 4.2: Cafe Senopati
        $outlet4_2 = Outlet::factory()->restaurant()->create([
            'tenant_id' => $tenant4->id,
            'code' => 'OUT002',
            'name' => 'Cafe Senopati',
            'address' => 'Jl. Senopati No. 88, Jakarta Selatan, DKI Jakarta 12190',
            'settings' => [
                'table_count' => 18,
                'kitchen_display' => true,
                'service_charge_percent' => 5,
                'coffee_machine_integration' => true,
                'drive_thru' => true,
            ],
        ]);

        // Registers for Outlet 4.2
        Register::factory()->create([
            'outlet_id' => $outlet4_2->id,
            'name' => 'Kasir Drive-Thru',
            'printer_profile_id' => 'EPSON-TM-T82',
            'settings' => [
                'receipt_printer' => 'EPSON-TM-T82',
                'cash_drawer' => true,
                'card_reader' => true,
                'drive_thru_mode' => true,
            ],
        ]);

        Register::factory()->create([
            'outlet_id' => $outlet4_2->id,
            'name' => 'Kasir Indoor',
            'printer_profile_id' => 'EPSON-TM-T82',
            'settings' => [
                'receipt_printer' => 'EPSON-TM-T82',
                'cash_drawer' => true,
                'card_reader' => true,
                'coffee_order_display' => true,
            ],
        ]);

        $this->command->info('Created 4 tenants with 8 outlets and 12 registers using factories.');
    }
}
