<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

trait CachesReferenceData
{
    protected static function rememberReferenceCollection(
        string $cacheKey,
        callable $callback
    ): Collection {
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return static::hydrate($cached);
        }

        if ($cached !== null) {
            Cache::forget($cacheKey);
        }

        /** @var Collection $collection */
        $collection = $callback();

        Cache::forever($cacheKey, $collection->toArray());

        return $collection;
    }

    protected static function rememberReferenceInt(
        string $cacheKey,
        callable $callback
    ): int {
        $cached = Cache::get($cacheKey);

        if (is_int($cached)) {
            return $cached;
        }

        if (is_numeric($cached)) {
            return (int) $cached;
        }

        if ($cached !== null) {
            Cache::forget($cacheKey);
        }

        $value = (int) $callback();

        Cache::forever($cacheKey, $value);

        return $value;
    }
}