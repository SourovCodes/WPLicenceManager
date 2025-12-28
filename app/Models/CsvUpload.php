<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class CsvUpload extends Model
{
    /** @use HasFactory<\Database\Factories\CsvUploadFactory> */
    use HasFactory;

    protected $fillable = [
        'license_id',
        'original_filename',
        'stored_path',
        'sftp_host',
        'sftp_port',
        'sftp_username',
        'sftp_password_encrypted',
        'sftp_remote_path',
        'status',
        'error_message',
        'processed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sftp_port' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'sftp_password_encrypted',
    ];

    /**
     * @return BelongsTo<License, $this>
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function setSftpPasswordAttribute(string $value): void
    {
        $this->attributes['sftp_password_encrypted'] = Crypt::encryptString($value);
    }

    public function getSftpPasswordAttribute(): ?string
    {
        if (empty($this->attributes['sftp_password_encrypted'])) {
            return null;
        }

        return Crypt::decryptString($this->attributes['sftp_password_encrypted']);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
            'error_message' => null,
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'processed_at' => now(),
        ]);
    }
}
