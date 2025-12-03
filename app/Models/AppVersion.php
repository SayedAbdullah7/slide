<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AppVersion extends Model
{
    protected $fillable = [
        'version',
        'os',
        'is_mandatory',
        'release_notes',
        'release_notes_ar',
        'is_active',
        'released_at',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
        'released_at' => 'datetime',
    ];

    /**
     * Scope to get active versions only
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by OS
     */
    public function scopeForOs(Builder $query, string $os): Builder
    {
        return $query->where('os', $os);
    }

    /**
     * Compare two version strings
     * Returns: 1 if $version1 > $version2, -1 if $version1 < $version2, 0 if equal
     */
    public static function compareVersions(string $version1, string $version2): int
    {
        $v1Parts = array_map('intval', explode('.', $version1));
        $v2Parts = array_map('intval', explode('.', $version2));

        $maxLength = max(count($v1Parts), count($v2Parts));

        for ($i = 0; $i < $maxLength; $i++) {
            $v1Part = $v1Parts[$i] ?? 0;
            $v2Part = $v2Parts[$i] ?? 0;

            if ($v1Part > $v2Part) {
                return 1;
            } elseif ($v1Part < $v2Part) {
                return -1;
            }
        }

        return 0;
    }

    /**
     * Get latest version for specific OS
     */
    public static function getLatestVersion(string $os): ?self
    {
        $versions = self::active()
            ->forOs($os)
            ->get();

        if ($versions->isEmpty()) {
            return null;
        }

        // Sort versions using custom comparison
        return $versions->sort(function ($a, $b) {
            return self::compareVersions($b->version, $a->version); // Descending order
        })->first();
    }

    /**
     * Check if there's a mandatory update available
     */
    public static function hasMandatoryUpdate(string $currentVersion, string $os): bool
    {
        $latestVersion = self::getLatestVersion($os);

        if (!$latestVersion || !$latestVersion->is_mandatory) {
            return false;
        }

        return self::compareVersions($latestVersion->version, $currentVersion) > 0;
    }
}
