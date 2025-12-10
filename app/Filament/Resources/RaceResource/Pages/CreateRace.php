<?php

namespace App\Filament\Resources\RaceResource\Pages;

use App\Filament\Resources\RaceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRace extends CreateRecord
{
    protected static string $resource = RaceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if ($user && $user->team_id) {
            $data['team_id'] = $user->team_id;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
