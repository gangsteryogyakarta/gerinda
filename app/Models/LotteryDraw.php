<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotteryDraw extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'lottery_prize_id',
        'event_registration_id',
        'massa_id',
        'drawn_by',
        'drawn_at',
        'is_claimed',
        'claimed_at',
    ];

    protected $casts = [
        'drawn_at' => 'datetime',
        'claimed_at' => 'datetime',
        'is_claimed' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function prize(): BelongsTo
    {
        return $this->belongsTo(LotteryPrize::class, 'lottery_prize_id');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(EventRegistration::class, 'event_registration_id');
    }

    public function massa(): BelongsTo
    {
        return $this->belongsTo(Massa::class);
    }

    public function drawnByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'drawn_by');
    }

    public function claim(): void
    {
        $this->update([
            'is_claimed' => true,
            'claimed_at' => now(),
        ]);
    }
}
