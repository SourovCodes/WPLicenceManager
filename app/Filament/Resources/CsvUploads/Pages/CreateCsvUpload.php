<?php

namespace App\Filament\Resources\CsvUploads\Pages;

use App\Filament\Resources\CsvUploads\CsvUploadResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCsvUpload extends CreateRecord
{
    protected static string $resource = CsvUploadResource::class;
}
