<?php

namespace App\Filament\Resources\SetupSheetResource\Pages;

use App\Filament\Resources\SetupSheetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSetupSheet extends EditRecord
{
    protected static string $resource = SetupSheetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
