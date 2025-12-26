<?php

namespace App\Filament\Resources\SetupSheetResource\Pages;

use App\Filament\Resources\SetupSheetResource;
use App\Models\SetupSheet;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSetupSheet extends CreateRecord
{
    protected static string $resource = SetupSheetResource::class;

    public function mount(): void
    {
        parent::mount();

        $user = auth()->user();
        if (! $user) {
            return;
        }

        // ✅ Guard: ako je forma već popunjena (npr. posle validation error), ne diramo.
        // Ali ako je data setovana a zapravo prazna, dozvoli prefill.
        if (! empty($this->data)) {
            $hasMeaningful = false;

            foreach (['race_id','driver_id','time_label','chassis','engine','tyres_type'] as $k) {
                if (! empty($this->data[$k] ?? null)) {
                    $hasMeaningful = true;
                    break;
                }
            }

            if ($hasMeaningful) {
                return;
            }
        }

        // Poslednja shema koju je kreirao OVAJ korisnik
        $lastSheet = SetupSheet::query()
            ->where('created_by_id', $user->id)
            ->latest('id')
            ->first();

        if (! $lastSheet) {
            // bar postavi datum danas i očisti rezultate
            $this->form->fill([
                'date' => now()->toDateString(),
                'temperature' => null,
                'lap_time' => null,
                'fastest_lap' => null,
                'comments' => null,
            ]);
            return;
        }

        // Polja koja povlačimo iz prethodne sheme
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
            'camber',
            'caster',
            'tyres_type',

            // tyre pressures (cold)
            'pressure_cold_fl',
            'pressure_cold_fr',
            'pressure_cold_rl',
            'pressure_cold_rr',

            // tyre pressures (hot)
            'pressure_hot_fl',
            'pressure_hot_fr',
            'pressure_hot_rl',
            'pressure_hot_rr',

            // sliders
            'front_entry',
            'front_mid',
            'front_exit',
            'rear_entry',
            'rear_mid',
            'rear_exit',

            'engine_low',
            'engine_mid',
            'engine_top',
        ]);

        // Novi unos: datum danas, rezultati prazni
        $data['date'] = now()->toDateString();
        $data['temperature'] = null;
        $data['lap_time'] = null;
        $data['fastest_lap'] = null;
        $data['comments'] = null;

        $this->form->fill($data);
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function preserveFormDataWhenCreatingAnother(array $data): array
    {
        // ostavi sve setup vrednosti (uključujući tyre pressures), a očisti rezultate
        $data['temperature'] = null;
        $data['lap_time'] = null;
        $data['fastest_lap'] = null;
        $data['comments'] = null;

        // datum uvek današnji (da ne ostane stari)
        $data['date'] = now()->toDateString();

        return $data;
    }

    protected function getCreateAnotherFormAction(): Actions\Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Create & create a blank form');
    }
}
