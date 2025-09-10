<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class CashierOrder extends Page
{
    protected string $view = 'filament.pages.cashier-order';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    public $orderItems = [];

    public $orderTotal = 0;

    public $tax = 0;

    public $grandTotal = 0;

    public $notes = '';

    // Customer information
    public $customerName = '';

    public $customerEmail = '';

    public $customerPhone = '';

    // Filter properties
    public $searchTerm = '';

    public $selectedCategory = '';

    public $products = [];

    public $categories = [];

    public function mount()
    {
        $this->loadCategories();
        $this->loadProducts();
        $this->updateTotals();
    }

    public function loadCategories()
    {
        $this->categories = ProductCategory::active()
            ->ordered()
            ->get()
            ->toArray();
    }

    public function loadProducts()
    {
        $query = Product::active()
            ->with('category');

        // Apply search filter
        if (! empty($this->searchTerm)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->searchTerm.'%')
                    ->orWhere('sku', 'like', '%'.$this->searchTerm.'%');
            });
        }

        // Apply category filter
        if (! empty($this->selectedCategory)) {
            $query->where('product_category_id', $this->selectedCategory);
        }

        $this->products = $query->orderBy('name')->get()->toArray();
    }

    public function updatedSearchTerm()
    {
        $this->loadProducts();
    }

    public function updatedSelectedCategory()
    {
        $this->loadProducts();
    }

    public function selectCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;
        $this->loadProducts();
    }

    public function addToOrder($productId)
    {
        $product = collect($this->products)->firstWhere('id', $productId);

        if (! $product) {
            return;
        }

        // Check if product is out of stock
        if ($product['track_stock'] && $product['stock_quantity'] <= 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Cannot add '.$product['name'].' - out of stock',
            ]);

            return;
        }

        $existingItem = collect($this->orderItems)->firstWhere('id', $productId);

        if ($existingItem) {
            // Check if adding one more would exceed stock
            if ($product['track_stock'] && ($existingItem['quantity'] + 1) > $product['stock_quantity']) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Cannot add more '.$product['name'].' - insufficient stock',
                ]);

                return;
            }

            $this->orderItems = collect($this->orderItems)->map(function ($item) use ($productId) {
                if ($item['id'] === $productId) {
                    $item['quantity'] += 1;
                }

                return $item;
            })->toArray();
        } else {
            $this->orderItems[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => 1,
            ];
        }

        $this->updateTotals();
    }

    public function removeFromOrder($productId)
    {
        $this->orderItems = collect($this->orderItems)->reject(function ($item) use ($productId) {
            return $item['id'] === $productId;
        })->values()->toArray();

        $this->updateTotals();
    }

    public function updateQuantity($productId, $change)
    {
        $this->orderItems = collect($this->orderItems)->map(function ($item) use ($productId, $change) {
            if ($item['id'] === $productId) {
                $item['quantity'] += $change;
                if ($item['quantity'] <= 0) {
                    return null; // Will be filtered out
                }
            }

            return $item;
        })->filter()->values()->toArray();

        $this->updateTotals();
    }

    public function updateTotals()
    {
        $this->orderTotal = collect($this->orderItems)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        $this->tax = $this->orderTotal * 0.1; // 10% tax
        $this->grandTotal = $this->orderTotal + $this->tax;
    }

    public function placeOrder()
    {
        if (empty($this->orderItems)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Cannot place order: No items in cart',
            ]);

            return;
        }

        // Validate customer information
        if (empty($this->customerName)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Customer name is required',
            ]);

            return;
        }

        // Validate stock availability
        $stockValidation = $this->validateStockAvailability();
        if (! $stockValidation['valid']) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $stockValidation['message'],
            ]);

            return;
        }

        try {
            DB::transaction(function () {
                // Create the order
                $order = Order::create([
                    'cashier_id' => auth()->id(),
                    'customer_name' => $this->customerName,
                    'customer_email' => $this->customerEmail,
                    'customer_phone' => $this->customerPhone,
                    'notes' => $this->notes,
                    'subtotal' => $this->orderTotal,
                    'tax_amount' => $this->tax,
                    'discount_amount' => 0,
                    'total_amount' => $this->grandTotal,
                    'status' => 'completed',
                ]);

                // Create order items and update stock
                foreach ($this->orderItems as $item) {
                    $product = Product::find($item['id']);

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'unit_price' => $product->price,
                        'quantity' => $item['quantity'],
                        'total_price' => $product->price * $item['quantity'],
                    ]);

                    // Update stock if tracking is enabled
                    if ($product->track_stock) {
                        $product->decrement('stock_quantity', $item['quantity']);
                    }
                }
            });

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Order #{$order->order_number} placed successfully! Total: $".number_format($this->grandTotal, 2),
            ]);

            // Reset the order
            $this->resetOrder();

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to place order. Please try again.',
            ]);
        }
    }

    public function saveDraft()
    {
        if (empty($this->orderItems)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Cannot save draft: No items in cart',
            ]);

            return;
        }

        try {
            DB::transaction(function () {
                // Create the order as pending
                $order = Order::create([
                    'cashier_id' => auth()->id(),
                    'customer_name' => $this->customerName,
                    'customer_email' => $this->customerEmail,
                    'customer_phone' => $this->customerPhone,
                    'notes' => $this->notes,
                    'subtotal' => $this->orderTotal,
                    'tax_amount' => $this->tax,
                    'discount_amount' => 0,
                    'total_amount' => $this->grandTotal,
                    'status' => 'pending',
                ]);

                // Create order items without updating stock
                foreach ($this->orderItems as $item) {
                    $product = Product::find($item['id']);

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'unit_price' => $product->price,
                        'quantity' => $item['quantity'],
                        'total_price' => $product->price * $item['quantity'],
                    ]);
                }
            });

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Order #{$order->order_number} saved as draft!",
            ]);

            // Reset the order
            $this->resetOrder();

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to save draft. Please try again.',
            ]);
        }
    }

    private function validateStockAvailability(): array
    {
        foreach ($this->orderItems as $item) {
            $product = Product::find($item['id']);

            if (! $product) {
                return [
                    'valid' => false,
                    'message' => "Product {$item['name']} not found",
                ];
            }

            if ($product->track_stock && $product->stock_quantity < $item['quantity']) {
                return [
                    'valid' => false,
                    'message' => "Insufficient stock for {$product->name}. Available: {$product->stock_quantity}, Required: {$item['quantity']}",
                ];
            }
        }

        return ['valid' => true];
    }

    private function resetOrder(): void
    {
        $this->orderItems = [];
        $this->customerName = '';
        $this->customerEmail = '';
        $this->customerPhone = '';
        $this->notes = '';
        $this->updateTotals();
    }
}
