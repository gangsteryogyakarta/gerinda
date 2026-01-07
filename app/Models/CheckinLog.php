<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckinLog extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $fillable = [
        'event_id',
        'event_registration_id',
        'massa_id',
        'checked_by',
        'action',
        'method',
        'device_info',
        'ip_address',
        'latitude',
        'longitude',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(EventRegistration::class, 'event_registration_id');
    }

    public function massa(): BelongsTo
    {
        return $this->belongsTo(Massa::class);
    }

    public function checkedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function scopeForEvent($query, int $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeCheckIns($query)
    {
        return $query->where('action', 'check_in');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByHour($query, int $hour)
    {
        return $query->whereRaw('HOUR(created_at) = ?', [$hour]);
    }
}
