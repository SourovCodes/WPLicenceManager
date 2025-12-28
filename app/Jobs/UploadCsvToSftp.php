<?php

namespace App\Jobs;

use App\Models\CsvUpload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use phpseclib3\Net\SFTP;
use Throwable;

class UploadCsvToSftp implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public CsvUpload $csvUpload) {}

    public function handle(): void
    {
        $this->csvUpload->markAsProcessing();

        try {
            $sftp = new SFTP($this->csvUpload->sftp_host, $this->csvUpload->sftp_port);

            if (! $sftp->login($this->csvUpload->sftp_username, $this->csvUpload->sftp_password)) {
                throw new \RuntimeException('SFTP authentication failed. Please check your credentials.');
            }

            $localPath = Storage::disk('local')->path($this->csvUpload->stored_path);

            if (! file_exists($localPath)) {
                throw new \RuntimeException('CSV file not found on server.');
            }

            $remotePath = $this->csvUpload->sftp_remote_path
                ? rtrim($this->csvUpload->sftp_remote_path, '/').'/'.$this->csvUpload->original_filename
                : $this->csvUpload->original_filename;

            $result = $sftp->put($remotePath, $localPath, SFTP::SOURCE_LOCAL_FILE);

            if (! $result) {
                throw new \RuntimeException('Failed to upload file to SFTP server.');
            }

            $this->csvUpload->markAsCompleted();

            Storage::disk('local')->delete($this->csvUpload->stored_path);

            Log::info('CSV uploaded successfully to SFTP', [
                'csv_upload_id' => $this->csvUpload->id,
                'remote_path' => $remotePath,
            ]);
        } catch (Throwable $e) {
            Log::error('SFTP upload failed', [
                'csv_upload_id' => $this->csvUpload->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        $this->csvUpload->markAsFailed($exception->getMessage());

        Log::error('CSV SFTP upload job failed permanently', [
            'csv_upload_id' => $this->csvUpload->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
