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
            gap: 1.25rem;
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
        
        @media (min-width: 1400px) {
            .product-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        .product-card {
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 0.75rem;
            padding: 1.25rem 1.25rem 1rem 1.25rem;
            cursor: pointer;
            transition: all 0.2s ease;
            background: white;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            position: relative;
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
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 140px;
            width: 100%;
        }
        
        .dark .product-image {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .product-title {
            font-weight: 600;
            color: rgb(2, 6, 23);
            margin-bottom: 0.625rem;
            font-size: 0.9375rem;
            line-height: 1.3;
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
            font-size: 1rem;
            font-weight: 700;
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
            background: rgba(0, 0, 0, 0.02);
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
        }
        
        .dark .order-totals {
            background: rgba(255, 255, 255, 0.02);
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.875rem;
            margin-bottom: 0.75rem;
            padding: 0.5rem 0;
        }
        
        .total-row:last-child {
            font-size: 1.125rem;
            font-weight: 700;
            border-top: 2px solid rgba(59, 130, 246, 0.2);
            padding-top: 0.75rem;
            margin-top: 0.5rem;
            background: rgba(59, 130, 246, 0.05);
            margin-left: -1rem;
            margin-right: -1rem;
            padding-left: 1rem;
            padding-right: 1rem;
            border-radius: 0 0 0.5rem 0.5rem;
        }
        
        .dark .total-row:last-child {
            border-color: rgba(59, 130, 246, 0.3);
            background: rgba(59, 130, 246, 0.1);
        }
        
        .total-label {
            color: rgb(100, 116, 139);
            font-weight: 500;
        }
        
        .dark .total-label {
            color: rgb(148, 163, 184);
        }
        
        .total-value {
            color: rgb(2, 6, 23);
            font-weight: 600;
        }
        
        .dark .total-value {
            color: rgb(255, 255, 255);
        }
        
        .total-row:last-child .total-label,
        .total-row:last-child .total-value {
            color: rgb(2, 6, 23);
        }
        
        .dark .total-row:last-child .total-label,
        .dark .total-row:last-child .total-value {
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
            padding: 3rem 1rem;
            background: rgba(0, 0, 0, 0.02);
            border: 2px dashed rgba(0, 0, 0, 0.1);
            border-radius: 0.75rem;
            margin: 1rem 0;
        }
        
        .dark .empty-state {
            color: rgb(148, 163, 184);
            background: rgba(255, 255, 255, 0.02);
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .empty-state::before {
            content: "🛒";
            display: block;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            opacity: 0.5;
        }
        
        .order-items {
            margin-bottom: 1.5rem;
        }
        
        .order-item {
            background: rgba(0, 0, 0, 0.02);
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
        }
        
        .dark .order-item {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .order-item:hover {
            background: rgba(0, 0, 0, 0.03);
            border-color: rgba(59, 130, 246, 0.3);
        }
        
        .dark .order-item:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .order-item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }
        
        .order-item-info {
            flex: 1;
        }
        
        .order-item-name {
            font-weight: 600;
            color: rgb(2, 6, 23);
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
        }
        
        .dark .order-item-name {
            color: rgb(255, 255, 255);
        }
        
        .order-item-sku {
            color: rgb(100, 116, 139);
            font-size: 0.75rem;
            margin-bottom: 0.125rem;
            font-family: monospace;
        }
        
        .dark .order-item-sku {
            color: rgb(148, 163, 184);
        }
        
        .order-item-category {
            color: rgb(59, 130, 246);
            font-size: 0.75rem;
            margin-bottom: 0.25rem;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 0.25rem;
            padding: 0.125rem 0.375rem;
            display: inline-block;
            font-weight: 500;
        }
        
        .dark .order-item-category {
            color: rgb(147, 197, 253);
            background: rgba(59, 130, 246, 0.2);
            border-color: rgba(59, 130, 246, 0.3);
        }
        
        .order-item-price {
            color: rgb(100, 116, 139);
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
        }
        
        .dark .order-item-price {
            color: rgb(148, 163, 184);
        }
        
        .order-item-subtotal {
            color: rgb(2, 6, 23);
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .dark .order-item-subtotal {
            color: rgb(255, 255, 255);
        }
        
        .order-item-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .order-item-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }
        
        .order-item-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 0.375rem;
            padding: 0.25rem;
        }
        
        .dark .order-item-controls {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.2);
        }
        
        .quantity-btn {
            width: 1.75rem;
            height: 1.75rem;
            border: none;
            border-radius: 0.25rem;
            background: rgb(243, 244, 246);
            color: rgb(75, 85, 99);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.15s ease;
        }
        
        .dark .quantity-btn {
            background: rgba(255, 255, 255, 0.1);
            color: rgb(203, 213, 225);
        }
        
        .quantity-btn:hover {
            background: rgb(229, 231, 235);
            color: rgb(55, 65, 81);
        }
        
        .dark .quantity-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            color: rgb(255, 255, 255);
        }
        
        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .quantity-display {
            min-width: 2.5rem;
            text-align: center;
            font-size: 0.875rem;
            font-weight: 600;
            color: rgb(2, 6, 23);
            padding: 0 0.5rem;
        }
        
        .dark .quantity-display {
            color: rgb(255, 255, 255);
        }
        
        .remove-btn {
            background: rgb(254, 242, 242);
            border: 1px solid rgb(252, 165, 165);
            border-radius: 0.375rem;
            color: rgb(239, 68, 68);
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            padding: 0.375rem 0.75rem;
            transition: all 0.15s ease;
        }
        
        .dark .remove-btn {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
            color: rgb(248, 113, 113);
        }
        
        .remove-btn:hover {
            background: rgb(239, 68, 68);
            color: white;
            border-color: rgb(220, 38, 38);
        }
        
        .dark .remove-btn:hover {
            background: rgb(239, 68, 68);
            color: white;
            border-color: rgb(220, 38, 38);
        }
        
        .order-item-total {
            font-weight: 600;
            color: rgb(2, 6, 23);
            font-size: 1rem;
            background: rgb(239, 246, 255);
            border: 1px solid rgb(147, 197, 253);
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
        }
        
        .dark .order-item-total {
            color: rgb(255, 255, 255);
            background: rgba(59, 130, 246, 0.1);
            border-color: rgba(59, 130, 246, 0.3);
        }
        
        .sticky-sidebar {
            position: sticky;
            top: 1.5rem;
        }
        
        .order-item-notes-input {
            margin-top: 0.5rem;
            margin-bottom: 0.75rem;
        }
        
        .notes-input {
            width: 100%;
            padding: 0.375rem 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 0.375rem;
            background: white;
            color: rgb(2, 6, 23);
            font-size: 0.75rem;
        }
        
        .dark .notes-input {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.2);
            color: rgb(255, 255, 255);
        }
        
        .notes-input:focus {
            outline: none;
            border-color: rgb(59, 130, 246);
            box-shadow: 0 0 0 1px rgb(59, 130, 246);
        }
        
        .order-item-notes {
            margin-top: 0.25rem;
        }
        
        .customer-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            user-select: none;
            padding: 0.1rem;
        }
        
        .customer-section-header:hover {
            opacity: 0.8;
        }
        
        .customer-section-toggle {
            transition: transform 0.2s ease;
        }
        
        .customer-section-toggle.collapsed {
            transform: rotate(-90deg);
        }
        
        .customer-section-content {
            overflow: hidden;
            transition: max-height 0.3s ease, opacity 0.3s ease;
            padding: 0.1rem;
        }
        
        .customer-section-content.collapsed {
            max-height: 0;
            opacity: 0;
        }
        
        .customer-section-content.expanded {
            max-height: 1000px;
            opacity: 1;
        }
        
        .product-stock {
            font-size: 0.75rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .product-stock.in-stock {
            color: rgb(100, 116, 139);
        }
        
        
        .product-stock.low-stock {
            color: rgb(245, 158, 11);
            font-weight: 600;
        }
        
        .dark .product-stock.in-stock {
            color: rgb(148, 163, 184);
        }
        
        
        .dark .product-stock.low-stock {
            color: rgb(251, 191, 36);
        }
    </style>
    
    <div class="orders-grid">
        {{-- Left Column: Products --}}
        <div class="order-section">
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
            
            <div class="search-bar">
                <input type="text" placeholder="🔍 Search products..." class="search-input" wire:model.live="searchTerm">
            </div>

            <div class="product-grid">
                @if(empty($products))
                    <div class="empty-state">
                        No products found matching your criteria
                    </div>
                @else
                    @foreach($products as $product)
                        @php
                            $isLowStock = $product['track_stock'] && $product['stock_quantity'] <= ($product['min_stock_level'] ?? 0);
                        @endphp
                        <div class="product-card" wire:click="addToOrder({{ $product['id'] }})">
                            <div class="product-image">
                                @if($product['image'])
                                    <img src="{{ asset('storage/' . $product['image']) }}" alt="{{ $product['name'] }}" class="w-full h-full object-cover rounded">
                                @else
                                    <svg width="56" height="56" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #9ca3af;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                @endif
                            </div>
                            <h3 class="product-title">{{ $product['name'] }}</h3>
                            @if($product['track_stock'])
                                <div class="product-stock {{ $isLowStock ? 'low-stock' : 'in-stock' }}">
                                    Stock: {{ $product['stock_quantity'] }} {{ $product['unit'] ?? 'units' }}
                                    @if($isLowStock)
                                        (Low Stock)
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
                <div class="customer-section-header" wire:click="toggleCustomerSection">
                    <div>
                        <h2>Customer Information</h2>
                    </div>
                    <svg class="customer-section-toggle {{ $customerSectionCollapsed ? 'collapsed' : '' }}" 
                         width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
                
                <div class="customer-section-content {{ $customerSectionCollapsed ? 'collapsed' : 'expanded' }}">
                    <p>Enter customer details</p>
                    <div>{{ $this->form }}</div>
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
                                <div class="order-item-header">
                                    <div class="order-item-info">
                                        <div class="order-item-name">{{ $item['name'] }}</div>
                                        @if(!empty($item['sku']))
                                            <div class="order-item-sku">SKU: {{ $item['sku'] }}</div>
                                        @endif
                                        @if(!empty($item['category']))
                                            <div class="order-item-category">{{ $item['category'] }}</div>
                                        @endif
                                        <div class="order-item-price">${{ number_format($item['price'], 2) }} × {{ $item['quantity'] }}</div>
                                        <div class="order-item-subtotal">Line Total: ${{ number_format($item['price'] * $item['quantity'], 2) }}</div>
                                    </div>
                                    <div class="order-item-actions">
                                        <button class="remove-btn" wire:click="removeFromOrder({{ $item['id'] }})">Remove</button>
                                    </div>
                                </div>
                                
                                <div class="order-item-footer">
                                    <div class="order-item-controls">
                                        <button class="quantity-btn" wire:click="updateQuantity({{ $item['id'] }}, -1)">−</button>
                                        <span class="quantity-display">{{ $item['quantity'] }}</span>
                                        <button class="quantity-btn" wire:click="updateQuantity({{ $item['id'] }}, 1)">+</button>
                                    </div>
                                    <div class="order-item-total">${{ number_format($item['price'] * $item['quantity'], 2) }}</div>
                                </div>
                                
                                <div class="order-item-notes-input">
                                    <input type="text" 
                                           placeholder="Add note for {{ $item['name'] }}..." 
                                           class="notes-input"
                                           wire:model.live="orderItems.{{ $loop->index }}.notes"
                                           wire:blur="updateItemNotes({{ $item['id'] }}, $event.target.value)">
                                </div>
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


                <div class="action-buttons">
                    <x-filament::button
                        wire:click="placeOrder"
                        :disabled="empty($orderItems) || empty($this->customerName)"
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