<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $currentUser = auth()->user();

        if ($currentUser?->isTechnician()) {
            // Tehničar uvek kreira korisnike u SVOM timu
            $data['team_id'] = $currentUser->team_id;
            // Tehničar može samo da kreira WORKER role (UI već ograničava, ali dodatna sigurnost)
            $data['role'] = User::ROLE_WORKER;
        }

        // Ko kreira user-a (audit)
        if ($currentUser) {
            $data['created_by_id'] = $currentUser->id;
            $data['updated_by_id'] = $currentUser->id;
        }

        // Ako username nije poslat (UI zahteva), pokušaj da popunimo iz email ili name
        if (empty($data['username'])) {
            if (! empty($data['email'])) {
                $data['username'] = strstr($data['email'], '@', true) ?: $data['email'];
            } elseif (! empty($data['name'])) {
                $data['username'] = Str::slug($data['name']);
            }
        }

        // --- SERVER-SIDE: ensure username uniqueness WITHIN TEAM only ---
        // Ako imamo team_id -> enforce uniqueness per team by suffixing numeric counter
        if (! empty($data['team_id']) && ! empty($data['username'])) {
            $base = Str::slug($data['username']);
            $username = $base;
            $i = 1;
            while (User::where('team_id', $data['team_id'])->where('username', $username)->exists()) {
                $username = $base . '-' . $i;
                $i++;
            }
            $data['username'] = $username;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
