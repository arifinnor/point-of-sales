<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'manager']);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (): bool => auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'manager'])),
        ];
    }
}
