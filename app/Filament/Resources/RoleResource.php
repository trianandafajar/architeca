<?php

namespace App\Filament\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Forms\ShieldSelectAllToggle;
use App\Filament\Resources\RoleResource\Pages;
use BezhanSalleh\FilamentShield\Support\Utils;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Database\Eloquent\Model;

class RoleResource extends Resource implements HasShieldPermissions
{
    use HasShieldFormComponents;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = 'Settings';


    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('filament-shield::filament-shield.field.name'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('guard_name')
                                    ->label(__('filament-shield::filament-shield.field.guard_name'))
                                    ->default(Utils::getFilamentAuthGuard())
                                    ->nullable()
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->maxLength(255),

                                Forms\Components\Select::make(config('permission.column_names.team_foreign_key'))
                                    ->label(__('filament-shield::filament-shield.field.team'))
                                    ->placeholder(__('filament-shield::filament-shield.field.team.placeholder'))
                                    /** @phpstan-ignore-next-line */
                                    ->default([Filament::getTenant()?->id])
                                    ->options(fn (): Arrayable => Utils::getTenantModel() ? Utils::getTenantModel()::pluck('name', 'id') : collect())
                                    ->hidden(fn (): bool => ! (static::shield()->isCentralApp() && Utils::isTenancyEnabled()))
                                    ->dehydrated(fn (): bool => ! (static::shield()->isCentralApp() && Utils::isTenancyEnabled())),
                                ShieldSelectAllToggle::make('select_all')
                                    ->onIcon('heroicon-s-shield-check')
                                    ->offIcon('heroicon-s-shield-exclamation')
                                    ->label(__('filament-shield::filament-shield.field.select_all.name'))
                                    ->helperText(fn (): HtmlString => new HtmlString(__('filament-shield::filament-shield.field.select_all.message')))
                                    ->dehydrated(fn (bool $state): bool => $state),

                            ])
                            ->columns([
                                'sm' => 2,
                                'lg' => 3,
                            ]),
                    ]),
                static::getShieldFormComponents(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->weight('font-medium')
                    ->label(__('filament-shield::filament-shield.column.name'))
                    ->formatStateUsing(fn ($state): string => Str::headline($state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('guard_name')
                    ->badge()
                    ->color('warning')
                    ->label(__('filament-shield::filament-shield.column.guard_name')),
                Tables\Columns\TextColumn::make('team.name')
                    ->default('Global')
                    ->badge()
                    ->color(fn (mixed $state): string => str($state)->contains('Global') ? 'gray' : 'primary')
                    ->label(__('filament-shield::filament-shield.column.team'))
                    ->searchable()
                    ->visible(fn (): bool => static::shield()->isCentralApp() && Utils::isTenancyEnabled()),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->badge()
                    ->label(__('filament-shield::filament-shield.column.permissions'))
                    ->counts('permissions')
                    ->colors(['success']),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('filament-shield::filament-shield.column.updated_at'))
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'view' => Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getCluster(): ?string
    {
        return Utils::getResourceCluster() ?? static::$cluster;
    }

    public static function getModel(): string
    {
        return Utils::getRoleModel();
    }

    public static function getModelLabel(): string
    {
        return __('filament-shield::filament-shield.resource.label.role');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-shield::filament-shield.resource.label.roles');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Utils::isResourceNavigationRegistered();
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-shield::filament-shield.nav.role.label');
    }

    public static function getNavigationIcon(): string
    {
        return __('filament-shield::filament-shield.nav.role.icon');
    }

    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return Utils::getSubNavigationPosition() ?? static::$subNavigationPosition;
    }

    public static function getSlug(): string
    {
        return Utils::getResourceSlug();
    }

    public static function getNavigationBadge(): ?string
    {
        return Utils::isResourceNavigationBadgeEnabled()
            ? strval(static::getEloquentQuery()->count())
            : null;
    }

    protected static function getEditingRoleName(): ?string
    {
        $record = request()->route('record');

        // Livewire update requests use /livewire/update as their route, so
        // the role parameter is not available there. Recover it from the
        // edit page URL that initiated the request.
        if (blank($record)) {
            $refererPath = parse_url((string) request()->header('referer'), PHP_URL_PATH);

            if (is_string($refererPath) && preg_match('#/shield/roles/([^/]+)/edit/?$#', $refererPath, $matches)) {
                $record = urldecode($matches[1]);
            }
        }

        // Keep the context across Livewire requests when the browser does
        // not send a referer header.
        if (blank($record)) {
            $record = session('role_resource.editing_role');
        }

        if ($record instanceof Model) {
            return $record->getAttribute('name');
        }

        if (blank($record)) {
            return null;
        }

        $modelClass = static::getModel();

        /** @var Model $model */
        $model = new $modelClass();

        $roleName = ($model
            ->resolveRouteBinding($record)
            ?? $model->newQuery()->where('name', $record)->first())
            ?->getAttribute('name');

        if (filled($roleName)) {
            session()->put('role_resource.editing_role', $roleName);
        }

        return $roleName;
    }

    protected static function getPanelIdsForEditingRole(): array
    {
        return match (static::getEditingRoleName()) {
            'admin' => ['admin'],
            'contractor' => ['contractor'],
            'staff' => ['staff'],
            'super_admin' => array_keys(Filament::getPanels()),
            'panel_user' => [],

            default => [],
        };
    }

    protected static function getPanelEntityClasses(string $type): array
    {
        return collect(static::getPanelIdsForEditingRole())
            ->flatMap(function (string $panelId) use ($type): array {
                $panel = Filament::getPanel($panelId);

                return match ($type) {
                    'resources' => $panel->getResources(),

                    'pages' => $panel->getPages(),

                    'widgets' => collect($panel->getWidgets())
                        ->map(
                            fn ($widget) => $widget instanceof WidgetConfiguration
                                ? $widget->widget
                                : $widget
                        )
                        ->all(),

                    default => [],
                };
            })
            ->unique()
            ->values()
            ->all();
    }

    protected static function getPanelShieldResources(): array
    {
        $resources = collect(static::getPanelEntityClasses('resources'))
            ->filter(fn (mixed $resource): bool => is_string($resource) && class_exists($resource));

        if (Utils::isGeneralExcludeEnabled()) {
            $resources = $resources->reject(
                fn (string $resource): bool => in_array(
                    Str::of($resource)->afterLast('\\')->toString(),
                    Utils::getExcludedResouces(),
                    true,
                )
            );
        }

        return $resources
            ->map(fn (string $resource): array => [
                'resource' => FilamentShield::getPermissionIdentifier($resource),
                'model' => str($resource::getModel())->afterLast('\\')->toString(),
                'fqcn' => $resource,
            ])
            ->unique('resource')
            ->sortBy('resource')
            ->values()
            ->all();
    }

    protected static function getPanelShieldPages(): array
    {
        $excluded = Utils::getExcludedPages();

        return collect(static::getPanelEntityClasses('pages'))
            ->filter(fn (mixed $page): bool => is_string($page) && class_exists($page))
            ->reject(fn (string $page): bool => Utils::isGeneralExcludeEnabled() && in_array(
                Str::afterLast($page, '\\'),
                $excluded,
                true,
            ))
            ->map(fn (string $page): array => [
                'class' => $page,
                'permission' => Str::of($page)
                    ->afterLast('\\')
                    ->prepend(Utils::getPagePermissionPrefix() . '_')
                    ->toString(),
            ])
            ->unique('permission')
            ->sortBy('permission')
            ->values()
            ->all();
    }

    protected static function getPanelShieldWidgets(): array
    {
        $excluded = Utils::getExcludedWidgets();

        return collect(static::getPanelEntityClasses('widgets'))
            ->filter(fn (mixed $widget): bool => is_string($widget) && class_exists($widget))
            ->reject(fn (string $widget): bool => Utils::isGeneralExcludeEnabled() && in_array(
                Str::afterLast($widget, '\\'),
                $excluded,
                true,
            ))
            ->map(fn (string $widget): array => [
                'class' => $widget,
                'permission' => Str::of($widget)
                    ->afterLast('\\')
                    ->prepend(Utils::getWidgetPermissionPrefix() . '_')
                    ->toString(),
            ])
            ->unique('permission')
            ->sortBy('permission')
            ->values()
            ->all();
    }

    public static function getResourceEntitiesSchema(): ?array
    {
        return collect(static::getPanelShieldResources())
            ->map(function (array $entity): Forms\Components\Section {
                return Forms\Components\Section::make(
                    $entity['model']
                        ?? class_basename($entity['fqcn'])
                )
                    ->description(fn (): HtmlString => new HtmlString(
                        '<span style="word-break: break-word;">' . Utils::showModelPath($entity['fqcn']) . '</span>'
                    ))
                    ->compact()
                    ->schema([
                        static::getCheckBoxListComponentForResource($entity),
                    ])
                    ->columnSpan(static::shield()->getSectionColumnSpan())
                    ->collapsible();
            })
            ->values()
            ->all();
    }

    public static function getResourceTabBadgeCount(): ?int
    {
        return collect(static::getPanelShieldResources())
            ->sum(fn (array $resource): int => count(static::getResourcePermissionOptions($resource)));
    }

    public static function getPageOptions(): array
    {
        return collect(static::getPanelShieldPages())
            ->flatMap(fn (array $page): array => [
                $page['permission'] => static::shield()->hasLocalizedPermissionLabels()
                    ? FilamentShield::getLocalizedPageLabel($page['class'])
                    : $page['permission'],
            ])
            ->toArray();
    }

    public static function getWidgetOptions(): array
    {
        return collect(static::getPanelShieldWidgets())
            ->flatMap(fn (array $widget): array => [
                $widget['permission'] => static::shield()->hasLocalizedPermissionLabels()
                    ? FilamentShield::getLocalizedWidgetLabel($widget['class'])
                    : $widget['permission'],
            ])
            ->toArray();
    }

    public static function isScopedToTenant(): bool
    {
        return Utils::isScopedToTenant();
    }

    public static function canGloballySearch(): bool
    {
        return Utils::isResourceGloballySearchable() && count(static::getGloballySearchableAttributes()) && static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
