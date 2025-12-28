<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseActivation extends Model
{
    /** @use HasFactory<\Database\Factories\LicenseActivationFactory> */
    use HasFactory;

    protected $fillable = [
        'license_id',
        'domain',
        'ip_address',
        'local_key',
        'is_active',
        'activated_at',
        'deactivated_at',
        'deactivation_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'activated_at' => 'datetime',
            'deactivated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<License, $this>
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function deactivate(string $reason = 'Manual deactivation'): void
    {
        $this->update([
            'is_active' => false,
            'deactivated_at' => now(),
            'deactivation_reason' => $reason,
        ]);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }
}
