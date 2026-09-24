<?php

namespace App\Filament\Contractor\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Wizard;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Support\Enums\Alignment;

class CreateProject extends CreateRecord
{
    use HasWizard {
        HasWizard::form as baseWizardForm;
    }

    protected static string $resource = ProjectResource::class;

    public function getFormActionsAlignment(): string|Alignment
    {
        return Alignment::End;
    }

    public function form(Form $form): Form
    {
        $form = $this->baseWizardForm($form);

        foreach ($form->getComponents() as $component) {
            if ($component instanceof Wizard) {
                $component->persistStepInQueryString();
            }
        }

        return $form;
    }

    protected function getWizardNextAction(): Action
    {
        return parent::getWizardNextAction()
            ->extraAttributes([
                'class' => 'disabled:opacity-50 disabled:cursor-not-allowed',
            ]);
    }

    protected function getWizardPreviousAction(): Action
    {
        return parent::getWizardPreviousAction()
            ->extraAttributes([
                'class' => 'disabled:opacity-50 disabled:cursor-not-allowed',
            ]);
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->extraAttributes([
                'class' => 'disabled:opacity-50 disabled:cursor-not-allowed',
            ])
            ->color('primary');
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

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function fillForm(): void
    {
        $this->form->fill(session()->get('contractor_create_project_data', []));
    }

    public function updated($propertyName): void
    {
        session()->put('contractor_create_project_data', $this->form->getState());
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $budgetItems = $data['budgetItems'] ?? [];
        $members = $data['members'] ?? [];

        unset($data['budgetItems'], $data['members']);

        $record = parent::handleRecordCreation($data);

        $record->budgetItems()->createMany($budgetItems);
        $record->members()->createMany($members);

        session()->forget('contractor_create_project_data');

        return $record;
    }

    public function getSteps(): array
    {
        return [
            Forms\Components\Wizard\Step::make('Project details')
                ->description('Basic project information.')
                ->icon('heroicon-o-building-office-2')
                ->schema(ProjectResource::getProjectFormSchema()),
            Forms\Components\Wizard\Step::make('Budget items')
                ->description('Project budget breakdown.')
                ->icon('heroicon-o-calculator')
                ->schema([
                    Forms\Components\Repeater::make('budgetItems')
                        ->label('Budget Items')
                        ->defaultItems(0)
                        ->addActionLabel('Add Budget Item')
                        ->schema([
                            Forms\Components\TextInput::make('item_name')
                                ->label('Item Name')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true),
                            Forms\Components\TextInput::make('unit')
                                ->label('Unit')
                                ->numeric()
                                ->minValue(0)
                                ->extraInputAttributes([
                                    'inputmode' => 'numeric',
                                    'onkeydown' => "return !['e', 'E', '+', '-', '.'].includes(event.key)",
                                ])
                                ->default(0)
                                ->rules(['required', 'numeric', 'min:0'])
                                ->live(onBlur: true),
                            Forms\Components\TextInput::make('unit_price')
                                ->label('Unit Price')
                                ->numeric()
                                ->minValue(0)
                                ->extraInputAttributes([
                                    'min' => 0,
                                    'inputmode' => 'decimal',
                                    'onkeydown' => "return !['e', 'E', '+', '-'].includes(event.key)",
                                ])
                                ->prefix('$')
                                ->default(0)
                                ->rules(['required', 'numeric', 'min:0'])
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(Get $get, Set $set): mixed => $set(
                                    'total_price',
                                    (float) ($get('unit_price') ?? 0) * (float) ($get('unit') ?? 0),
                                )),
                            Forms\Components\TextInput::make('total_price')
                                ->label('Total Price')
                                ->numeric()
                                ->minValue(0)
                                ->extraInputAttributes(['min' => 0])
                                ->prefix('$')
                                ->default(0)
                                ->readOnly()
                                ->dehydrated(),
                        ])
                        ->columns(2),
                    $this->skipWizardStepAction('skip_budget_items', 1),
                ]),
            Forms\Components\Wizard\Step::make('Project members')
                ->description('Members involved in the project.')
                ->icon('heroicon-o-user-group')
                ->schema([
                    Forms\Components\Repeater::make('members')
                        ->label('Project Members')
                        ->defaultItems(0)
                        ->minItems(1)
                        ->validationMessages([
                            'min' => 'At least one project member is required.',
                        ])
                        ->addActionLabel('Add project member')
                        ->itemLabel(fn(array $state): ?string => filled($state['user_id'] ?? null)
                            ? User::find($state['user_id'])?->name
                            : 'New member')
                        ->schema([
                            Forms\Components\Select::make('user_id')
                                ->label('User')
                                ->options(User::query()->orderBy('name')->pluck('name', 'id'))
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Name')
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
                                    Forms\Components\TextInput::make('role')
                                        ->label('Role')
                                        ->default('staff')
                                        ->formatStateUsing(fn(?string $state): string => ucfirst($state ?: 'staff'))
                                        ->disabled()
                                        ->dehydrated()
                                        ->dehydrateStateUsing(fn(): string => 'staff')
                                        ->required(),
                                ])
                                ->createOptionAction(fn(Forms\Components\Actions\Action $action): Forms\Components\Actions\Action => $action
                                    ->modalDescription('Create a new user to be added directly as a project member.')
                                    ->modalFooterActionsAlignment(Alignment::End)
                                    ->extraModalWindowAttributes([
                                        'class' => 'architeca-project-modal',
                                    ]))
                                ->createOptionUsing(fn(array $data): int => User::create($data)->getKey()),
                            Forms\Components\Select::make('role')
                                ->label('Role in Project')
                                ->options([
                                    'supervisor' => 'Contractor',
                                    'worker' => 'Staff',
                                ])
                                ->default('worker')
                                ->required()
                                ->live(),
                        ])
                        ->columns(2),
                ]),
            Forms\Components\Wizard\Step::make('Confirm project')
                ->description('Review the data again.')
                ->icon('heroicon-o-check-circle')
                ->schema([
                    Forms\Components\Placeholder::make('project_summary')
                        ->label('Project')
                        ->content(fn(Get $get): string => $get('name') ?: '-'),
                    Forms\Components\Placeholder::make('related_summary')
                        ->label('Additional Data')
                        ->content(fn(Get $get): string => sprintf(
                            '%d budget item, %d member',
                            count($get('budgetItems') ?? []),
                            count($get('members') ?? []),
                        )),
                    Forms\Components\Placeholder::make('confirmation_note')
                        ->label('Confirmation')
                        ->content('Click Create project to save the project and its related data.'),
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
                ->action(fn($livewire): mixed => $livewire->dispatchFormEvent(
                    'wizard::nextStep',
                    'data',
                    $stepIndex,
                )),
        ])
            ->alignEnd();
    }
}
