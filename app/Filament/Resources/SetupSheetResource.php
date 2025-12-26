<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SetupSheetResource\Pages;
use App\Models\SetupSheet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class SetupSheetResource extends Resource
{
    protected static ?string $model = SetupSheet::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Setup sheets';
    protected static ?string $navigationGroup = 'Karting';
    protected static ?int $navigationSort = 10;

    private static function ratingField(string $name, string $label): Forms\Components\Component
    {
        // Prefer slider if available in installed Filament version, fallback to select.
        if (class_exists(\Filament\Forms\Components\Slider::class)) {
            return \Filament\Forms\Components\Slider::make($name)
                ->label($label)
                ->minValue(-3)
                ->maxValue(3)
                ->step(1)
                ->default(0);
        }

        $opts = [];
        for ($i = -3; $i <= 3; $i++) {
            $opts[(string) $i] = (string) $i;
        }

        return Forms\Components\Select::make($name)
            ->label($label)
            ->options($opts)
            ->default('0')
            ->native(false);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // 🔗 VEZE: trka, vozač, ko je uneo
            Forms\Components\Section::make('Links')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('race_id')
                        ->label('Race / Event')
                        ->relationship(
                            name: 'race',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query) => $query->when(
                                Auth::user()?->team_id,
                                fn (Builder $q, $teamId) => $q->where('team_id', $teamId),
                            )
                        )
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->createOptionForm([
                            Forms\Components\Hidden::make('team_id')
                                ->default(fn () => Auth::user()?->team_id),

                            Forms\Components\TextInput::make('name')
                                ->label('Name')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('track')
                                ->label('Track')
                                ->maxLength(255),

                            Forms\Components\DatePicker::make('date')
                                ->label('Date'),

                            Forms\Components\Textarea::make('notes')
                                ->label('Notes')
                                ->rows(2),
                        ]),

                    Forms\Components\Select::make('driver_id')
                        ->label('Driver')
                        ->relationship(
                            name: 'driver',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query) => $query->when(
                                Auth::user()?->team_id,
                                fn (Builder $q, $teamId) => $q->where('team_id', $teamId),
                            )
                        )
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->createOptionForm([
                            Forms\Components\Hidden::make('team_id')
                                ->default(fn () => Auth::user()?->team_id),

                            Forms\Components\TextInput::make('name')
                                ->label('Name')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('short_name')
                                ->label('Short name')
                                ->maxLength(50),

                            Forms\Components\TextInput::make('kart_number')
                                ->label('Kart #')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(999)
                                ->nullable(),

                            Forms\Components\TextInput::make('team')
                                ->label('Team')
                                ->maxLength(255)
                                ->nullable(),
                        ]),

                    Forms\Components\Placeholder::make('created_by')
                        ->label('Created by')
                        ->content(fn (?SetupSheet $record) => $record?->createdBy?->name ?? auth()->user()?->name),
                ]),

            // 🧾 Osnovne informacije
            Forms\Components\Section::make('Basic info')
                ->columns(2)
                ->schema([
                    Forms\Components\DatePicker::make('date')
                        ->label('Date')
                        ->required(),

                    Forms\Components\TextInput::make('time_label')
                        ->label('Time / Test')
                        ->maxLength(255),
                ]),

            // 🛞 Kart setup
            Forms\Components\Section::make('Kart setup')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('chassis')->maxLength(255),
                    Forms\Components\TextInput::make('carb')->maxLength(255),
                    Forms\Components\TextInput::make('engine')->maxLength(255),
                    Forms\Components\TextInput::make('sprocket')->maxLength(255),
                    Forms\Components\TextInput::make('exhaust')->maxLength(255),
                    Forms\Components\TextInput::make('spacer')->maxLength(255),
                    Forms\Components\TextInput::make('axle')->maxLength(255),
                    Forms\Components\TextInput::make('front_bar')->label('Front bar')->maxLength(255),
                    Forms\Components\TextInput::make('ch_positions')->label('Ch. positions')->maxLength(255),

                    // ✅ Camber pa Caster
                    Forms\Components\TextInput::make('camber')->maxLength(255),
                    Forms\Components\TextInput::make('caster')->maxLength(255),

                    Forms\Components\TextInput::make('tyres_type')->label('Tyres type')->maxLength(255),
                ]),

            // ✅ Tyre pressures – 2 kvadrata (cold/hot) 2x2 jedna do druge
            Forms\Components\Section::make('Tyre pressures')
                ->columns(2)
                ->schema([
                    Forms\Components\Fieldset::make('Cold')
                        ->columns(2)
                        ->columnSpan(1) // ✅ forsira da zauzme 1/2 širine
                        ->schema([
                            Forms\Components\TextInput::make('pressure_cold_fl')->label('FL')->numeric()->step(0.1),
                            Forms\Components\TextInput::make('pressure_cold_fr')->label('FR')->numeric()->step(0.1),
                            Forms\Components\TextInput::make('pressure_cold_rl')->label('RL')->numeric()->step(0.1),
                            Forms\Components\TextInput::make('pressure_cold_rr')->label('RR')->numeric()->step(0.1),
                        ]),

                    Forms\Components\Fieldset::make('Hot')
                        ->columns(2)
                        ->columnSpan(1) // ✅ forsira da zauzme 1/2 širine
                        ->schema([
                            Forms\Components\TextInput::make('pressure_hot_fl')->label('FL')->numeric()->step(0.1),
                            Forms\Components\TextInput::make('pressure_hot_fr')->label('FR')->numeric()->step(0.1),
                            Forms\Components\TextInput::make('pressure_hot_rl')->label('RL')->numeric()->step(0.1),
                            Forms\Components\TextInput::make('pressure_hot_rr')->label('RR')->numeric()->step(0.1),
                        ]),
                ]),

            // ✅ Sliders -3..3 (default 0)
            Forms\Components\Section::make('Balance / Handling')
                ->columns(3)
                ->schema([
                    self::ratingField('front_entry', 'Front entry'),
                    self::ratingField('front_mid', 'Front mid'),
                    self::ratingField('front_exit', 'Front exit'),

                    self::ratingField('rear_entry', 'Rear entry'),
                    self::ratingField('rear_mid', 'Rear mid'),
                    self::ratingField('rear_exit', 'Rear exit'),
                ]),

            Forms\Components\Section::make('Engine')
                ->columns(3)
                ->schema([
                    self::ratingField('engine_low', 'Low'),
                    self::ratingField('engine_mid', 'Mid'),
                    self::ratingField('engine_top', 'Top'),
                ]),

            // Rezultat + komentari
            Forms\Components\Section::make('Result')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('temperature')->label('Temperature')->maxLength(255),

                    Forms\Components\TextInput::make('lap_time')->label('Lap time')->maxLength(255),

                    Forms\Components\TextInput::make('fastest_lap')->label('Fastest lap')->maxLength(255),

                    Forms\Components\Textarea::make('comments')
                        ->label('Comments')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('race.name')
                    ->label('Race / Event')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('driver.name')
                    ->label('Driver')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('time_label')
                    ->label('Time / Test')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('lap_time')
                    ->label('Lap time')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('fastest_lap')
                    ->label('Fastest lap')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('temperature')
                    ->label('Temp')
                    ->toggleable(),

                // ✅ grouped tyre pressures (toggle hidden by default)
                Tables\Columns\TextColumn::make('pressure_cold_summary')
                    ->label('Pressure (Cold)')
                    ->html()
                    ->state(function (SetupSheet $record) {
                        $fl = $record->pressure_cold_fl;
                        $fr = $record->pressure_cold_fr;
                        $rl = $record->pressure_cold_rl;
                        $rr = $record->pressure_cold_rr;

                        if ($fl === null && $fr === null && $rl === null && $rr === null) {
                            return '—';
                        }

                        return new HtmlString(
                            '<div class="text-xs leading-5">' .
                                '<div><strong>FL</strong> ' . e((string) $fl) . ' &nbsp; <strong>FR</strong> ' . e((string) $fr) . '</div>' .
                                '<div><strong>RL</strong> ' . e((string) $rl) . ' &nbsp; <strong>RR</strong> ' . e((string) $rr) . '</div>' .
                            '</div>'
                        );
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('pressure_hot_summary')
                    ->label('Pressure (Hot)')
                    ->html()
                    ->state(function (SetupSheet $record) {
                        $fl = $record->pressure_hot_fl;
                        $fr = $record->pressure_hot_fr;
                        $rl = $record->pressure_hot_rl;
                        $rr = $record->pressure_hot_rr;

                        if ($fl === null && $fr === null && $rl === null && $rr === null) {
                            return '—';
                        }

                        return new HtmlString(
                            '<div class="text-xs leading-5">' .
                                '<div><strong>FL</strong> ' . e((string) $fl) . ' &nbsp; <strong>FR</strong> ' . e((string) $fr) . '</div>' .
                                '<div><strong>RL</strong> ' . e((string) $rl) . ' &nbsp; <strong>RR</strong> ' . e((string) $rr) . '</div>' .
                            '</div>'
                        );
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                // === Setup detalji – sakriveni po difoltu ===
                Tables\Columns\TextColumn::make('chassis')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('engine')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sprocket')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tyres_type')->label('Tyres')->toggleable(isToggledHiddenByDefault: true),

                // ✅ uklonjeno: front_entry / rear_entry kolone iz liste

                // === Meta / audit polja – sakrivena po difoltu ===
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Created by')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updatedBy.name')
                    ->label('Updated by')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('race_id')
                    ->label('Race')
                    ->relationship('race', 'name'),

                SelectFilter::make('driver_id')
                    ->label('Driver')
                    ->relationship('driver', 'name'),

                Filter::make('date_range')
                    ->label('Date range')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('date', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSetupSheets::route('/'),
            'create' => Pages\CreateSetupSheet::route('/create'),
            'edit'   => Pages\EditSetupSheet::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->where('team_id', $user->team_id);
    }
}
