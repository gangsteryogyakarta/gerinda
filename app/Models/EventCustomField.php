<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventCustomField extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'field_name',
        'field_label',
        'field_type',
        'field_options',
        'placeholder',
        'help_text',
        'is_required',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'field_options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Validate a value against this field's rules
     */
    public function validateValue($value): bool
    {
        if ($this->is_required && empty($value)) {
            return false;
        }

        if (empty($value)) {
            return true;
        }

        switch ($this->field_type) {
            case 'number':
                return is_numeric($value);
            case 'date':
                return strtotime($value) !== false;
            case 'select':
            case 'radio':
                return in_array($value, $this->field_options ?? []);
            case 'checkbox':
                if (is_array($value)) {
                    return empty(array_diff($value, $this->field_options ?? []));
                }
                return true;
            default:
                return true;
        }
    }
}
