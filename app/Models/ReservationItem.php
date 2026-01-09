<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationItem extends Model
{
    protected $fillable = [
        'reservation_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total_price',
        'product_name',
        'product_sku',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public static function createFromProduct(Reservation $reservation, Product $product, int $quantity): self
    {
        $pricing = $product->calculateItemPricing($quantity);

        return self::create([
            'reservation_id' => $reservation->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $pricing['unit_price'],
            'subtotal' => $pricing['subtotal'],
            'tax_rate' => $pricing['tax_rate'],
            'tax_amount' => $pricing['tax_amount'],
            'total_price' => $pricing['total_price'],
            'product_name' => $product->name,
            'product_sku' => $product->sku,
        ]);
    }
}
