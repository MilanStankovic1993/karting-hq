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
use Filament\Forms\Get;
use Filament\Forms\Set;
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

    private static function pressureField(string $name, string $label): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make($name)
            ->label($label)
            ->numeric()
            ->step(1)
            ->minValue(0)
            ->maxValue(300)
            ->formatStateUsing(fn ($state) => $state === null ? null : (int) round(((float) $state) * 100))
            ->dehydrateStateUsing(fn ($state) => $state === null || $state === '' ? null : ((float) $state) / 100);
    }

    private static function pressureSummary(?float $fl, ?float $fr, ?float $rl, ?float $rr): HtmlString|string
    {
        if ($fl === null && $fr === null && $rl === null && $rr === null) {
            return '—';
        }

        $fmt = fn ($v) => $v === null ? '—' : (string) ((int) round($v * 100));

        return new HtmlString(
            '<div class="text-xs leading-5">' .
                '<div><strong>FL</strong> ' . e($fmt($fl)) . ' &nbsp; <strong>FR</strong> ' . e($fmt($fr)) . '</div>' .
                '<div><strong>RL</strong> ' . e($fmt($rl)) . ' &nbsp; <strong>RR</strong> ' . e($fmt($rr)) . '</div>' .
            '</div>'
        );
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
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

                    Forms\Components\TextInput::make('wheels')
                        ->label('Wheels')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('ch_position_front')->label('CH position – Front')->maxLength(255),
                    Forms\Components\TextInput::make('ch_position_rear')->label('CH position – Rear')->maxLength(255),

                    Forms\Components\TextInput::make('camber')->maxLength(255),
                    Forms\Components\TextInput::make('caster')->maxLength(255),

                    Forms\Components\TextInput::make('tyres_type')->label('Tyres type')->maxLength(255),
                ]),

            /**
             * ✅ Special (optional) – svaki field ima svoj toggle (uključi/isključi posebno)
             */
            Forms\Components\Section::make('Special (optional)')
                ->description('Enable only what you need')
                ->collapsible()
                ->collapsed()
                ->columns(3)
                ->schema([
                    // --- Rear hubs ---
                    Forms\Components\Toggle::make('show_rear_hubs')
                        ->label('Rear hubs')
                        ->helperText('Enable field')
                        ->dehydrated(false)
                        ->live()
                        ->afterStateHydrated(function (Forms\Components\Toggle $component, ?SetupSheet $record) {
                            $component->state(filled($record?->rear_hubs));
                        })
                        ->afterStateUpdated(function ($state, Set $set) {
                            if (! $state) {
                                $set('rear_hubs', null);
                            }
                        }),

                    Forms\Components\TextInput::make('rear_hubs')
                        ->label('Rear hubs value')
                        ->maxLength(255)
                        ->nullable()
                        ->visible(fn (Get $get) => (bool) $get('show_rear_hubs'))
                        ->columnSpan(2),

                    // --- Front hubs ---
                    Forms\Components\Toggle::make('show_front_hubs')
                        ->label('Front hubs')
                        ->helperText('Enable field')
                        ->dehydrated(false)
                        ->live()
                        ->afterStateHydrated(function (Forms\Components\Toggle $component, ?SetupSheet $record) {
                            $component->state($record?->front_hubs !== null);
                        })
                        ->afterStateUpdated(function ($state, Set $set) {
                            if (! $state) {
                                $set('front_hubs', null);
                            }
                        }),

                    Forms\Components\TextInput::make('front_hubs')
                        ->label('Front hubs value')
                        ->numeric()
                        ->step(1)
                        ->minValue(0)
                        ->maxValue(9999)
                        ->nullable()
                        ->visible(fn (Get $get) => (bool) $get('show_front_hubs'))
                        ->columnSpan(2),

                    // --- Special axle ---
                    Forms\Components\Toggle::make('show_special_axle')
                        ->label('Special axle')
                        ->helperText('Enable field')
                        ->dehydrated(false)
                        ->live()
                        ->afterStateHydrated(function (Forms\Components\Toggle $component, ?SetupSheet $record) {
                            $component->state(filled($record?->special_axle));
                        })
                        ->afterStateUpdated(function ($state, Set $set) {
                            if (! $state) {
                                $set('special_axle', null);
                            }
                        }),

                    Forms\Components\TextInput::make('special_axle')
                        ->label('Special axle value')
                        ->maxLength(255)
                        ->nullable()
                        ->visible(fn (Get $get) => (bool) $get('show_special_axle'))
                        ->columnSpan(2),

                    // --- Bearing carriers ---
                    Forms\Components\Toggle::make('show_bearing_carriers')
                        ->label('Bearing carriers')
                        ->helperText('Enable field')
                        ->dehydrated(false)
                        ->live()
                        ->afterStateHydrated(function (Forms\Components\Toggle $component, ?SetupSheet $record) {
                            $component->state(filled($record?->bearing_carriers));
                        })
                        ->afterStateUpdated(function ($state, Set $set) {
                            if (! $state) {
                                $set('bearing_carriers', null);
                            }
                        }),

                    Forms\Components\TextInput::make('bearing_carriers')
                        ->label('Bearing carriers value')
                        ->maxLength(255)
                        ->nullable()
                        ->visible(fn (Get $get) => (bool) $get('show_bearing_carriers'))
                        ->columnSpan(2),

                    // --- Rear width ---
                    Forms\Components\Toggle::make('show_rear_width')
                        ->label('Rear width')
                        ->helperText('Enable field')
                        ->dehydrated(false)
                        ->live()
                        ->afterStateHydrated(function (Forms\Components\Toggle $component, ?SetupSheet $record) {
                            $component->state($record?->rear_width !== null);
                        })
                        ->afterStateUpdated(function ($state, Set $set) {
                            if (! $state) {
                                $set('rear_width', null);
                            }
                        }),

                    Forms\Components\TextInput::make('rear_width')
                        ->label('Rear width value')
                        ->numeric()
                        ->step(1)
                        ->minValue(0)
                        ->maxValue(9999)
                        ->nullable()
                        ->visible(fn (Get $get) => (bool) $get('show_rear_width'))
                        ->columnSpan(2),
                ]),


            Forms\Components\Section::make('Tyre pressures')
                ->columns(2)
                ->schema([
                    Forms\Components\Fieldset::make('Cold')
                        ->columns(2)
                        ->columnSpan(1)
                        ->schema([
                            self::pressureField('pressure_cold_fl', 'FL'),
                            self::pressureField('pressure_cold_fr', 'FR'),
                            self::pressureField('pressure_cold_rl', 'RL'),
                            self::pressureField('pressure_cold_rr', 'RR'),
                        ]),

                    Forms\Components\Fieldset::make('Hot')
                        ->columns(2)
                        ->columnSpan(1)
                        ->schema([
                            self::pressureField('pressure_hot_fl', 'FL'),
                            self::pressureField('pressure_hot_fr', 'FR'),
                            self::pressureField('pressure_hot_rl', 'RL'),
                            self::pressureField('pressure_hot_rr', 'RR'),
                        ]),
                ]),

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
                Tables\Columns\TextColumn::make('date')->date()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('race.name')->label('Race / Event')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('driver.name')->label('Driver')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('time_label')->label('Time / Test')->toggleable(),
                Tables\Columns\TextColumn::make('lap_time')->label('Lap time')->toggleable(),
                Tables\Columns\TextColumn::make('fastest_lap')->label('Fastest lap')->toggleable(),
                Tables\Columns\TextColumn::make('temperature')->label('Temp')->toggleable(),

                Tables\Columns\TextColumn::make('wheels')
                    ->label('Wheels')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('rear_hubs')->label('Rear hubs')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('front_hubs')->label('Front hubs')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('special_axle')->label('Special axle')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('bearing_carriers')->label('Bearing carriers')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('rear_width')->label('Rear width')->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('pressure_cold_summary')
                    ->label('Pressure (Cold)')
                    ->html()
                    ->state(fn (SetupSheet $r) => self::pressureSummary(
                        $r->pressure_cold_fl,
                        $r->pressure_cold_fr,
                        $r->pressure_cold_rl,
                        $r->pressure_cold_rr
                    ))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('pressure_hot_summary')
                    ->label('Pressure (Hot)')
                    ->html()
                    ->state(fn (SetupSheet $r) => self::pressureSummary(
                        $r->pressure_hot_fl,
                        $r->pressure_hot_fr,
                        $r->pressure_hot_rl,
                        $r->pressure_hot_rr
                    ))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('chassis')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('engine')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sprocket')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tyres_type')->label('Tyres')->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('createdBy.name')->label('Created by')->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updatedBy.name')->label('Updated by')->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->label('Created at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->label('Updated at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('race_id')->label('Race')->relationship('race', 'name'),
                SelectFilter::make('driver_id')->label('Driver')->relationship('driver', 'name'),

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
