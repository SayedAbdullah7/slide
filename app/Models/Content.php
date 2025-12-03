<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Content extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'content',
        'last_updated',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_updated' => 'date',
    ];

    /**
     * Content types constants
     */
    const TYPE_PRIVACY_POLICY = 'privacy_policy';
    const TYPE_TERMS_CONDITIONS = 'terms_conditions';
    const TYPE_ABOUT_APP = 'about_app';

    /**
     * Scope to get only active content
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get content by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get all available content types
     */
    public static function getContentTypes()
    {
        return [
            self::TYPE_PRIVACY_POLICY => 'سياسة الخصوصية',
            self::TYPE_TERMS_CONDITIONS => 'الشروط والأحكام',
            self::TYPE_ABOUT_APP => 'عن التطبيق',
        ];
    }
}
