<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventStatistic extends Model
{
    use HasFactory;

    protected $table = 'event_statistics';

    protected $fillable = [
        'event_id',
        'stat_date',
        'hour',
        'registrations_count',
        'checkins_count',
        'new_massa_count',
        'returning_massa_count',
        'demographic_breakdown',
        'location_breakdown',
    ];

    protected $casts = [
        'stat_date' => 'date',
        'demographic_breakdown' => 'array',
        'location_breakdown' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('stat_date', $date);
    }

    public function scopeHourly($query)
    {
        return $query->whereNotNull('hour');
    }

    public function scopeDaily($query)
    {
        return $query->whereNull('hour');
    }

    /**
     * Get or create a statistic record for an event, date, and hour
     */
    public static function getOrCreate(int $eventId, $date, ?int $hour = null): self
    {
        return static::firstOrCreate([
            'event_id' => $eventId,
            'stat_date' => $date,
            'hour' => $hour,
        ], [
            'registrations_count' => 0,
            'checkins_count' => 0,
            'new_massa_count' => 0,
            'returning_massa_count' => 0,
        ]);
    }

    public function incrementCheckins(): void
    {
        $this->increment('checkins_count');
    }

    public function incrementRegistrations(): void
    {
        $this->increment('registrations_count');
    }

    public function incrementNewMassa(): void
    {
        $this->increment('new_massa_count');
    }

    public function incrementReturningMassa(): void
    {
        $this->increment('returning_massa_count');
    }
}
