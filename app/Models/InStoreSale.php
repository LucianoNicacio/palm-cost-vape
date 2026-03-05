<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InStoreSale extends Model
{
    protected $fillable = [
        'recorded_by',
        'total_items',
        'total_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'total_items' => 'integer',
    ];

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InStoreSaleItem::class);
    }
}
