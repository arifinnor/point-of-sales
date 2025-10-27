<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    /** @use HasFactory<\Database\Factories\InventoryFactory> */
    use HasFactory, HasUuids;

    protected $table = 'inventory';

    protected $fillable = [
        'tenant_id',
        'variant_id',
        'outlet_id',
        'on_hand',
        'safety_stock',
    ];

    protected function casts(): array
    {
        return [
            'on_hand' => 'integer',
            'safety_stock' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Inventory $inventory) {
            if (! $inventory->tenant_id && app()->has('current_tenant')) {
                $inventory->tenant_id = app()->get('current_tenant')->id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    /**
     * Check if stock is low (below or at safety stock).
     */
    public function isLowStock(): bool
    {
        return $this->on_hand <= $this->safety_stock;
    }

    /**
     * Check if stock is available.
     */
    public function isAvailable(int $quantity = 1): bool
    {
        return $this->on_hand >= $quantity;
    }

    /**
     * Decrement stock with optional lock.
     */
    public function decrementStock(int $quantity, bool $allowNegative = false): bool
    {
        if (! $allowNegative && $this->on_hand < $quantity) {
            return false;
        }

        $this->decrement('on_hand', $quantity);

        return true;
    }

    /**
     * Increment stock.
     */
    public function incrementStock(int $quantity): void
    {
        $this->increment('on_hand', $quantity);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('on_hand', '<=', 'safety_stock');
    }
}
