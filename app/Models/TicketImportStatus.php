<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketImportStatus extends Model
{
    public const CODE_QUEUED = 'queued';
    public const CODE_PROCESSING = 'processing';
    public const CODE_FINISHED = 'finished';
    public const CODE_FAILED = 'failed';
    public const CODE_FINISHED_WITH_ERRORS = 'finished_with_errors';

    protected $fillable = [
        'code',
        'name',
        'sort_order',
        'is_final',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_final' => 'boolean',
        ];
    }

    public function imports(): HasMany
    {
        return $this->hasMany(TicketImport::class, 'status_id');
    }
}