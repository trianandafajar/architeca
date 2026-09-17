<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers\Concerns;

use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Model;

trait HasProjectAttachments
{
    protected function attachmentsUpload(string $directory): FileUpload
    {
        return FileUpload::make('attachments')
            ->label('Attachments')
            ->multiple()
            ->disk('local')
            ->directory($directory)
            ->visibility('private')
            ->openable()
            ->downloadable()
            ->dehydrated(false)
            ->loadStateFromRelationshipsUsing(function (FileUpload $component, Model $record): void {
                $component->state($record->attachments()->pluck('file_path')->all());
            })
            ->saveRelationshipsUsing(function (FileUpload $component, Model $record): void {
                $paths = array_values($component->getState() ?? []);

                $record->attachments()->whereNotIn('file_path', $paths)->delete();

                foreach ($paths as $path) {
                    $record->attachments()->firstOrCreate(
                        ['file_path' => $path],
                        [
                            'project_id' => $record->project_id,
                            'user_id' => auth()->id(),
                            'file_type' => $component->getDisk()->mimeType($path) ?: null,
                        ],
                    );
                }

                $record->unsetRelation('attachments');
            })
            ->acceptedFileTypes(['image/*', 'video/*', 'application/pdf'])
            ->maxSize(10240)
            ->helperText('You can upload multiple files. Accepted formats: images, videos, PDFs. Max size: 10MB per file.')
            ->columnSpanFull();
    }

    protected function attachmentsColumn(): ViewColumn
    {
        return ViewColumn::make('attachments')
            ->label('Attachments')
            ->view('filament.tables.columns.project-attachments');
    }
}
