<?php

namespace App\Filament\Resources\SetupSheetResource\Pages;

use App\Filament\Resources\SetupSheetResource;
use App\Models\SetupSheet;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions;

class CreateSetupSheet extends CreateRecord
{
    protected static string $resource = SetupSheetResource::class;

    public function mount(): void
    {
        parent::mount();

        // Ako nema user-a iz nekog razloga (ne bi trebalo u Filamentu, ali da smo mirni)
        $user = auth()->user();
        if (! $user) {
            return;
        }

        // Poslednja shema koju je kreirao OVAJ korisnik
        $lastSheet = SetupSheet::query()
            ->where('created_by_id', $user->id)
            ->latest('id') // ili 'created_at'
            ->first();

        if (! $lastSheet) {
            return;
        }

        // Polja koja želiš da povučeš iz prethodne sheme
        $data = $lastSheet->only([
            'race_id',
            'driver_id',
            'time_label',

            'chassis',
            'carb',
            'engine',
            'sprocket',
            'exhaust',
            'spacer',
            'axle',
            'front_bar',
            'ch_positions',
            'caster',
            'camber',
            'tyres_type',

            'front_entry',
            'front_mid',
            'front_exit',
            'rear_entry',
            'rear_mid',
            'rear_exit',

            'engine_low',
            'engine_mid',
            'engine_top',

            'temperature',
        ]);

        // Za novi unos: datum danas, a vreme rezultata i komentari prazni
        $data['date'] = now()->toDateString();
        $data['fastest_lap'] = null;
        $data['comments'] = null;

        $this->form->fill($data);
    }

    /**
     * Posle običnog "Create" vraća na listu.
     */
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    /**
     * Upisujemo created_by_id + team_id pri kreiranju.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if (! $user) {
            return $data;
        }

        // Tim korisnika – za tehnicare/worker-e obavezno postoji
        if ($user->team_id) {
            $data['team_id'] = $user->team_id;
        }

        // Ko je kreirao sheet
        $data['created_by_id'] = $user->id;

        return $data;
    }

    /**
     * Za "Create & create another" – šta ostaje u formi posle kreiranja.
     * (kod tebe je labela "Create & create a blank form", ali ponašanje
     * je i dalje default; ako želiš full prazno, možemo i ovde da null-ujemo sve).
     */
    protected function preserveFormDataWhenCreatingAnother(array $data): array
    {
        // Po defaultu ostavi sve, pa očisti šta nećeš
        $data['fastest_lap'] = null;
        $data['comments'] = null;

        return $data;
    }

    /**
     * Samo menjamo label da bude jasnija.
     */
    protected function getCreateAnotherFormAction(): Actions\Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Create & create a blank form');
    }
}
