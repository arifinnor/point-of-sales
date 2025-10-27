<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'sku',
        'name',
        'category_id',
        'tax_rate',
        'price_incl',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:2',
            'price_incl' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Product $product) {
            if (! $product->tenant_id && app()->has('current_tenant')) {
                $product->tenant_id = app()->get('current_tenant')->id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the tax-exclusive price.
     */
    public function getPriceExclAttribute(): float
    {
        return round($this->price_incl / (1 + ($this->tax_rate / 100)), 2);
    }

    /**
     * Get the tax amount.
     */
    public function getTaxAmountAttribute(): float
    {
        return round($this->price_incl - $this->price_excl, 2);
    }
}
