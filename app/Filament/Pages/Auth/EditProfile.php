<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\Storage;
use Filament\Support\Enums\MaxWidth;

class EditProfile extends BaseEditProfile
{
    protected static string $layout = 'filament-panels::components.layout.index';

    protected static string $view = 'filament.pages.auth.edit-profile';

    public function getHeading(): string
    {
        return 'Profile settings';
    }

    public function getSubheading(): ?string
    {
        return 'Manage your personal information and security preferences.';
    }

    public function getViewData(): array
    {
        $user = auth()->user();

        return [
            'stats' => [
                'projects' => $user->projects()->count(),
                'reports' => $user->dailyReports()->count(),
                'expenses' => $user->expenses()->count(),
            ],
        ];
    }

    protected function getPasswordFormComponent(): \Filament\Forms\Components\TextInput
    {
        return parent::getPasswordFormComponent()
            ->same('passwordConfirmation');
    }

    protected function getPasswordConfirmationFormComponent(): \Filament\Forms\Components\TextInput
    {
        return parent::getPasswordConfirmationFormComponent()
            ->requiredWith('password');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Profile information')
                    ->description('Your name and email address.')
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                    ])
                    ->columns(2),

                Section::make('Security')
                    ->description('Use a long, random password to keep your account secure.')
                    ->schema([
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * Dibuka dari blade: wire:click="mountAction('changeAvatar')"
     */
    public function changeAvatarAction(): Action
    {
        return Action::make('changeAvatar')
            ->modalHeading('Change profile photo')
            ->modalDescription('Drag & drop a photo, then adjust the crop.')
            ->modalWidth('lg')
            ->modalSubmitActionLabel('Save photo')
            ->form([
                FileUpload::make('avatar')
                    ->hiddenLabel()
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048)
                    ->imageEditor()
                    ->circleCropper()
                    ->imageCropAspectRatio('1:1')
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth('512')
                    ->imageResizeTargetHeight('512')
                    ->imagePreviewHeight('260')
                    ->disk('public')
                    ->directory('avatars')
                    ->visibility('public')
                    ->helperText('JPG, PNG, or WEBP. Max 2 MB.')
                    ->required(),
            ])
            ->action(function (array $data): void {
                $user = $this->getUser();

                $this->deleteCurrentAvatar();

                $user->forceFill(['avatar_url' => $data['avatar']])->save();

                Notification::make()
                    ->title('Profile photo updated')
                    ->success()
                    ->send();
            });
    }

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    public function removeAvatarAction(): Action
    {
        return Action::make('removeAvatar')
            ->label('Remove photo')
            ->link()
            ->color('danger')
            ->size('sm')
            ->visible(fn(): bool => filled($this->getUser()->avatar_url))
            ->requiresConfirmation()
            ->modalHeading('Remove profile photo?')
            ->modalDescription('Your avatar will go back to your initial.')
            ->modalSubmitActionLabel('Remove')
            ->action(function (): void {
                $this->deleteCurrentAvatar();

                $this->getUser()->forceFill(['avatar_url' => null])->save();

                Notification::make()
                    ->title('Profile photo removed')
                    ->success()
                    ->send();
            });
    }

    protected function deleteCurrentAvatar(): void
    {
        $path = $this->getUser()->avatar_url;

        if (filled($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
