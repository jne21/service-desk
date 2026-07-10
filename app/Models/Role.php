<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use App\Models\Concerns\CachesReferenceData;

class Role extends Model
{
    use CachesReferenceData;

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
        return static::rememberReferenceCollection(
            'roles:ordered',
            fn () => static::query()
                ->orderBy('id')
                ->get(['id', 'name', 'home_route'])
        );
    }
}