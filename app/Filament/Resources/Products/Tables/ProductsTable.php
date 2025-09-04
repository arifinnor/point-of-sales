<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextColumn as BadgeColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl('/images/product-placeholder.png'),
                TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->sortable()
                    ->color(fn ($record) => match (true) {
                        $record->isOutOfStock() => 'danger',
                        $record->isLowStock() => 'warning',
                        default => 'success',
                    }),
                BadgeColumn::make('stock_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->isOutOfStock() => 'danger',
                        $record->isLowStock() => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(fn ($record) => match (true) {
                        $record->isOutOfStock() => 'Out of Stock',
                        $record->isLowStock() => 'Low Stock',
                        default => 'In Stock',
                    }),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('active')
                    ->label('Active Products')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)),
                Filter::make('inactive')
                    ->label('Inactive Products')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', false)),
                Filter::make('low_stock')
                    ->label('Low Stock')
                    ->query(fn (Builder $query): Builder => $query->whereColumn('stock_quantity', '<=', 'min_stock_level')),
                Filter::make('out_of_stock')
                    ->label('Out of Stock')
                    ->query(fn (Builder $query): Builder => $query->where('stock_quantity', '<=', 0)),
                Filter::make('track_stock')
                    ->label('Track Stock')
                    ->query(fn (Builder $query): Builder => $query->where('track_stock', true)),
            ])
            ->recordActions([
                ViewAction::make()
                    ->visible(fn (): bool => auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'manager'])),
                EditAction::make()
                    ->visible(fn (): bool => auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'manager'])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->check() && auth()->user()->hasRole('super_admin')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
