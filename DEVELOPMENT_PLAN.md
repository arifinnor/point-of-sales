# POS System Development Plan

> **Project:** Multi-tenant Point of Sale System with Restaurant & Minimarket Modes  
> **Stack:** Laravel 12 + Inertia.js v2 + Vue 3 + Tailwind CSS v4  
> **Status:** In Progress

## Overview

This document tracks the implementation progress of a production-ready POS system with strict ACID compliance, row-level multi-tenancy, and configurable vertical modes (restaurant, minimarket).

### Key Requirements
- **Performance Targets:** Read TTFB ≤ 300ms, Sale commit ≤ 800ms at 200 rps
- **Consistency:** All sale flows are ACID with row-level locks on inventory
- **Timezone:** Asia/Jakarta, Currency: IDR (tax-inclusive pricing)
- **Security:** Tenant isolation, audit trail, idempotent APIs
- **Laravel Way:** Always do the Laravel Way: (prefer php artisan command instead manually created file)

---

## Phase 1: Foundation & Infrastructure

### 1.1 Multi-Tenancy Foundation
- [ ] Create tenant middleware for context enforcement
- [ ] Implement tenant-scoped query builder trait
- [ ] Add tenant context to all cache keys, queues, and events
- [ ] Create tenant switcher for SuperAdmin role
- [ ] Add tenant column to all business entity migrations
- [ ] Create composite unique indexes (tenant_id + business_key)

**Acceptance Criteria:**
- No cross-tenant data leakage in queries
- Same SKU/barcode allowed across tenants but unique within tenant
- SuperAdmin can switch tenant context safely

---

### 1.2 Core Database Schema

#### 1.2.1 Tenants, Outlets & Registers
- [ ] Create `tenants` migration (id, code, name, timezone, settings jsonb)
- [ ] Create `outlets` migration (tenant_id, code, name, address, mode)
- [ ] Create `registers` migration (outlet_id, name, printer_profile_id)
- [ ] Add tenant_id foreign keys and indexes
- [ ] Seed sample tenant with outlets and registers

**Files:**
```
database/migrations/2025_xx_xx_create_tenants_table.php
database/migrations/2025_xx_xx_create_outlets_table.php
database/migrations/2025_xx_xx_create_registers_table.php
```

---

#### 1.2.2 Product Catalog
- [ ] Create `product_categories` migration (tenant_id, parent_id, name, code, status)
- [ ] Create `products` migration (tenant_id, sku, name, category_id, tax_rate, price_incl, status)
- [ ] Create `product_variants` migration (product_id, code, name, barcode, price_override_incl)
- [ ] Add unique constraints: (tenant_id + sku), (tenant_id + barcode)
- [ ] Create nested set or closure table for category hierarchy (if needed)

**Acceptance Criteria:**
- Variant barcode/code lookups are fast (indexed)
- Price override logic works correctly (variant → product fallback)
- Archived products excluded from active sales

---

#### 1.2.3 Inventory
- [ ] Create `inventory` migration (tenant_id, variant_id, outlet_id, on_hand, safety_stock)
- [ ] Add unique constraint on (tenant_id + variant_id + outlet_id)
- [ ] Ensure `on_hand` is integer, indexed for fast lookups
- [ ] Add check constraint or application logic to prevent negative stock

**Acceptance Criteria:**
- Row-level locking (`SELECT ... FOR UPDATE`) prevents overselling
- Concurrent sales do not decrement below zero when `allow_negative_stock=false`

---

#### 1.2.4 Shifts
- [ ] Create `shifts` migration (tenant_id, register_id, opened_by, opened_at, opening_float, closed_by, closed_at, expected_cash, actual_cash, status)
- [ ] Add constraint: only one open shift per register at a time
- [ ] Index on (tenant_id + register_id + status) for fast lookups

**Acceptance Criteria:**
- Cannot create sale if shift is not open (403 SHIFT_NOT_OPEN)
- Closing shift calculates expected cash from payments
- Cash variance (expected vs actual) is captured

---

#### 1.2.5 Sales & Payments
- [ ] Create `sales` migration (tenant_id, outlet_id, register_id, shift_id, receipt_no, customer_id, subtotal_excl, tax_amount, discount_amount, total_due, paid_total, change_due, status, channel)
- [ ] Create `sale_items` migration (sale_id, variant_id, qty, unit_price_incl, discount, tax_rate, line_excl, line_tax, line_incl)
- [ ] Create `payments` migration (sale_id, method, amount, reference)
- [ ] Add unique constraint on (tenant_id + receipt_no)
- [ ] Receipt number format: `OUTLET/REG-YYYYMMDD-####`

**Acceptance Criteria:**
- Tax calculations match MDC formula (tax-inclusive)
- Split payments allowed: sum(payments.amount) ≥ total_due
- Change only given for cash payments

---

#### 1.2.6 Stock Moves & Returns
- [ ] Create `stock_moves` migration (tenant_id, variant_id, outlet_id, qty, reason, ref_type, ref_id, moved_at)
- [ ] Create `returns` migration (tenant_id, sale_id, policy, reason, total_refund, method)
- [ ] Index on (tenant_id + ref_type + ref_id) for audit trail queries

**Acceptance Criteria:**
- Every sale creates compensating stock_moves
- Returns restore inventory with correct stock moves
- Void transactions reverse stock without reusing receipt numbers

---

## Phase 2: Models & Business Logic

### 2.1 Eloquent Models
- [ ] Create Tenant model with HasMany relationships
- [ ] Create Outlet model with mode configuration
- [ ] Create Register model
- [ ] Create ProductCategory model with nested set or self-referencing parent
- [ ] Create Product model with variants relationship
- [ ] Create ProductVariant model with inventory relationship
- [ ] Create Inventory model with tenant + outlet scopes
- [ ] Create Shift model with open/close methods
- [ ] Create Sale model with items and payments relationships
- [ ] Create SaleItem model with tax calculation methods
- [ ] Create Payment model with validation rules
- [ ] Create StockMove model
- [ ] Create Return model
- [ ] Add tenant-scoped global scope to all models
- [ ] Add `casts()` method for money fields (decimal 18,2)

**Acceptance Criteria:**
- All models enforce tenant scope automatically
- Relationships use eager loading to prevent N+1
- Money fields cast correctly

---

### 2.2 Factories & Seeders
- [ ] Create TenantFactory
- [ ] Create OutletFactory with modes (restaurant, minimarket, pos)
- [ ] Create RegisterFactory
- [ ] Create ProductCategoryFactory with nested categories
- [ ] Create ProductFactory with realistic tax rates
- [ ] Create ProductVariantFactory with barcodes
- [ ] Create InventoryFactory
- [ ] Create ShiftFactory (open and closed states)
- [ ] Create SaleFactory with complete sale data
- [ ] Create SaleItemFactory
- [ ] Create PaymentFactory
- [ ] Create comprehensive DatabaseSeeder for demo data
- [ ] Create tenant-specific seeder for isolated testing

**Acceptance Criteria:**
- Factories generate valid, tenant-scoped data
- Seeders create realistic demo scenarios
- Tests can use factories for all models

---

## Phase 3: RBAC & Security

### 3.1 Role-Based Access Control
- [ ] Define Cashier role and permissions (create sale, read products, open/close shift, create return ≤ Rp1,000,000)
- [ ] Define Supervisor role (+ approve discounts, stock adjustments ≤ ±5)
- [ ] Define Admin role (full access within tenant)
- [ ] Define SuperAdmin role (cross-tenant with explicit context switch)
- [ ] Create permissions seeder
- [ ] Add role checks to policies
- [ ] Create POSPolicy with tenant-aware gates

**Acceptance Criteria:**
- Cashier cannot approve large returns
- Supervisor can approve discounts
- SuperAdmin must assume tenant before data operations

---

### 3.2 Security & Audit
- [ ] Implement audit trail for all write operations (old→new, actor, reason, timestamp)
- [ ] Add Idempotency-Key middleware for state-changing endpoints
- [ ] Never log PII or card PAN
- [ ] Rate limit write endpoints
- [ ] Add CSRF protection to all web forms
- [ ] Implement tenant-namespaced idempotency keys

**Acceptance Criteria:**
- All mutations are auditable
- Duplicate requests with same idempotency key return cached response
- No sensitive data in logs

---

## Phase 4: Core Features

### 4.1 Product Management
- [ ] Build product category CRUD (create, read, update, archive)
- [ ] Build product CRUD with variant management
- [ ] Build variant CRUD with barcode generation/scanning
- [ ] Implement product search (by name, SKU, barcode)
- [ ] Build product import (CSV/Excel with idempotent SKU matching)
- [ ] Build product export
- [ ] Add validation for duplicate SKU/barcode within tenant

**UI Components:**
- ProductCategoryList.vue, ProductCategoryForm.vue
- ProductList.vue, ProductForm.vue
- VariantForm.vue, BarcodeScanner.vue

**Acceptance Criteria:**
- Barcode lookup priority: variant.barcode → product.sku → variant.code
- Import rejects duplicates with precise error messages
- Archived products not visible in POS

---

### 4.2 Inventory Management
- [ ] Build inventory list with low-stock alerts (on_hand ≤ safety_stock)
- [ ] Build stock adjustment form with reason and supervisor approval
- [ ] Build stock transfer between outlets
- [ ] Build stock receive workflow
- [ ] Implement inventory reports (by outlet, by product, by category)
- [ ] Add real-time stock level updates via websockets (tenant-namespaced)

**Acceptance Criteria:**
- Adjustments create stock_moves with ref_type='adjustment'
- Transfers decrement source, increment destination atomically
- Low stock alerts trigger notifications

---

### 4.3 Shift Management
- [ ] Build shift open screen (select register, enter opening float)
- [ ] Build shift dashboard (current sales, cash/card totals, time elapsed)
- [ ] Build shift close screen (enter actual cash, calculate variance)
- [ ] Build shift history/reports
- [ ] Prevent sales when no shift is open (403 error)
- [ ] Handle stale shifts (auto-close after 24h with warning)

**UI Components:**
- ShiftOpen.vue, ShiftDashboard.vue, ShiftClose.vue

**Acceptance Criteria:**
- Only one open shift per register
- Cash variance calculated as (actual_cash - expected_cash)
- All sales linked to shift_id

---

### 4.4 POS Sale Flow
- [ ] Build POS screen layout (product search, cart, payment panel)
- [ ] Implement barcode scanner integration
- [ ] Build cart management (add, update qty, remove items)
- [ ] Implement line-level discounts
- [ ] Implement order-level discounts
- [ ] Calculate tax per line (tax-inclusive formula from MDC)
- [ ] Display running totals (subtotal_excl, tax_amount, total_due)
- [ ] Build payment modal (cash, card, ewallet, transfer)
- [ ] Implement split payments (multiple payment methods)
- [ ] Calculate cash change and rounding (nearest Rp100 for cash)
- [ ] Commit sale in DB transaction with row-level locks on inventory
- [ ] Decrement inventory per item atomically
- [ ] Generate receipt_no with format OUTLET/REG-YYYYMMDD-####
- [ ] Create stock_moves for each item

**UI Components:**
- POSScreen.vue, ProductSearch.vue, Cart.vue, PaymentModal.vue

**Acceptance Criteria:**
- Concurrent sales do not oversell (tested with row locks)
- Cash rounding to nearest Rp100
- Tax math matches MDC formula
- Sale commit ≤ 800ms at 200 rps burst

---

### 4.5 Receipt Printing
- [ ] Build receipt template (HTML/Markdown)
- [ ] Implement ESC/POS adapter for thermal printers (58mm, 80mm)
- [ ] Implement PDF adapter for A4/Letter
- [ ] Handle text wrapping for long item names
- [ ] Include required fields: outlet, register, receipt_no, cashier, datetime, items, subtotal, discounts, tax, total, payments, change, footer (return policy)
- [ ] Test with 20+ items on 58mm and 80mm paper
- [ ] Add print preview
- [ ] Add email receipt option

**Acceptance Criteria:**
- Receipts render correctly on 58mm and 80mm
- Long item names wrap without truncation
- Alignment is consistent

---

### 4.6 Returns & Voids
- [ ] Build return screen (search sale by receipt_no)
- [ ] Implement full return (all items, full refund)
- [ ] Implement partial return (select items, partial refund)
- [ ] Require reason for return
- [ ] Require supervisor approval for returns > Rp1,000,000
- [ ] Create compensating stock_moves to restore inventory
- [ ] Update sale status to 'refunded'
- [ ] Build void transaction (before payment finalized)
- [ ] Ensure void reverses stock without reusing receipt_no

**Acceptance Criteria:**
- Partial returns for split-payment sales work correctly
- Stock restored only for returned items
- Void transactions keep audit trail

---

### 4.7 Daily Reports
- [ ] Build daily sales summary (total sales, total items, avg transaction)
- [ ] Build payment breakdown (cash, card, ewallet, transfer)
- [ ] Build tax report (subtotal_excl, tax_amount, total_incl)
- [ ] Build product sales report (top products, qty sold, revenue)
- [ ] Build shift report (per shift summary)
- [ ] Validate totals: sum(sale_items) = sum(payments) with tax math consistent
- [ ] Add export to PDF/Excel

**Acceptance Criteria:**
- Report totals match database sums
- Tax calculations are consistent
- Reports filterable by date range, outlet, register

---

## Phase 5: Vertical Modes

### 5.1 Restaurant Mode
- [ ] Create `tables` migration (tenant_id, outlet_id, number, status, seats)
- [ ] Create `table_tabs` migration (table_id, sale_id, status, opened_at)
- [ ] Build table management screen (floor plan, table status)
- [ ] Build open tab workflow (select table, add items)
- [ ] Build split bill workflow (divide items into separate sales)
- [ ] Build merge bill workflow (combine multiple tabs)
- [ ] Implement per-guest partial payments
- [ ] Add service charge (percent or fixed)
- [ ] Add tip calculation
- [ ] Build course firing/hold workflow
- [ ] Build kitchen ticket routing (by category/station)
- [ ] Integrate KDS (Kitchen Display System) - optional
- [ ] Add modifiers/add-ons with price deltas
- [ ] Implement comp/void with reason and RBAC
- [ ] Print separate receipts per split bill

**UI Components:**
- TableLayout.vue, TableCard.vue, TabScreen.vue, SplitBillModal.vue, KitchenTicket.vue

**Acceptance Criteria:**
- Split bill inventory decrements on commit per bill
- Service charge and tips included in totals with correct rounding
- Kitchen tickets route correctly by category

---

### 5.2 Minimarket Mode
- [ ] Build quick tender keys (cash denominations: Rp5k, 10k, 20k, 50k, 100k)
- [ ] Implement weighted barcode parsing (EAN with price embedded)
- [ ] Handle open-price items (e.g., bulk items weighed at checkout)
- [ ] Implement supervisor-gated price overrides (with threshold)
- [ ] Build receipt-based return workflow (scan receipt barcode)
- [ ] Optimize scanning performance (high-throughput)
- [ ] Add cash drawer open/close operations (pay in/out)
- [ ] Support label/receipt printing (58mm/80mm)
- [ ] Add basic per-line discounts only (no complex promotions for MVP)

**UI Components:**
- QuickTenderPanel.vue, WeightedBarcodeInput.vue, PriceOverrideModal.vue

**Acceptance Criteria:**
- Weighted barcode parsing correct (price extracted from EAN)
- Price overrides require supervisor approval over threshold
- Scanning performance meets high-throughput needs

---

## Phase 6: Events & Integrations

### 6.1 Domain Events
- [ ] Emit `sale.created` event with entity snapshot
- [ ] Emit `sale.voided` event
- [ ] Emit `sale.refunded` event
- [ ] Emit `stock.changed` event
- [ ] Emit `shift.opened` event
- [ ] Emit `shift.closed` event
- [ ] Include tenant_id in all event payloads
- [ ] Implement event listener for logging/analytics

**Acceptance Criteria:**
- Events include entity ID, snapshot, tenant context
- Events are tenant-namespaced

---

### 6.2 Webhooks
- [ ] Build webhook registration (URL, events, secret)
- [ ] Implement webhook delivery with exponential backoff retry
- [ ] Add idempotency key to webhook payloads
- [ ] Log webhook attempts and responses
- [ ] Build webhook management UI (test, retry, disable)

**Acceptance Criteria:**
- Webhooks are retryable and idempotent
- Failed webhooks retry with exponential backoff (max 5 attempts)

---

## Phase 7: Testing

### 7.1 Sales Flow Tests
- [ ] Test single-item sale with cash payment
- [ ] Test multi-item sale with line discounts
- [ ] Test order-level discount
- [ ] Test split payments (cash + card)
- [ ] Test cash rounding to nearest Rp100
- [ ] Test tax calculations (tax-inclusive formula)
- [ ] Test discount > price (floor to 0 line total)
- [ ] Test 0% tax items mixed with taxed items

**Test File:** `tests/Feature/SalesFlowTest.php`

---

### 7.2 Inventory & Concurrency Tests
- [ ] Test prevent negative stock when `allow_negative_stock=false`
- [ ] Test row-level locking prevents overselling (concurrent sales)
- [ ] Test inventory decrement per item on sale commit
- [ ] Test refund restores stock correctly
- [ ] Test void reverses stock without reusing receipt_no
- [ ] Test low stock alerts trigger

**Test File:** `tests/Feature/InventoryConcurrencyTest.php`

---

### 7.3 Shift Management Tests
- [ ] Test open shift with opening float
- [ ] Test cannot create sale if shift not open (403)
- [ ] Test close shift calculates expected cash from payments
- [ ] Test cash variance (expected vs actual)
- [ ] Test only one open shift per register
- [ ] Test stale shift handling

**Test File:** `tests/Feature/ShiftManagementTest.php`

---

### 7.4 Multi-Tenancy Tests
- [ ] Test no cross-tenant data leakage in queries
- [ ] Test same SKU allowed across tenants but unique within tenant
- [ ] Test tenant-scoped uniqueness constraints
- [ ] Test tenant-namespaced caches
- [ ] Test tenant-namespaced events
- [ ] Test SuperAdmin context switching
- [ ] Test receipt numbering unique per tenant + register

**Test File:** `tests/Feature/MultiTenancyTest.php`

---

### 7.5 Restaurant Mode Tests
- [ ] Test open tab per table
- [ ] Test split bill (divide items into separate sales)
- [ ] Test merge bill (combine multiple tabs)
- [ ] Test per-guest partial payments
- [ ] Test service charge and tip math
- [ ] Test kitchen ticket routing by category
- [ ] Test comp/void with reason and RBAC
- [ ] Test inventory decrements per split bill

**Test File:** `tests/Feature/RestaurantModeTest.php`

---

### 7.6 Minimarket Mode Tests
- [ ] Test weighted barcode parsing (price extraction)
- [ ] Test open-price items
- [ ] Test supervisor-gated price override
- [ ] Test receipt-based return workflow
- [ ] Test quick tender keys
- [ ] Test cash drawer operations (pay in/out)
- [ ] Test scanning performance (high-throughput)

**Test File:** `tests/Feature/MinimarketModeTest.php`

---

### 7.7 Edge Case Tests
- [ ] Test variant price_override vs product price_incl
- [ ] Test discount greater than price (floor to 0)
- [ ] Test partial returns for sales with split payments
- [ ] Test duplicate SKU/barcode on import (rejection)
- [ ] Test stale open shift (not closed overnight)
- [ ] Test duplicate offline uploads (dedupe by idempotency key)
- [ ] Test rounding interactions with service charges and tips
- [ ] Test weighted barcode prefixes colliding with real SKUs

**Test File:** `tests/Feature/EdgeCasesTest.php`

---

## Phase 8: Performance & Optimization

### 8.1 Performance Optimization
- [ ] Add indexes on frequently queried columns (tenant_id, outlet_id, register_id, shift_id, status)
- [ ] Implement eager loading for relationships (prevent N+1)
- [ ] Add Redis caching for product catalog
- [ ] Add Redis caching for inventory levels (invalidate on stock moves)
- [ ] Optimize sale commit transaction (minimize lock duration)
- [ ] Add database query logging to identify slow queries
- [ ] Load test sale endpoint (target: 200 rps burst, ≤ 800ms commit)
- [ ] Load test product search (target: TTFB ≤ 300ms)
- [ ] Add APM monitoring (e.g., Laravel Telescope, New Relic)

**Acceptance Criteria:**
- Read TTFB ≤ 300ms (p95)
- Sale commit ≤ 800ms at 200 rps burst (p95)
- Overall p95 ≤ 1.2s

---

### 8.2 Code Quality
- [ ] Run Laravel Pint (`vendor/bin/pint --dirty`)
- [ ] Run ESLint (`npm run lint`)
- [ ] Run Prettier (`npm run format`)
- [ ] Fix all linter errors
- [ ] Add PHPDoc blocks for complex methods
- [ ] Review and remove any TODOs or placeholders

---

## Phase 9: Documentation & Deployment

### 9.1 Documentation
- [ ] Update README.md with project overview
- [ ] Document installation and setup steps
- [ ] Document environment variables and configuration
- [ ] Document API endpoints (OpenAPI/Swagger - optional)
- [ ] Document RBAC roles and permissions
- [ ] Document vertical mode configuration
- [ ] Document deployment process
- [ ] Create user manual (Cashier, Supervisor, Admin)

---

### 9.2 Deployment Prep
- [ ] Configure production environment variables
- [ ] Set up production database (with replication if needed)
- [ ] Configure Redis for caching and queues
- [ ] Set up queue workers (Laravel Horizon recommended)
- [ ] Configure backup strategy (database, uploads)
- [ ] Set up monitoring and alerting (APM, error tracking)
- [ ] Configure SSL/TLS certificates
- [ ] Set up CDN for static assets (if needed)
- [ ] Run security audit (dependency scan, OWASP checks)
- [ ] Conduct penetration testing (optional for MVP)

---

## Progress Summary

### Completed: 0 / 30 Major Tasks
### In Progress: 0
### Pending: 30

---

## Notes & Decisions

### Architecture Decisions
- **Multi-tenancy:** Row-scoped with global query scopes on all models
- **Inventory Locking:** Row-level locks (`SELECT ... FOR UPDATE`) to prevent overselling
- **Tax Model:** Tax-inclusive pricing (prices include tax)
- **Rounding:** Bankers rounding for non-cash, Rp100 for cash
- **Idempotency:** Idempotency-Key header required for all state-changing endpoints
- **Events:** Domain events with webhook delivery (retryable, idempotent)

### Risks & Mitigations
- **Risk:** Inventory locking contention under high load  
  **Mitigation:** Optimize transaction duration, consider optimistic locking for read-heavy scenarios
  
- **Risk:** Receipt printing failures  
  **Mitigation:** Queue print jobs, provide fallback PDF download
  
- **Risk:** Offline mode sync conflicts  
  **Mitigation:** Server-side conflict resolution, device accepts corrections

### Open Questions
- [ ] Which thermal printer models to support?
- [ ] Cash drawer integration method?
- [ ] KDS integration (third-party or build custom)?
- [ ] Offline mode required for MVP?
- [ ] Multi-currency support timeline?

---

## References

- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Inertia.js v2 Documentation](https://inertiajs.com/)
- [Vue 3 Documentation](https://vuejs.org/)
- [Tailwind CSS v4 Documentation](https://tailwindcss.com/)
- [Pest v4 Documentation](https://pestphp.com/)
- MDC Requirements: `.cursor/rules/system-requirements.mdc`

---

**Last Updated:** October 26, 2025

