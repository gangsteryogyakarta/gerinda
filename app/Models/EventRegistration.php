<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EventRegistration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_id',
        'massa_id',
        'ticket_number',
        'qr_code_path',
        'registration_status',
        'confirmed_at',
        'attendance_status',
        'checked_in_at',
        'checked_in_by',
        'checkin_method',
        'eligible_for_lottery',
        'won_lottery',
        'lottery_prize',
        'lottery_won_at',
        'custom_field_values',
        'notes',
        'registered_by',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'lottery_won_at' => 'datetime',
        'custom_field_values' => 'array',
        'eligible_for_lottery' => 'boolean',
        'won_lottery' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($registration) {
            if (empty($registration->ticket_number)) {
                $registration->ticket_number = static::generateTicketNumber($registration->event_id);
            }
        });
    }

    public static function generateTicketNumber(int $eventId): string
    {
        $prefix = 'TKT';
        $eventCode = str_pad($eventId, 4, '0', STR_PAD_LEFT);
        $timestamp = now()->format('ymdHis');
        $random = strtoupper(Str::random(4));
        return "{$prefix}{$eventCode}{$timestamp}{$random}";
    }

    // Relationships
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function massa(): BelongsTo
    {
        return $this->belongsTo(Massa::class);
    }

    public function checkedInByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function registeredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function checkinLogs(): HasMany
    {
        return $this->hasMany(CheckinLog::class);
    }

    public function lotteryDraws(): HasMany
    {
        return $this->hasMany(LotteryDraw::class);
    }

    // Scopes
    public function scopeConfirmed($query)
    {
        return $query->where('registration_status', 'confirmed');
    }

    public function scopeCheckedIn($query)
    {
        return $query->where('attendance_status', 'checked_in');
    }

    public function scopeNotArrived($query)
    {
        return $query->where('attendance_status', 'not_arrived');
    }

    public function scopeEligibleForLottery($query)
    {
        return $query->where('eligible_for_lottery', true)
            ->where('attendance_status', 'checked_in')
            ->where('won_lottery', false);
    }

    public function scopeByTicket($query, string $ticketNumber)
    {
        return $query->where('ticket_number', $ticketNumber);
    }

    // Methods
    public function confirm(): void
    {
        $this->update([
            'registration_status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
        
        $this->event->incrementParticipants();
    }

    public function checkIn(int $userId = null, string $method = 'manual'): void
    {
        $this->update([
            'attendance_status' => 'checked_in',
            'checked_in_at' => now(),
            'checked_in_by' => $userId,
            'checkin_method' => $method,
        ]);
    }

    public function markAsWinner(string $prize): void
    {
        $this->update([
            'won_lottery' => true,
            'lottery_prize' => $prize,
            'lottery_won_at' => now(),
        ]);
    }

    public function isCheckedIn(): bool
    {
        return $this->attendance_status === 'checked_in';
    }

    public function canCheckIn(): bool
    {
        return $this->registration_status === 'confirmed' 
            && $this->attendance_status === 'not_arrived';
    }

    /**
     * Get custom field value by field name
     */
    public function getCustomFieldValue(string $fieldName): mixed
    {
        return $this->custom_field_values[$fieldName] ?? null;
    }
}
