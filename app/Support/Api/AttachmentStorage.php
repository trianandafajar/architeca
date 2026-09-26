<?php

namespace App\Support\Api;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AttachmentStorage
{
    public function attach(Model $target, Project $project, User $user, UploadedFile $file, ?string $caption = null): Attachment
    {
        $path = $file->store('attachments', 'local');
        $data = [
            'project_id' => $project->id,
            'user_id' => $user->id,
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'caption' => $caption,
        ];

        if ($target instanceof Project) {
            return $target->attachments()->create($data + [
                'attachable_type' => $target->getMorphClass(),
                'attachable_id' => $target->id,
            ]);
        }

        return $target->attachments()->create($data);
    }

    public function delete(Attachment $attachment): void
    {
        $disk = $this->diskFor($attachment);
        if ($disk) {
            Storage::disk($disk)->delete($attachment->file_path);
        }
        $attachment->delete();
    }

    public function diskFor(Attachment $attachment): ?string
    {
        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($attachment->file_path)) {
                return $disk;
            }
        }

        return null;
    }
}
