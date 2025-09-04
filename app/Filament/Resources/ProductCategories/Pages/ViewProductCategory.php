<?php

namespace App\Filament\Resources\ProductCategories\Pages;

use App\Filament\Resources\ProductCategories\ProductCategoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProductCategory extends ViewRecord
{
    protected static string $resource = ProductCategoryResource::class;

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
