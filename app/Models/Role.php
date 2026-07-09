<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class Role extends Model
{
    protected $fillable = [
        'name',
        'home_route',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function orderedCached(): Collection
    {
        return Cache::rememberForever(
            'roles:ordered',
            fn () => static::query()
                ->orderBy('id')
                ->get(['id', 'name', 'home_route'])
        );
    }
}