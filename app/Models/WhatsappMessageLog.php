<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappMessageLog extends Model
{
    protected $fillable = [
        'message_id',
        'phone',
        'message',
        'status',
        'failure_reason',
        'event_id',
        'campaign_id',
        'sent_at',
        'delivered_at',
        'read_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function campaign()
    {
        return $this->belongsTo(WhatsappCampaign::class, 'campaign_id');
    }
}
