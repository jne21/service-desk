<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketSource extends Model
{
    protected $fillable = [
        'code',
        'name',
        'api_token_hash',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'source_id');
    }

    public function imports(): HasMany
    {
        return $this->hasMany(TicketImport::class, 'ticket_source_id');
    }
}