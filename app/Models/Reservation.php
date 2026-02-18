<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Reservation extends Model
{
     use HasFactory;
    protected $fillable = [
        'confirmation_number',
        'customer_id',
        'subtotal',
        'tax_amount',
        'total_price',
        'item_count',
        'status',
        'pickup_date',
        'notes',
        'processed_by',
        'processed_at',
        'ready_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'item_count' => 'integer',
        'pickup_date' => 'datetime',
        'processed_at' => 'datetime',
        'ready_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $appends = [
        'status_label',
        'status_color',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reservation) {
            $reservation->confirmation_number = $reservation->confirmation_number
                ?? self::generateConfirmationNumber();
        });
    }

    public static function generateConfirmationNumber(): string
    {
        do {
            $number = 'PCV-' . strtoupper(Str::random(6));
        } while (self::where('confirmation_number', $number)->exists());

        return $number;
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(ReservationItem::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function recalculateTotals(): void
    {
        $this->subtotal = $this->items->sum('subtotal');
        $this->tax_amount = $this->items->sum('tax_amount');
        $this->total_price = $this->items->sum('total_price');
        $this->item_count = $this->items->sum('quantity');
        $this->save();
    }

    /**
     * Get the pickup deadline (24 hours after ready_at).
     */
    public function getPickupDeadlineAttribute(): ?Carbon
    {
        return $this->ready_at ? $this->ready_at->copy()->addHours(24) : null;
    }

    /**
     * Check if this reservation has expired (ready for more than 24 hours).
     */
    public function isExpired(): bool
    {
        return $this->status === 'ready'
            && $this->ready_at
            && $this->ready_at->diffInHours(now()) >= 24;
    }

    /**
     * Get the customer's email (supports both registered customers and guest email).
     */
    public function getNotificationEmail(): ?string
    {
        return $this->customer?->email;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'ready' => 'Ready for Pickup',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'ready' => 'green',
            'completed' => 'gray',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('created_at', now()->year);
    }

    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('created_at', $date);
    }

    /**
     * Scope: reservations that are ready and past the 24-hour pickup window.
     */
    public function scopeExpiredPickup($query)
    {
        return $query->where('status', 'ready')
            ->whereNotNull('ready_at')
            ->where('ready_at', '<=', now()->subHours(24));
    }
}
