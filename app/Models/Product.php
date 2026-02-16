<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use IlluminateDatabaseEloquentFactoriesHasFactory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'external_id',
        'name',
        'sku',
        'description',
        'price',
        'is_taxable',
        'track_inventory',
        'stock',
        'category_id',
        'is_active',
        'is_featured',
        'brand',
        'image',
        'age_restricted',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_taxable' => 'boolean',
        'track_inventory' => 'boolean',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'age_restricted' => 'boolean',
    ];

    // Add accessors to JSON output
    protected $appends = [
        'image_url',
        'in_stock',
        'formatted_price',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        return Str::startsWith($this->image, 'http')
            ? $this->image
            : Storage::url($this->image);
    }

    public function getInStockAttribute(): bool
    {
        return !$this->track_inventory || $this->stock > 0;
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where('track_inventory', false)
                ->orWhere('stock', '>', 0);
        });
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', '%' . $term . '%')
                ->orWhere('sku', 'like', '%' . $term . '%');
        });
    }

    public function decrementStock(int $quantity = 1): void
    {
        if ($this->track_inventory) {
            $this->decrement('stock', $quantity);
        }
    }

    public function incrementStock(int $quantity = 1): void
    {
        if ($this->track_inventory) {
            $this->increment('stock', $quantity);
        }
    }

    public function calculateItemPricing(int $quantity): array
    {
        $subtotal = $this->price * $quantity;
        $taxRate = $this->is_taxable ? (float) config('store.tax_rate', 0.07) : 0;
        $taxAmount = round($subtotal * $taxRate, 2);

        return [
            'unit_price' => $this->price,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total_price' => $subtotal + $taxAmount,
        ];
    }
}