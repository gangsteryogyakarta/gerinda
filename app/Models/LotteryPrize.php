<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LotteryPrize extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'image',
        'quantity',
        'remaining_quantity',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($prize) {
            if ($prize->remaining_quantity === null) {
                $prize->remaining_quantity = $prize->quantity;
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function draws(): HasMany
    {
        return $this->hasMany(LotteryDraw::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('remaining_quantity', '>', 0);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function hasStock(): bool
    {
        return $this->remaining_quantity > 0;
    }

    public function decrementStock(): void
    {
        if ($this->remaining_quantity > 0) {
            $this->decrement('remaining_quantity');
        }
    }
}
