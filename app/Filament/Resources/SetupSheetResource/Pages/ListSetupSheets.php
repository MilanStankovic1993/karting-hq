<?php

namespace App\Filament\Resources\SetupSheetResource\Pages;

use App\Filament\Resources\SetupSheetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSetupSheets extends ListRecords
{
    protected static string $resource = SetupSheetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
