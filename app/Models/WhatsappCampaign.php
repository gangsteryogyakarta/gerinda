<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappCampaign extends Model
{
    protected $fillable = [
        'name',
        'message',
        'filters',
        'recipients',
        'recipient_count',
        'scheduled_at',
        'status',
        'sent_count',
        'failed_count',
        'created_by',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'recipients' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeDueToSend($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now());
    }

    public function messages()
    {
        return $this->hasMany(WhatsappMessageLog::class, 'campaign_id');
    }
}
