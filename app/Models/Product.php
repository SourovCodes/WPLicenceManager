<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'version',
        'download_url',
        'is_active',
        'requires_license',
        'has_api_access',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'requires_license' => 'boolean',
            'has_api_access' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /**
     * @return HasMany<License, $this>
     */
    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    public function isPlugin(): bool
    {
        return $this->type === 'plugin';
    }

    public function isTheme(): bool
    {
        return $this->type === 'theme';
    }
}
