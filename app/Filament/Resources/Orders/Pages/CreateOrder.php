<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['cashier_id'] = auth()->id();

        // Calculate subtotal from order items
        $subtotal = 0;
        if (isset($data['orderItems']) && is_array($data['orderItems'])) {
            foreach ($data['orderItems'] as $item) {
                $subtotal += ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 1);
            }
        }

        $data['subtotal'] = $subtotal;
        $data['total_amount'] = $subtotal + ($data['tax_amount'] ?? 0) - ($data['discount_amount'] ?? 0);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Recalculate totals after order items are created
        $this->record->calculateTotals();
    }
}
