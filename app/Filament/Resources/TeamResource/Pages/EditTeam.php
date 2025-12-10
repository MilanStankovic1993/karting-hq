<?php

namespace App\Filament\Resources\TeamResource\Pages;

use App\Filament\Resources\TeamResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\EditRecord;

class EditTeam extends EditRecord
{
    protected static string $resource = TeamResource::class;

    protected function getHeaderActions(): array
    {
        $team = $this->record;

        return [
            // Toggle (Activate / Deactivate) action visible only to super-admin
            Actions\Action::make('toggleSubscription')
                ->label(fn () => $team->is_active ? 'Deactivate team' : 'Activate team')
                ->color(fn () => $team->is_active ? 'danger' : 'success')
                ->icon(fn () => $team->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->requiresConfirmation(fn () => $team->is_active)
                ->modalHeading(fn () => ($team->is_active ? 'Deactivate team' : 'Activate team') . ': ' . $team->name)
                ->modalSubheading(fn () => $team->is_active
                    ? 'This will mark the team as inactive. Optionally you can also deactivate all users in this team.'
                    : 'This will mark the team as active again.')
                ->form([
                    Forms\Components\Checkbox::make('deactivate_users')
                        ->label('Also deactivate all users in this team')
                        ->helperText('If checked, will try to set users.is_active = false.'),
                ])
                ->action(function (array $data) use ($team) {
                    // If currently active -> deactivate (and optionally users)
                    if ($team->is_active) {
                        $deactivateUsers = (bool) ($data['deactivate_users'] ?? false);
                        $team->deactivateWithUsers($deactivateUsers);
                        return;
                    }

                    // If currently inactive -> activate (simple enable)
                    $team->is_active = true;

                    // if dates are missing, set a sensible default (optional) — here: do not auto-set unless you want
                    if (! $team->subscription_started_at) {
                        $team->subscription_started_at = now();
                    }
                    if (! $team->subscription_expires_at) {
                        $team->subscription_expires_at = now()->addDays(30);
                    }

                    $team->save();
                })
                ->hidden(fn () => ! auth()->user()?->isSuperAdmin()),

            // Keep existing delete action (super-admin only by resource policy)
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
