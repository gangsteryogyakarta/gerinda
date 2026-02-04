<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Village extends Model
{
    use HasFactory;

    protected $fillable = ['district_id', 'code', 'name', 'postal_code', 'latitude', 'longitude'];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function massa(): HasMany
    {
        return $this->hasMany(Massa::class);
    }
}
