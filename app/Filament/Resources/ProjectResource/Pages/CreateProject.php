<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Support\Enums\Alignment;

class CreateProject extends CreateRecord
{
    use HasWizard;

    protected static string $resource = ProjectResource::class;

    public function getSubheading(): ?string
    {
        return 'Buat project baru dan lengkapi informasi dasarnya sebelum mulai dikelola.';
    }

    public function getFormActionsAlignment(): string|Alignment
    {
        return Alignment::End;
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->color('primary');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['owner_id'] ??= auth()->id();

        return $data;
    }

    protected function hasSkippableSteps(): bool
    {
        return false;
    }

    public function getSteps(): array
    {
        return [
            Forms\Components\Wizard\Step::make('Project details')
                ->description('Informasi dasar project.')
                ->icon('heroicon-o-building-office-2')
                ->schema(ProjectResource::getProjectFormSchema()),
            Forms\Components\Wizard\Step::make('Budget items')
                ->description('Rincian anggaran project.')
                ->icon('heroicon-o-calculator')
                ->schema([
                    Forms\Components\Repeater::make('budgetItems')
                        ->label('Rincian anggaran')
                        ->relationship('budgetItems')
                        ->defaultItems(0)
                        ->addActionLabel('Tambah item anggaran')
                        ->schema([
                            Forms\Components\TextInput::make('item_name')
                                ->label('Nama item')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('quantity')
                                ->label('Jumlah')
                                ->numeric()
                                ->default(0)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Get $get, Set $set): mixed => $set(
                                    'total_price',
                                    (float) ($get('quantity') ?? 0) * (float) ($get('unit_price') ?? 0),
                                )),
                            Forms\Components\TextInput::make('unit')
                                ->label('Satuan')
                                ->maxLength(50),
                            Forms\Components\TextInput::make('unit_price')
                                ->label('Harga satuan')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Get $get, Set $set): mixed => $set(
                                    'total_price',
                                    (float) ($get('quantity') ?? 0) * (float) ($get('unit_price') ?? 0),
                                )),
                            Forms\Components\TextInput::make('total_price')
                                ->label('Total harga')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->readOnly()
                                ->dehydrated(),
                            Forms\Components\Textarea::make('notes')
                                ->label('Catatan')
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                    $this->skipWizardStepAction('skip_budget_items', 1),
                ]),
            Forms\Components\Wizard\Step::make('Project members')
                ->description('Anggota yang terlibat dalam project.')
                ->icon('heroicon-o-user-group')
                ->schema([
                    Forms\Components\Repeater::make('members')
                        ->label('Anggota project')
                        ->relationship('members')
                        ->defaultItems(0)
                        ->minItems(1)
                        ->validationMessages([
                            'min' => 'Tambahkan minimal satu anggota sebelum melanjutkan.',
                        ])
                        ->addActionLabel('Tambah anggota')
                        ->itemLabel(fn (array $state): ?string => filled($state['user_id'] ?? null)
                            ? User::find($state['user_id'])?->name
                            : 'Anggota baru')
                        ->schema([
                            Forms\Components\Select::make('user_id')
                                ->label('User')
                                ->options(User::query()->orderBy('name')->pluck('name', 'id'))
                                ->required()
                                ->searchable()
                                ->preload()
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nama')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('email')
                                        ->label('Email')
                                        ->email()
                                        ->required()
                                        ->unique(User::class, 'email'),
                                    Forms\Components\TextInput::make('password')
                                        ->label('Password')
                                        ->password()
                                        ->required()
                                        ->minLength(8),
                                    Forms\Components\Select::make('role')
                                        ->label('Role')
                                        ->options([
                                            'admin' => 'Admin',
                                            'contractor' => 'Contractor',
                                            'staff' => 'Staff',
                                        ])
                                        ->default('staff')
                                        ->required(),
                                ])
                                ->createOptionAction(fn (Forms\Components\Actions\Action $action): Forms\Components\Actions\Action => $action
                                    ->modalDescription('Buat user baru untuk langsung ditambahkan sebagai anggota project.')
                                    ->modalFooterActionsAlignment(Alignment::End)
                                    ->extraModalWindowAttributes([
                                        'class' => 'architeca-project-modal',
                                    ]))
                                ->createOptionUsing(fn (array $data): int => User::create($data)->getKey()),
                            Forms\Components\Select::make('role')
                                ->label('Peran di project')
                                ->options([
                                    'owner' => 'Owner',
                                    'manager' => 'Manager',
                                    'supervisor' => 'Supervisor',
                                    'worker' => 'Worker',
                                    'viewer' => 'Viewer',
                                ])
                                ->default('worker')
                                ->required(),
                        ])
                        ->columns(2),
                ]),
            Forms\Components\Wizard\Step::make('Confirm project')
                ->description('Periksa kembali data.')
                ->icon('heroicon-o-check-circle')
                ->schema([
                    Forms\Components\Placeholder::make('project_summary')
                        ->label('Project')
                        ->content(fn (Get $get): string => $get('name') ?: '-'),
                    Forms\Components\Placeholder::make('related_summary')
                        ->label('Data tambahan')
                        ->content(fn (Get $get): string => sprintf(
                            '%d budget item, %d member',
                            count($get('budgetItems') ?? []),
                            count($get('members') ?? []),
                        )),
                    Forms\Components\Placeholder::make('confirmation_note')
                        ->label('Konfirmasi')
                        ->content('Klik Create project untuk menyimpan project beserta data relasinya.'),
                ]),
        ];
    }

    protected function skipWizardStepAction(string $name, int $stepIndex): Forms\Components\Actions
    {
        return Forms\Components\Actions::make([
            Forms\Components\Actions\Action::make($name)
                ->label('Skip')
                ->icon('heroicon-o-forward')
                ->color('gray')
                ->action(fn ($livewire): mixed => $livewire->dispatchFormEvent(
                    'wizard::nextStep',
                    'data',
                    $stepIndex,
                )),
        ])
            ->alignEnd();
    }
}
