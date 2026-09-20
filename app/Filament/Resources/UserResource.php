<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'User Management';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->description('Manage user identity, password, and access.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->autofocus(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->afterStateHydrated(function (Forms\Components\TextInput $component): void {
                                $component->state(null);
                            })
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->maxLength(255)
                            ->helperText(fn(string $operation): ?string => $operation === 'edit'
                                ? 'Leave empty to keep current password.'
                                : null),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->same('password')
                            ->dehydrated(false)
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->helperText(fn(string $operation): ?string => $operation === 'edit'
                                ? 'Fill only if you want to change password.'
                                : null),
                        Forms\Components\Select::make('branch_id')
                            ->label('Branch')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\Select::make('role')
                            ->label('Role')
                            ->options(fn(): array => static::getRoleOptions())
                            ->visible(fn(?User $record): bool => ! $record?->hasAnyRole(['admin', 'super_admin']))
                            ->required()
                            ->default('staff'),
                    ])
                    ->columns(2),
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
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->state(fn(User $record): string => $record->getRoleNames()
                        ->map(fn(string $role): string => Str::headline($role))
                        ->implode(', '))
                    ->color(fn(User $record): string => match ($record->getRoleNames()->first()) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'contractor' => 'success',
                        'staff' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->options(fn(): array => static::getRoleOptions()),
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name')
                    ->label('Branch')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Impersonate::make()
                        ->label('Impersonate')
                        ->grouped()
                        ->icon('heroicon-o-user-circle')
                        ->color('primary')
                        ->tooltip('Login as user')
                        ->requiresConfirmation()
                        ->modalHeading('Confirm Login')
                        ->modalDescription('Are you sure you want to log in as this user?')
                        ->modalSubmitActionLabel('Yes, Login')
                        ->redirectTo(fn(User $record): string => $record->hasRole('contractor')
                            ? url('/contractor')
                            : url('/staff')),
                    Tables\Actions\EditAction::make()->icon('heroicon-o-pencil'),
                    Tables\Actions\DeleteAction::make()->icon('heroicon-o-trash'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /** @return array<string, string> */
    public static function getRoleOptions(): array
    {
        $roleModel = config('permission.models.role');

        return $roleModel::query()
            ->whereNotIn('name', ['panel_user', 'admin', 'super_admin'])
            ->orderBy('name')
            ->pluck('name', 'name')
            ->mapWithKeys(fn(string $name): array => [$name => Str::headline($name)])
            ->all();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereKeyNot(auth()->id());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
