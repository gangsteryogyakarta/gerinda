<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'slug',
        'event_category_id',
        'description',
        'copywriting',
        'banner_image',
        'venue_name',
        'venue_address',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'postal_code',
        'latitude',
        'longitude',
        'registration_start',
        'registration_end',
        'event_start',
        'event_end',
        'max_participants',
        'current_participants',
        'enable_waitlist',
        'require_ticket',
        'enable_checkin',
        'send_wa_notification',
        'status',
        'created_by',
    ];

    protected $casts = [
        'registration_start' => 'datetime',
        'registration_end' => 'datetime',
        'event_start' => 'datetime',
        'event_end' => 'datetime',
        'enable_waitlist' => 'boolean',
        'require_ticket' => 'boolean',
        'enable_checkin' => 'boolean',
        'send_wa_notification' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($event) {
            if (empty($event->code)) {
                $event->code = static::generateEventCode();
            }
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->name) . '-' . Str::random(6);
            }
        });
    }

    public static function generateEventCode(): string
    {
        $prefix = 'EVT';
        $date = now()->format('ymd');
        $random = strtoupper(Str::random(4));
        return "{$prefix}{$date}{$random}";
    }

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customFields(): HasMany
    {
        return $this->hasMany(EventCustomField::class)->orderBy('sort_order');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }



    public function checkinLogs(): HasMany
    {
        return $this->hasMany(CheckinLog::class);
    }

    public function statistics(): HasMany
    {
        return $this->hasMany(EventStatistic::class);
    }

    // Accessors
    public function getIsFullAttribute(): bool
    {
        if ($this->max_participants === null) {
            return false;
        }
        return $this->current_participants >= $this->max_participants;
    }

    public function getAvailableSlotsAttribute(): ?int
    {
        if ($this->max_participants === null) {
            return null;
        }
        return max(0, $this->max_participants - $this->current_participants);
    }

    public function getRegistrationOpenAttribute(): bool
    {
        // Database stores datetime WITHOUT timezone info (assumed WIB/Asia/Jakarta)
        // Laravel treats it as UTC when casting, so we need to adjust
        // Get current time in WIB for fair comparison with stored WIB times
        $nowWib = now()->setTimezone('Asia/Jakarta');
        
        // Convert stored UTC-interpreted datetime back to what it really is (WIB value)
        // The stored value "11:10" is meant to be 11:10 WIB, but Laravel reads it as 11:10 UTC
        // So we create a Carbon from the format string without timezone conversion
        if ($this->registration_start) {
            $regStartWib = \Carbon\Carbon::createFromFormat(
                'Y-m-d H:i:s', 
                $this->getAttributes()['registration_start'],
                'Asia/Jakarta'
            );
            if ($nowWib < $regStartWib) {
                return false;
            }
        }
        
        if ($this->registration_end) {
            $regEndWib = \Carbon\Carbon::createFromFormat(
                'Y-m-d H:i:s', 
                $this->getAttributes()['registration_end'],
                'Asia/Jakarta'
            );
            if ($nowWib > $regEndWib) {
                return false;
            }
        }
        
        return in_array($this->status, ['published', 'ongoing']);
    }

    public function getIsOngoingAttribute(): bool
    {
        $now = now();
        return $now >= $this->event_start && $now <= $this->event_end;
    }

    public function isRegistrationOpen(): bool
    {
        return $this->registration_open;
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('event_start', '>', now())
            ->whereIn('status', ['published', 'ongoing'])
            ->orderBy('event_start');
    }

    public function scopePast($query)
    {
        return $query->where('event_end', '<', now());
    }

    // Methods
    public function incrementParticipants(): void
    {
        $this->increment('current_participants');
    }

    public function decrementParticipants(): void
    {
        if ($this->current_participants > 0) {
            $this->decrement('current_participants');
        }
    }

    public function canRegister(): bool
    {
        if (!$this->registration_open) {
            return false;
        }
        
        if ($this->is_full && !$this->enable_waitlist) {
            return false;
        }
        
        return true;
    }
}
