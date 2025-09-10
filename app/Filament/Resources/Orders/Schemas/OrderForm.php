<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Items')
                    ->schema([
                        Repeater::make('orderItems')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->label('Product')
                                    ->options(Product::active()->pluck('name', 'id'))
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if ($state) {
                                            $product = Product::find($state);
                                            if ($product) {
                                                $set('product_name', $product->name);
                                                $set('product_sku', $product->sku);
                                                $set('unit_price', $product->price);
                                                // Calculate total price with current quantity
                                                $quantity = $get('quantity') ?? 1;
                                                $set('total_price', $product->price * $quantity);
                                            }
                                        }
                                    }),
                                TextInput::make('product_name')
                                    ->label('Product Name')
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('product_sku')
                                    ->label('SKU')
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $unitPrice = $get('unit_price') ?? 0;
                                        $set('total_price', $unitPrice * ($state ?? 1));
                                    }),
                                TextInput::make('unit_price')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('total_price')
                                    ->label('Total Price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(),
                                Textarea::make('notes')
                                    ->label('Item Notes')
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addAction(
                                fn (Action $action) => $action->label('Add Product')
                            )
                            ->deleteAction(
                                fn (Action $action) => $action->label('Remove Product')
                            )
                            ->reorderAction(
                                fn (Action $action) => $action->label('Reorder Products')
                            )
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateTotals($get, $set);
                            }),
                    ]),

                Section::make('Order Summary')
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('$')
                            ->default(0.0)
                            ->readOnly(),
                        TextInput::make('tax_amount')
                            ->label('Tax Amount')
                            ->numeric()
                            ->prefix('$')
                            ->default(0.0)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateTotals($get, $set);
                            }),
                        TextInput::make('discount_amount')
                            ->label('Discount Amount')
                            ->numeric()
                            ->prefix('$')
                            ->default(0.0)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateTotals($get, $set);
                            }),
                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('$')
                            ->default(0.0)
                            ->readOnly(),
                        Select::make('status')
                            ->label('Order Status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Customer Information')
                    ->schema([
                        TextInput::make('customer_name')
                            ->label('Customer Name'),
                        TextInput::make('customer_email')
                            ->label('Customer Email')
                            ->email(),
                        TextInput::make('customer_phone')
                            ->label('Customer Phone')
                            ->tel(),
                        Textarea::make('notes')
                            ->label('Order Notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        // Calculate subtotal from all order items
        $orderItems = $get('orderItems') ?? [];
        $subtotal = 0;

        foreach ($orderItems as $item) {
            if (isset($item['unit_price']) && isset($item['quantity'])) {
                $subtotal += floatval($item['unit_price']) * intval($item['quantity']);
            }
        }

        // Get tax and discount amounts
        $taxAmount = floatval($get('tax_amount') ?? 0);
        $discountAmount = floatval($get('discount_amount') ?? 0);

        // Calculate total
        $totalAmount = $subtotal + $taxAmount - $discountAmount;

        // Update the form fields
        $set('subtotal', number_format($subtotal, 2, '.', ''));
        $set('total_amount', number_format($totalAmount, 2, '.', ''));
    }
}
