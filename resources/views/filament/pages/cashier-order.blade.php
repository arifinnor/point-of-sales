<x-filament-panels::page>
    <style>
        .orders-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        @media (min-width: 1024px) {
            .orders-grid {
                grid-template-columns: 2fr 1fr;
            }
        }
        
        .order-section {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
        }
        
        .dark .order-section {
            background: rgb(15, 23, 42);
            border-color: rgba(255, 255, 255, 0.1);
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.3), 0 1px 2px 0 rgba(0, 0, 0, 0.2);
        }
        
        .order-section h2 {
            font-size: 1.125rem;
            font-weight: 600;
            color: rgb(2, 6, 23);
            margin-bottom: 1rem;
        }
        
        .dark .order-section h2 {
            color: rgb(255, 255, 255);
        }
        
        .order-section p {
            color: rgb(100, 116, 139);
            margin-bottom: 1rem;
        }
        
        .dark .order-section p {
            color: rgb(148, 163, 184);
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        @media (min-width: 640px) {
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (min-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        .product-card {
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 0.75rem;
            padding: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
            background: white;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
        
        .product-card:hover {
            border-color: rgb(59, 130, 246);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .dark .product-card {
            background: rgb(15, 23, 42);
            border-color: rgba(255, 255, 255, 0.1);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.3), 0 1px 2px 0 rgba(0, 0, 0, 0.2);
        }
        
        .dark .product-card:hover {
            border-color: rgb(59, 130, 246);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
        }
        
        .product-image {
            aspect-ratio: 1;
            background: rgb(248, 250, 252);
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .dark .product-image {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .product-title {
            font-weight: 500;
            color: rgb(2, 6, 23);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        
        .dark .product-title {
            color: rgb(255, 255, 255);
        }
        
        
        .product-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }
        
        .product-price {
            font-size: 1.125rem;
            font-weight: 600;
            color: rgb(2, 6, 23);
        }
        
        .dark .product-price {
            color: rgb(255, 255, 255);
        }
        
        .search-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .search-input {
            flex: 1;
            padding: 0.5rem 0.75rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            background: white;
            color: rgb(2, 6, 23);
        }
        
        .dark .search-input {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.2);
            color: rgb(255, 255, 255);
        }
        
        .category-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        .category-tab {
            padding: 0.5rem 1rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            background: white;
            color: rgb(100, 116, 139);
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .category-tab:hover {
            background: rgba(0, 0, 0, 0.05);
            color: rgb(2, 6, 23);
        }
        
        .category-tab.active {
            background: rgb(59, 130, 246);
            color: white;
            border-color: rgb(59, 130, 246);
        }
        
        .dark .category-tab {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.2);
            color: rgb(148, 163, 184);
        }
        
        .dark .category-tab:hover {
            background: rgba(255, 255, 255, 0.1);
            color: rgb(255, 255, 255);
        }
        
        .dark .category-tab.active {
            background: rgb(59, 130, 246);
            color: white;
            border-color: rgb(59, 130, 246);
        }
        
        .order-totals {
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding-top: 1rem;
        }
        
        .dark .order-totals {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.875rem;
            margin-bottom: 0.75rem;
        }
        
        .total-row:last-child {
            font-size: 1.125rem;
            font-weight: 600;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding-top: 0.75rem;
        }
        
        .dark .total-row:last-child {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .total-label {
            color: rgb(100, 116, 139);
        }
        
        .dark .total-label {
            color: rgb(148, 163, 184);
        }
        
        .total-value {
            color: rgb(2, 6, 23);
        }
        
        .dark .total-value {
            color: rgb(255, 255, 255);
        }
        
        .action-buttons {
            margin-top: 1.5rem;
        }
        
        .w-full {
            width: 100%;
        }
        
        .mb-3 {
            margin-bottom: 0.75rem;
        }
        
        .notes-section {
            margin-top: 1.5rem;
        }
        
        .notes-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: rgb(100, 116, 139);
            margin-bottom: 0.5rem;
        }
        
        .dark .notes-label {
            color: rgb(148, 163, 184);
        }
        
        .notes-textarea {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            resize: none;
            background: white;
            color: rgb(2, 6, 23);
        }
        
        .dark .notes-textarea {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.2);
            color: rgb(255, 255, 255);
        }
        
        .empty-state {
            width: 100%;
            text-align: center;
            color: rgb(100, 116, 139);
            font-size: 0.875rem;
            padding: 2rem 0;
        }
        
        .dark .empty-state {
            color: rgb(148, 163, 184);
        }
        
        .order-items {
            margin-bottom: 1.5rem;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .dark .order-item {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-item-info {
            flex: 1;
        }
        
        .order-item-name {
            font-weight: 500;
            color: rgb(2, 6, 23);
            font-size: 0.875rem;
        }
        
        .dark .order-item-name {
            color: rgb(255, 255, 255);
        }
        
        .order-item-price {
            color: rgb(100, 116, 139);
            font-size: 0.75rem;
        }
        
        .dark .order-item-price {
            color: rgb(148, 163, 184);
        }
        
        .order-item-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quantity-btn {
            width: 1.5rem;
            height: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 0.25rem;
            background: white;
            color: rgb(100, 116, 139);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }
        
        .dark .quantity-btn {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.2);
            color: rgb(148, 163, 184);
        }
        
        .quantity-btn:hover {
            background: rgba(0, 0, 0, 0.05);
        }
        
        .dark .quantity-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .quantity-display {
            min-width: 2rem;
            text-align: center;
            font-size: 0.875rem;
            font-weight: 500;
            color: rgb(2, 6, 23);
        }
        
        .dark .quantity-display {
            color: rgb(255, 255, 255);
        }
        
        .remove-btn {
            background: none;
            border: none;
            color: rgb(239, 68, 68);
            font-size: 0.75rem;
            cursor: pointer;
            padding: 0.25rem;
        }
        
        .remove-btn:hover {
            color: rgb(220, 38, 38);
        }
        
        .order-item-total {
            font-weight: 500;
            color: rgb(2, 6, 23);
            font-size: 0.875rem;
        }
        
        .dark .order-item-total {
            color: rgb(255, 255, 255);
        }
        
        .sticky-sidebar {
            position: sticky;
            top: 1.5rem;
        }
    </style>
    
    <div class="orders-grid">
        {{-- Left Column: Products --}}
        <div class="order-section">
            <h2>Products</h2>
            <p>Select products to add to your order</p>
            
            <div class="search-bar">
                <input type="text" placeholder="Search products..." class="search-input" wire:model.live="searchTerm">
            </div>
            
            <div class="category-tabs">
                <button class="category-tab {{ empty($selectedCategory) ? 'active' : '' }}" 
                        wire:click="selectCategory('')">
                    All Categories
                </button>
                @foreach($categories as $category)
                    <button class="category-tab {{ $selectedCategory == $category['id'] ? 'active' : '' }}" 
                            wire:click="selectCategory('{{ $category['id'] }}')">
                        {{ $category['name'] }}
                    </button>
                @endforeach
            </div>

            <div class="product-grid">
                @if(empty($products))
                    <div class="empty-state">
                        No products found matching your criteria
                    </div>
                @else
                    @foreach($products as $product)
                        <div class="product-card {{ $product['stock_quantity'] <= 0 ? 'opacity-50' : '' }}" 
                             wire:click="addToOrder({{ $product['id'] }})"
                             @if($product['stock_quantity'] <= 0) style="cursor: not-allowed;" @endif>
                            <div class="product-image">
                                @if($product['image'])
                                    <img src="{{ asset('storage/' . $product['image']) }}" alt="{{ $product['name'] }}" class="w-full h-full object-cover rounded">
                                @else
                                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #9ca3af;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                @endif
                            </div>
                            <h3 class="product-title">{{ $product['name'] }}</h3>
                            @if($product['track_stock'])
                                <div class="text-xs text-gray-500 mb-1">
                                    Stock: {{ $product['stock_quantity'] }} {{ $product['unit'] ?? 'units' }}
                                    @if($product['stock_quantity'] <= 0)
                                        <span class="text-red-500 font-semibold">(Out of Stock)</span>
                                    @elseif($product['stock_quantity'] <= $product['min_stock_level'])
                                        <span class="text-yellow-500 font-semibold">(Low Stock)</span>
                                    @endif
                                </div>
                            @endif
                            <div class="product-footer">
                                <span class="product-price">${{ number_format($product['price'], 2) }}</span>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- Right Column: Customer & Order Summary --}}
        <div class="sticky-sidebar">
            {{-- Customer Information Section --}}
            <div class="order-section mb-3">
                <h2>Customer Information</h2>
                <p>Enter customer details</p>
                
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        <span>Name</span>
                        <span class="text-red-500 ml-1">*</span>
                    </label>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            placeholder="Customer name"
                            wire:model.live="customerName"
                            required
                        />
                    </x-filament::input.wrapper>
                </div>
                
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Email
                    </label>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="email"
                            placeholder="customer@example.com"
                            wire:model.live="customerEmail"
                        />
                    </x-filament::input.wrapper>
                </div>
                
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Phone
                    </label>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="tel"
                            placeholder="Phone number"
                            wire:model.live="customerPhone"
                        />
                    </x-filament::input.wrapper>
                </div>
            </div>

            {{-- Order Summary Section --}}
            <div class="order-section">
                <h2>Order Summary</h2>
                <p>Review your order details</p>
                
                <div class="order-items">
                    @if(empty($orderItems))
                        <div class="empty-state">
                            No items in order yet
                        </div>
                    @else
                        @foreach($orderItems as $item)
                            <div class="order-item">
                                <div class="order-item-info">
                                    <div class="order-item-name">{{ $item['name'] }}</div>
                                    <div class="order-item-price">${{ number_format($item['price'], 2) }} each</div>
                                </div>
                                <div class="order-item-controls">
                                    <button class="quantity-btn" wire:click="updateQuantity({{ $item['id'] }}, -1)">-</button>
                                    <span class="quantity-display">{{ $item['quantity'] }}</span>
                                    <button class="quantity-btn" wire:click="updateQuantity({{ $item['id'] }}, 1)">+</button>
                                    <button class="remove-btn" wire:click="removeFromOrder({{ $item['id'] }})">Remove</button>
                                </div>
                                <div class="order-item-total">${{ number_format($item['price'] * $item['quantity'], 2) }}</div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="order-totals">
                    <div class="total-row">
                        <span class="total-label">Subtotal</span>
                        <span class="total-value">${{ number_format($orderTotal, 2) }}</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">Tax</span>
                        <span class="total-value">${{ number_format($tax, 2) }}</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">Total</span>
                        <span class="total-value">${{ number_format($grandTotal, 2) }}</span>
                    </div>
                </div>

                <div class="notes-section">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Order Notes
                    </label>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="textarea"
                            rows="3"
                            placeholder="Add any special instructions..."
                            wire:model.live="notes"
                        />
                    </x-filament::input.wrapper>
                </div>

                <div class="action-buttons">
                    <x-filament::button
                        wire:click="placeOrder"
                        :disabled="empty($orderItems) || empty($customerName)"
                        color="primary"
                        size="lg"
                        class="w-full mb-3"
                    >
                        Place Order
                    </x-filament::button>
                    
                    <x-filament::button
                        wire:click="saveDraft"
                        :disabled="empty($orderItems)"
                        color="gray"
                        size="lg"
                        class="w-full"
                    >
                        Save as Draft
                    </x-filament::button>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>