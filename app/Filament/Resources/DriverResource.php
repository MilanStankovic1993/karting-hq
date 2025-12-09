<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverResource\Pages;
use App\Models\Driver;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Karting';
    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('short_name')
                    ->maxLength(50)
                    ->label('Short name')
                    ->hint('npr. MIL'),

                Forms\Components\TextInput::make('team')
                    ->maxLength(255),

                Forms\Components\TextInput::make('kart_number')
                    ->label('Kart #')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(999)
                    ->nullable(),

                Forms\Components\Toggle::make('active')
                    ->default(true),

                Forms\Components\Textarea::make('notes')
                    ->rows(3),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('short_name')
                    ->label('Short'),

                Tables\Columns\TextColumn::make('team')
                    ->searchable(),

                Tables\Columns\TextColumn::make('kart_number')
                    ->label('Kart #')
                    ->sortable(),

                Tables\Columns\IconColumn::make('active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'edit' => Pages\EditDriver::route('/{record}/edit'),
        ];
    }
}
