<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverResource\Pages;
use App\Models\Driver;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Drivers';
    protected static ?string $navigationGroup = 'Karting';
    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('short_name')
                    ->label('Short name')
                    ->maxLength(50),

                Forms\Components\TextInput::make('team')
                    ->label('Karting team')
                    ->maxLength(255),

                Forms\Components\TextInput::make('kart_number')
                    ->label('Kart #')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(999)
                    ->nullable(),

                Forms\Components\Toggle::make('active')
                    ->label('Active')
                    ->default(true),

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

                Tables\Columns\TextColumn::make('short_name')
                    ->label('Short')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('team')
                    ->label('Karting team')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('kart_number')
                    ->label('Kart #')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('active')
                    ->boolean()
                    ->label('Active')
                    ->toggleable(),

                // === Meta / audit polja – sakrivena po difoltu, mogu da se uključe ===
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
            ->defaultSort('name')
            ->filters([
                // ovde možemo posle da dodamo filtere (npr. active)
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
            'index'  => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'edit'   => Pages\EditDriver::route('/{record}/edit'),
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
