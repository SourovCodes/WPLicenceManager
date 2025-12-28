<?php

namespace App\Filament\Resources\CsvUploads\Pages;

use App\Filament\Resources\CsvUploads\CsvUploadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCsvUploads extends ListRecords
{
    protected static string $resource = CsvUploadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
