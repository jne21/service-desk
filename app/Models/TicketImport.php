<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class TicketImport extends Model
{
    protected $fillable = [
        'ticket_source_id',
        'status_id',
        'tickets_count',
        'created_count',
        'updated_count',
        'restored_count',
        'failed_count',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'tickets_count' => 'integer',
            'created_count' => 'integer',
            'updated_count' => 'integer',
            'restored_count' => 'integer',
            'failed_count' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(TicketSource::class, 'ticket_source_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TicketImportStatus::class, 'status_id');
    }

    public function scopeForSource(Builder $query, TicketSource $source): Builder
    {
        return $query->where('ticket_source_id', $source->id);
    }
}