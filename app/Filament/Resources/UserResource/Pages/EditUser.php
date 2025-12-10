<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\User;
use Illuminate\Support\Str;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $currentUser = auth()->user();

        if ($currentUser?->isTechnician()) {
            // Tehničar ne može da prebacuje ljude u drugi tim
            $data['team_id'] = $currentUser->team_id;
            // Ne dozvoljavamo tehničaru da menja role u nešto drugo
            $data['role'] = User::ROLE_WORKER;
        }

        // Ko menja user-a (audit)
        if ($currentUser) {
            $data['updated_by_id'] = $currentUser->id;
        }

        // Ako username je prazan (ne bi trebalo) - popuni iz email ili name
        if (empty($data['username'])) {
            if (! empty($data['email'])) {
                $data['username'] = strstr($data['email'], '@', true) ?: $data['email'];
            } elseif (! empty($data['name'])) {
                $data['username'] = Str::slug($data['name']);
            }
        }

        // --- SERVER-SIDE: ensure username uniqueness WITHIN TEAM only ---
        // Ako imamo team_id -> enforce uniqueness per team by suffixing numeric counter,
        // IGNORING current record ID (so user can keep same username)
        if (! empty($data['team_id']) && ! empty($data['username'])) {
            $base = Str::slug($data['username']);
            $username = $base;
            $i = 1;
            $currentId = $this->record?->id;
            while (User::where('team_id', $data['team_id'])
                        ->where('username', $username)
                        ->when($currentId, fn($q) => $q->where('id', '<>', $currentId))
                        ->exists()) {
                $username = $base . '-' . $i;
                $i++;
            }
            $data['username'] = $username;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()?->isSuperAdmin()),
        ];
    }
}
