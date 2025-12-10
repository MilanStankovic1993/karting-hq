<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Users';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        $currentUser = Auth::user();

        return $form
            ->schema([
                Forms\Components\Section::make('Basic info')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->nullable()
                            ->unique(ignoreRecord: true),
                    ]),

                Forms\Components\Section::make('Team & role')
                    ->columns(2)
                    ->schema([
                        // TIM
                        // pretpostavljam da već imaš $currentUser gore definisan u form() metodi
                        // ako nemaš, na početku form() dodaj:
                        // $currentUser = auth()->user();
                        Forms\Components\Select::make('team_id')
                            ->label('Team')
                            ->relationship(
                                name: 'team',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query) use ($currentUser) {
                                    if (! $currentUser) {
                                        // bez user-a ne prikazuj ništa
                                        return $query->whereRaw('1 = 0');
                                    }

                                    // Super admin vidi sve timove
                                    if ($currentUser->isSuperAdmin()) {
                                        return $query;
                                    }

                                    // Tehničar vidi samo SVOJ tim
                                    if ($currentUser->isTechnician()) {
                                        return $query->where('id', $currentUser->team_id);
                                    }

                                    // Worker ne treba ni da bude ovde
                                    return $query->whereRaw('1 = 0');
                                }
                            )
                            ->searchable()
                            ->preload()
                            // Tehničaru ovaj select uopšte ne treba – njegov tim se upisuje automatski
                            ->hidden(fn () => auth()->user()?->isTechnician())
                            // Super admin MORA da dodeli tim (osim ako kreira baš SUPER_ADMIN-a)
                            ->required(fn (callable $get) => auth()->user()?->isSuperAdmin()
                                && $get('role') !== \App\Models\User::ROLE_SUPER_ADMIN),

                        // ROLE
                        Forms\Components\Select::make('role')
                            ->label('Role')
                            ->options(function () use ($currentUser) {
                                // Super admin može sve
                                if ($currentUser?->isSuperAdmin()) {
                                    return [
                                        'SUPER_ADMIN' => 'Super admin',
                                        'TECHNICIAN'  => 'Technician',
                                        'WORKER'      => 'Worker',
                                    ];
                                }

                                // Tehničar može da pravi SAMO radnike
                                if ($currentUser?->isTechnician()) {
                                    return [
                                        'WORKER' => 'Worker',
                                    ];
                                }

                                return [];
                            })
                            ->required()
                            ->default(fn () =>
                                $currentUser?->isTechnician()
                                    ? 'WORKER'
                                    : 'WORKER' // default za super admina kad zaboravi da izabere
                            ),
                    ]),


                Forms\Components\Section::make('Security')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $context) => $context === 'create')
                            ->dehydrateStateUsing(fn ($state) =>
                                filled($state) ? bcrypt($state) : null
                            )
                            ->dehydrated(fn ($state) => filled($state)),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('team.name')
                    ->label('Team')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('role')
                    ->label('Role')
                    ->colors([
                        'danger'  => 'SUPER_ADMIN',
                        'warning' => 'TECHNICIAN',
                        'success' => 'WORKER',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'SUPER_ADMIN' => 'Super admin',
                        'TECHNICIAN'  => 'Technician',
                        'WORKER'      => 'Worker',
                        default       => $state,
                    }),
            ])
            ->defaultSort('name')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => Auth::user()?->isSuperAdmin()),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => Auth::user()?->isSuperAdmin()),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        // Super admin vidi sve
        if ($user->isSuperAdmin()) {
            return $query;
        }

        // Tehničar vidi samo svoj tim, i to samo tehnicare + radnike
        if ($user->isTechnician()) {
            return $query
                ->where('team_id', $user->team_id)
                ->whereIn('role', ['TECHNICIAN', 'WORKER']);
        }

        // Worker nema pristup Users
        return $query->whereRaw('1 = 0');
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        // Tehničar vidi Users (svoj tim)
        if ($user->isTechnician()) {
            return true;
        }

        // Worker – ne
        return false;
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        // Super admin: može sve
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Tehničar može da pravi radnike svog tima
        if ($user->isTechnician()) {
            return true;
        }

        return false;
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isTechnician()) {
            // može da menja samo korisnike svog tima i ne može da dira super admine
            return $record->team_id === $user->team_id
                && in_array($record->role, ['TECHNICIAN', 'WORKER'], true);
        }

        return false;
    }

    public static function canDelete($record): bool
    {
        // samo super admin briše korisnike
        return Auth::user()?->isSuperAdmin() ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        // super admin + tehnicar vide Users u meniju
        if ($user->isSuperAdmin() || $user->isTechnician()) {
            return true;
        }

        // worker – ne
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
