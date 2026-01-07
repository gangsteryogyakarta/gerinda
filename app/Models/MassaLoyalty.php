<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MassaLoyalty extends Model
{
    use HasFactory;

    protected $table = 'massa_loyalty';

    protected $fillable = [
        'massa_id',
        'total_events_registered',
        'total_events_attended',
        'total_lotteries_won',
        'first_event_date',
        'last_event_date',
        'attendance_rate',
        'loyalty_tier',
        'points',
    ];

    protected $casts = [
        'first_event_date' => 'date',
        'last_event_date' => 'date',
        'attendance_rate' => 'decimal:2',
    ];

    public function massa(): BelongsTo
    {
        return $this->belongsTo(Massa::class);
    }

    /**
     * Recalculate loyalty stats for a massa
     */
    public function recalculate(): void
    {
        $massa = $this->massa;
        $registrations = $massa->registrations()
            ->whereHas('event', fn($q) => $q->whereIn('status', ['completed', 'ongoing']))
            ->get();

        $totalRegistered = $registrations->count();
        $totalAttended = $registrations->where('attendance_status', 'checked_in')->count();
        $totalWon = $registrations->where('won_lottery', true)->count();

        $attendanceRate = $totalRegistered > 0 
            ? ($totalAttended / $totalRegistered) * 100 
            : 0;

        $firstEvent = $registrations->min('created_at');
        $lastEvent = $registrations->max('created_at');

        // Calculate tier based on attendance
        $tier = match(true) {
            $totalAttended >= 20 => 'platinum',
            $totalAttended >= 10 => 'gold',
            $totalAttended >= 5 => 'silver',
            default => 'bronze',
        };

        // Points calculation (example formula)
        $points = ($totalAttended * 10) + ($totalWon * 50);

        $this->update([
            'total_events_registered' => $totalRegistered,
            'total_events_attended' => $totalAttended,
            'total_lotteries_won' => $totalWon,
            'first_event_date' => $firstEvent?->toDateString(),
            'last_event_date' => $lastEvent?->toDateString(),
            'attendance_rate' => round($attendanceRate, 2),
            'loyalty_tier' => $tier,
            'points' => $points,
        ]);
    }

    public function scopeByTier($query, string $tier)
    {
        return $query->where('loyalty_tier', $tier);
    }

    public function scopeTopAttenders($query, int $limit = 10)
    {
        return $query->orderByDesc('total_events_attended')->limit($limit);
    }
}
