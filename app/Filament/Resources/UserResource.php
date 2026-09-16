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
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'User Management';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi user')
                    ->description('Kelola identitas, password, dan akses pengguna.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
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
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255)
                            ->helperText(fn (string $operation): ?string => $operation === 'edit'
                                ? 'Kosongkan jika tidak ingin mengubah password.'
                                : null),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Konfirmasi password')
                            ->password()
                            ->same('password')
                            ->dehydrated(false)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->helperText(fn (string $operation): ?string => $operation === 'edit'
                                ? 'Isi hanya jika password ingin diubah.'
                                : null),
                        Forms\Components\Select::make('role')
                            ->label('Role')
                            ->options(fn (): array => static::getRoleOptions())
                            ->visible(fn (?User $record): bool => ! $record?->hasAnyRole(['admin', 'super_admin']))
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
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->state(fn (User $record): string => $record->getRoleNames()
                        ->map(fn (string $role): string => Str::headline($role))
                        ->implode(', '))
                    ->color(fn (User $record): string => match ($record->getRoleNames()->first()) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'contractor' => 'success',
                        'staff' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->options(fn (): array => static::getRoleOptions()),
            ])
            ->actions([
                Impersonate::make()
                    ->color('primary')
                    ->tooltip('Login sebagai user')
                    ->redirectTo(fn (User $record): string => $record->hasRole('contractor')
                        ? url('/contractor')
                        : url('/staff')),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            ->mapWithKeys(fn (string $name): array => [$name => Str::headline($name)])
            ->all();
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
