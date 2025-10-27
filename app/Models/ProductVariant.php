<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    /** @use HasFactory<\Database\Factories\ProductVariantFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'product_id',
        'code',
        'name',
        'barcode',
        'price_override_incl',
    ];

    protected function casts(): array
    {
        return [
            'price_override_incl' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class, 'variant_id');
    }

    /**
     * Get the effective price (override or product price).
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->price_override_incl ?? $this->product->price_incl;
    }

    /**
     * Get the effective tax rate from product.
     */
    public function getEffectiveTaxRateAttribute(): float
    {
        return $this->product->tax_rate;
    }

    /**
     * Get inventory for a specific outlet.
     */
    public function inventoryForOutlet(string $outletId): ?Inventory
    {
        return $this->inventory()->where('outlet_id', $outletId)->first();
    }
}
