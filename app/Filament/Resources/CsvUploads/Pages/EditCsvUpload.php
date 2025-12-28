<?php

namespace App\Filament\Resources\CsvUploads\Pages;

use App\Filament\Resources\CsvUploads\CsvUploadResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCsvUpload extends EditRecord
{
    protected static string $resource = CsvUploadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
