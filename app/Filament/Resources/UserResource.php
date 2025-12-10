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
use Illuminate\Support\Str;

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
                            ->maxLength(255)
                            ->reactive(),

                        // uklonjen globalni unique() - validaciju radimo serverside po timu
                        Forms\Components\TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Unique per team. If left empty we will attempt to fill from email/name.')
                            ->afterStateUpdated(fn ($state, callable $set) => $set('username', $state ? Str::slug($state) : $state)),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->nullable()
                            ->unique(ignoreRecord: true),
                    ]),

                Forms\Components\Section::make('Team & role')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('team_id')
                            ->label('Team')
                            ->relationship(
                                name: 'team',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query) use ($currentUser) {
                                    if (! $currentUser) {
                                        return $query->whereRaw('1 = 0');
                                    }

                                    if ($currentUser->isSuperAdmin()) {
                                        return $query;
                                    }

                                    if ($currentUser->isTechnician()) {
                                        return $query->where('id', $currentUser->team_id);
                                    }

                                    return $query->whereRaw('1 = 0');
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->hidden(fn () => auth()->user()?->isTechnician())
                            ->required(fn (callable $get) => auth()->user()?->isSuperAdmin()
                                && $get('role') !== User::ROLE_SUPER_ADMIN),

                        Forms\Components\Select::make('role')
                            ->label('Role')
                            ->options(function () use ($currentUser) {
                                if ($currentUser?->isSuperAdmin()) {
                                    return [
                                        User::ROLE_SUPER_ADMIN => 'Super admin',
                                        User::ROLE_TECHNICIAN  => 'Technician',
                                        User::ROLE_WORKER      => 'Worker',
                                    ];
                                }

                                if ($currentUser?->isTechnician()) {
                                    // tehničar sme da pravi samo WORKER-e (ne SUPER_ADMIN)
                                    return [
                                        User::ROLE_WORKER => 'Worker',
                                    ];
                                }

                                return [];
                            })
                            ->required()
                            ->default(fn () => $currentUser?->isTechnician() ? User::ROLE_WORKER : User::ROLE_WORKER),
                    ]),

                Forms\Components\Section::make('Security')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $context) => $context === 'create')
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => filled($state)),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $currentUser = Auth::user();

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
                        'danger'  => User::ROLE_SUPER_ADMIN,
                        'warning' => User::ROLE_TECHNICIAN,
                        'success' => User::ROLE_WORKER,
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        User::ROLE_SUPER_ADMIN => 'Super admin',
                        User::ROLE_TECHNICIAN  => 'Technician',
                        User::ROLE_WORKER      => 'Worker',
                        default                => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Created at')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters(array_filter([
                // Team filter VIDI SAMO super admin
                $currentUser && $currentUser->isSuperAdmin()
                    ? Tables\Filters\SelectFilter::make('team_id')
                        ->label('Team')
                        ->relationship('team', 'name')
                    : null,

                // Role filter: opcije zavise od trenutnog korisnika
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->options(function () use ($currentUser) {
                        if (! $currentUser) {
                            return [];
                        }
                        if ($currentUser->isSuperAdmin()) {
                            return [
                                User::ROLE_SUPER_ADMIN => 'Super admin',
                                User::ROLE_TECHNICIAN  => 'Technician',
                                User::ROLE_WORKER      => 'Worker',
                            ];
                        }
                        if ($currentUser->isTechnician()) {
                            // tehničar ne bi trebalo da vidi SUPER_ADMIN filter opciju
                            return [
                                User::ROLE_TECHNICIAN => 'Technician',
                                User::ROLE_WORKER     => 'Worker',
                            ];
                        }

                        return [];
                    }),
            ]))
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

        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isTechnician()) {
            return $query
                ->where('team_id', $user->team_id)
                ->whereIn('role', [User::ROLE_TECHNICIAN, User::ROLE_WORKER]);
        }

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

        if ($user->isTechnician()) {
            return true;
        }

        return false;
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

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
            return $record->team_id === $user->team_id
                && in_array($record->role, [User::ROLE_TECHNICIAN, User::ROLE_WORKER], true);
        }

        return false;
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->isSuperAdmin() ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin() || $user->isTechnician()) {
            return true;
        }

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
