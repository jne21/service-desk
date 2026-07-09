<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class TicketStatus extends Model
{
    protected $fillable = [
        'code',
        'name',
        'sort_order',
        'is_final',
    ];

    public static function orderedCached(): Collection
    {
        return Cache::rememberForever(
            'ticket_statuses:ordered',
            fn () => static::query()
                ->orderBy('sort_order')
                ->get(['id', 'code', 'name', 'sort_order', 'is_final'])
        );
    }

    protected function casts(): array
    {
        return [
            'is_final' => 'boolean',
        ];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'status_id');
    }
}