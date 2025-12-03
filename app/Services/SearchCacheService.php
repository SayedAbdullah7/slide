<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Search Cache Service
 *
 * This service handles caching of search results to improve performance.
 * Cache is automatically invalidated when data changes.
 */
class SearchCacheService
{
    /**
     * Cache duration in seconds (5 minutes)
     */
    private const CACHE_DURATION = 300;

    /**
     * Cache prefix for search queries
     */
    private const CACHE_PREFIX = 'search:';

    /**
     * Get cache key for a search query
     *
     * @param string $model
     * @param array $params
     * @return string
     */
    private function getCacheKey(string $model, array $params): string
    {
        $key = self::CACHE_PREFIX . $model . ':' . md5(json_encode($params));
        return $key;
    }

    /**
     * Get cached search results
     *
     * @param string $model
     * @param array $params
     * @return mixed|null
     */
    public function get(string $model, array $params)
    {
        $key = $this->getCacheKey($model, $params);
        return Cache::get($key);
    }

    /**
     * Cache search results
     *
     * @param string $model
     * @param array $params
     * @param mixed $data
     * @param int|null $duration
     * @return bool
     */
    public function put(string $model, array $params, $data, ?int $duration = null): bool
    {
        $key = $this->getCacheKey($model, $params);
        $duration = $duration ?? self::CACHE_DURATION;

        return Cache::put($key, $data, $duration);
    }

    /**
     * Check if cached results exist
     *
     * @param string $model
     * @param array $params
     * @return bool
     */
    public function has(string $model, array $params): bool
    {
        $key = $this->getCacheKey($model, $params);
        return Cache::has($key);
    }

    /**
     * Forget cached search results
     *
     * @param string $model
     * @param array $params
     * @return bool
     */
    public function forget(string $model, array $params): bool
    {
        $key = $this->getCacheKey($model, $params);
        return Cache::forget($key);
    }

    /**
     * Clear all search cache for a model
     *
     * @param string $model
     * @return void
     */
    public function clearModelCache(string $model): void
    {
        $pattern = self::CACHE_PREFIX . $model . ':*';
        Cache::forgetByPattern($pattern);
    }

    /**
     * Clear all search cache
     *
     * @return void
     */
    public function clearAll(): void
    {
        $pattern = self::CACHE_PREFIX . '*';
        Cache::forgetByPattern($pattern);
    }

    /**
     * Remember search results (get from cache or execute callback)
     *
     * @param string $model
     * @param array $params
     * @param callable $callback
     * @param int|null $duration
     * @return mixed
     */
    public function remember(string $model, array $params, callable $callback, ?int $duration = null)
    {
        $key = $this->getCacheKey($model, $params);
        $duration = $duration ?? self::CACHE_DURATION;

        return Cache::remember($key, $duration, $callback);
    }
}

