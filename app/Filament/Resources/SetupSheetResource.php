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

class SetupSheetResource extends Resource
{
    protected static ?string $model = SetupSheet::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Setup sheets';
    protected static ?string $navigationGroup = 'Karting';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // 🔗 VEZE: trka, vozač, ko je uneo
                Forms\Components\Section::make('Links')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('race_id')
                            ->label('Race / Event')
                            ->relationship(
                                name: 'race',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->when(
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
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->when(
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
                            ->content(fn (?SetupSheet $record) =>
                                $record?->createdBy?->name ?? auth()->user()?->name
                            ),
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
                        Forms\Components\TextInput::make('caster')->maxLength(255),
                        Forms\Components\TextInput::make('camber')->maxLength(255),
                        Forms\Components\TextInput::make('tyres_type')->label('Tyres type')->maxLength(255),
                    ]),

                // Pritisci
                Forms\Components\Section::make('Tyre pressures')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('front_entry')->label('Front entry')->maxLength(255),
                        Forms\Components\TextInput::make('front_mid')->label('Front mid')->maxLength(255),
                        Forms\Components\TextInput::make('front_exit')->label('Front exit')->maxLength(255),
                        Forms\Components\TextInput::make('rear_entry')->label('Rear entry')->maxLength(255),
                        Forms\Components\TextInput::make('rear_mid')->label('Rear mid')->maxLength(255),
                        Forms\Components\TextInput::make('rear_exit')->label('Rear exit')->maxLength(255),
                    ]),

                // Motor
                Forms\Components\Section::make('Engine')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('engine_low')->label('Low')->maxLength(255),
                        Forms\Components\TextInput::make('engine_mid')->label('Mid')->maxLength(255),
                        Forms\Components\TextInput::make('engine_top')->label('Top')->maxLength(255),
                    ]),

                // Rezultat + komentari
                Forms\Components\Section::make('Result')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('temperature')->label('Temperature')->maxLength(255),
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
                // === Default vidljive kolone ===
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

                Tables\Columns\TextColumn::make('fastest_lap')
                    ->label('Fastest lap')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('temperature')
                    ->label('Temp')
                    ->toggleable(),

                // === Setup detalji – sakriveni po difoltu, mogu da se upale po potrebi ===
                Tables\Columns\TextColumn::make('chassis')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('engine')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('sprocket')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('tyres_type')
                    ->label('Tyres')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('front_entry')
                    ->label('Front entry')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('rear_entry')
                    ->label('Rear entry')
                    ->toggleable(isToggledHiddenByDefault: true),

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

        // Ako nema user-a iz nekog razloga – ne vraćamo ništa
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        // Super admin vidi sve
        if ($user->isSuperAdmin()) {
            return $query;
        }

        // Svi ostali vide samo svoj tim
        return $query->where('team_id', $user->team_id);
    }
}
