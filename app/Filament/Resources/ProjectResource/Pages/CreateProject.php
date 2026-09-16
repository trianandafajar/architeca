<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Get;
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
                ->description('Informasi dasar project dan nilai kontrak.')
                ->icon('heroicon-o-building-office-2')
                ->schema(ProjectResource::getProjectFormSchema()),
            Forms\Components\Wizard\Step::make('Project members')
                ->description('Tambahkan anggota yang terlibat dalam project.')
                ->icon('heroicon-o-user-group')
                ->schema([
                    Forms\Components\Hidden::make('members_skipped')
                        ->default(false),
                    Forms\Components\Repeater::make('members')
                        ->label('Anggota project')
                        ->relationship('members')
                        ->defaultItems(0)
                        ->minItems(fn (Get $get): int => $get('members_skipped') ? 0 : 1)
                        ->validationMessages([
                            'min' => 'Tambahkan minimal satu anggota atau pilih Skip members.',
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
                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('skip_members')
                            ->label('Skip members')
                            ->icon('heroicon-o-forward')
                            ->color('gray')
                            ->action(function ($livewire): void {
                                $livewire->data['members_skipped'] = true;
                                $livewire->dispatchFormEvent('wizard::nextStep', 'data', 1);
                            }),
                    ])
                        ->alignEnd(),
                ]),
        ];
    }
}
