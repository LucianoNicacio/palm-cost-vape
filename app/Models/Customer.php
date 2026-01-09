<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class Customer extends Model
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'dob',
        'is_subscribed',
        'source',
        'notes',
        'total_reservations',
        'total_spent',
        'last_reservation_at',
    ];

    protected $casts = [
        'dob' => 'date',
        'is_subscribed' => 'boolean',
        'total_spent' => 'decimal:2',
        'last_reservation_at' => 'datetime',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }

    public function updateStats(): void
    {
        $this->total_reservations = $this->reservations()->count();
        $this->total_spent = $this->reservations()
            ->where('status', 'completed')
            ->sum('total_price');
        $this->last_reservation_at = $this->reservations()
            ->latest()
            ->value('created_at');
        $this->save();
    }

    public static function findOrCreateByEmail(array $data): self
    {
        return self::firstOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'dob' => $data['dob'] ?? null,
                'source' => $data['source'] ?? 'website',
            ]
        );
    }

    public function scopeSubscribed($query)
    {
        return $query->where('is_subscribed', true);
    }

    public function scopeNewThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }
}
