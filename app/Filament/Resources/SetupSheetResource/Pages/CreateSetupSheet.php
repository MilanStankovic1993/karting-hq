<?php

namespace App\Filament\Resources\SetupSheetResource\Pages;

use App\Filament\Resources\SetupSheetResource;
use App\Models\SetupSheet;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions;

class CreateSetupSheet extends CreateRecord
{
    protected static string $resource = SetupSheetResource::class;

    /**
     * Polja koja preuzimamo iz prethodne sheme istog korisnika.
     */
    private const PREFILLABLE_FIELDS = [
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
    ];

    public function mount(): void
    {
        parent::mount();

        // Poslednja shema od trenutno ulogovanog korisnika (po created_by_id)
        $lastSheet = SetupSheet::query()
            ->where('created_by_id', auth()->id())
            ->latest('id')
            ->first();

        if (! $lastSheet) {
            return;
        }

        $data = $lastSheet->only(self::PREFILLABLE_FIELDS);

        // Nova sesija: današnji datum, prazni rezultati/komentari.
        $data['date']        = now()->toDateString();
        $data['fastest_lap'] = null;
        $data['comments']    = null;

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
     * Upisujemo created_by_id pri kreiranju.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_id'] = auth()->id();

        return $data;
    }

    /**
     * Za "Create & create another" – šta ostaje u formi posle kreiranja.
     */
    protected function preserveFormDataWhenCreatingAnother(array $data): array
    {
        // Zadrži sve, ali resetuj ono što logično treba da bude novo:
        $data['fastest_lap'] = null;
        $data['comments']    = null;

        return $data;
    }
        /**
     * Predefinišemo form action za "Create & create another"
     * samo da promenimo label.
     */
    protected function getCreateAnotherFormAction(): Actions\Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Create & create a blank form');
    }
}
