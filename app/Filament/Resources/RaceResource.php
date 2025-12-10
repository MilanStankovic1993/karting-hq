<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RaceResource\Pages;
use App\Models\Race;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RaceResource extends Resource
{
    protected static ?string $model = Race::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationLabel = 'Races';
    protected static ?string $navigationGroup = 'Karting';
    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // === Default vidljive kolone ===
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('track')
                    ->label('Track')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                // Notes kao opciona kolona (skrivena po difoltu)
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(40)
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
                // ovde možemo kasnije dodati filtere (po datumu, stazi itd.)
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRaces::route('/'),
            'create' => Pages\CreateRace::route('/create'),
            'edit'   => Pages\EditRace::route('/{record}/edit'),
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
