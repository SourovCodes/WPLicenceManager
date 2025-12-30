<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class License extends Model
{
    /** @use HasFactory<\Database\Factories\LicenseFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'license_key',
        'customer_name',
        'customer_email',
        'status',
        'validity_days',
        'activated_at',
        'expires_at',
        'max_domain_changes',
        'domain_changes_used',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'expires_at' => 'datetime',
            'validity_days' => 'integer',
            'max_domain_changes' => 'integer',
            'domain_changes_used' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (License $license) {
            if (empty($license->license_key)) {
                $license->license_key = self::generateLicenseKey();
            }
        });
    }

    public static function generateLicenseKey(): string
    {
        do {
            $key = strtoupper(implode('-', [
                Str::random(4),
                Str::random(4),
                Str::random(4),
                Str::random(4),
            ]));
        } while (self::where('license_key', $key)->exists());

        return $key;
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasMany<LicenseActivation, $this>
     */
    public function activations(): HasMany
    {
        return $this->hasMany(LicenseActivation::class);
    }

    /**
     * @return HasOne<LicenseActivation, $this>
     */
    public function currentActivation(): HasOne
    {
        return $this->hasOne(LicenseActivation::class)->where('is_active', true);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return true;
        }

        return false;
    }

    public function isValid(): bool
    {
        return $this->isActive() && ! $this->isExpired();
    }

    public function getInvalidReason(): string
    {
        if ($this->status === 'inactive') {
            return 'License is inactive.';
        }

        if ($this->status === 'revoked') {
            return 'License has been revoked.';
        }

        if ($this->isExpired()) {
            return 'License has expired. Please renew your license.';
        }

        return 'License is not valid.';
    }

    public function canChangeDomain(): bool
    {
        return $this->domain_changes_used < $this->max_domain_changes;
    }

    public function remainingDomainChanges(): int
    {
        return max(0, $this->max_domain_changes - $this->domain_changes_used);
    }

    public function hasActiveActivation(): bool
    {
        return $this->currentActivation()->exists();
    }

    public function getActiveDomain(): ?string
    {
        return $this->currentActivation?->domain;
    }

    public function activate(string $domain, ?string $ipAddress = null): LicenseActivation
    {
        // If this is the first activation, set activation date and expiry
        if (! $this->activated_at) {
            $this->activated_at = now();
            $this->expires_at = now()->addDays($this->validity_days);
            $this->status = 'active';
            $this->save();
        }

        // Deactivate any existing activation
        $existingActivation = $this->currentActivation;
        if ($existingActivation) {
            if ($existingActivation->domain !== $domain) {
                $this->increment('domain_changes_used');
            }
            $existingActivation->deactivate('Switched to new domain');
        }

        // Create new activation
        return $this->activations()->create([
            'domain' => $domain,
            'ip_address' => $ipAddress,
            'local_key' => Str::random(64),
            'is_active' => true,
            'activated_at' => now(),
        ]);
    }

    public function deactivate(string $reason = 'Manual deactivation'): bool
    {
        $activation = $this->currentActivation;
        if ($activation) {
            $activation->deactivate($reason);

            return true;
        }

        return false;
    }
}
