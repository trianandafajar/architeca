<?php

namespace Tests\Feature;

use App\Filament\Resources\ProjectResource\Pages\EditProject;
use App\Filament\Resources\ProjectResource\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\ExpensesRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\ProgressUpdatesRelationManager;
use App\Models\Attachment;
use App\Models\Project;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProgressAndExpenseAttachmentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public static function managers(): array
    {
        return [
            'progress' => [ProgressUpdatesRelationManager::class, 'progressUpdates', ['percentage' => 25], 'Progress Update'],
            'expenses' => [ExpensesRelationManager::class, 'expenses', ['amount' => 150, 'category' => 'material'], 'Expense'],
        ];
    }

    #[DataProvider('managers')]
    public function test_attachments_can_be_created_displayed_edited_and_removed(string $manager, string $relationship, array $data, string $source): void
    {
        $project = Project::create(['owner_id' => auth()->id(), 'name' => 'Project A']);
        $component = Livewire::test($manager, ['ownerRecord' => $project, 'pageClass' => EditProject::class])
            ->callTableAction('create', data: [
                ...$data,
                'attachments' => [
                    UploadedFile::fake()->image('site.png'),
                    UploadedFile::fake()->createWithContent('receipt.pdf', '%PDF-1.7 receipt'),
                ],
            ])->assertHasNoTableActionErrors();

        $record = $project->{$relationship}()->sole();
        $attachments = $record->attachments()->get();
        $this->assertCount(2, $attachments);
        foreach ($attachments as $attachment) {
            $this->assertSame($project->id, $attachment->project_id);
            $this->assertSame(auth()->id(), $attachment->user_id);
            $this->assertTrue($attachment->attachable->is($record));
            Storage::disk('local')->assertExists($attachment->file_path);
        }

        $image = $attachments->firstWhere('file_type', 'image/png');
        $pdf = $attachments->firstWhere('file_type', 'application/pdf');
        $this->assertNotNull($image);
        $this->assertNotNull($pdf);
        $column = $component->instance()->getTable()->getColumn('attachments')->record($record);
        $this->assertSame('Attachments', $column->getLabel());
        $this->assertNull($component->instance()->getTable()->getColumn('attachment_files'));
        $this->freezeTime();
        $html = $column->render();
        $this->assertStringContainsString('<img src="'.e(Storage::disk('local')->temporaryUrl($image->file_path, now()->addMinutes(5))).'"', $html);
        $this->assertStringContainsString(basename($pdf->file_path), $html);
        $component->assertSee(basename($pdf->file_path));

        Livewire::test(AttachmentsRelationManager::class, ['ownerRecord' => $project, 'pageClass' => EditProject::class])
            ->assertCanSeeTableRecords($attachments)
            ->assertSee($source.' #'.$record->id);

        $component->mountTableAction('edit', $record);
        $this->assertEqualsCanonicalizing($attachments->pluck('file_path')->all(), array_values($component->get('mountedTableActionsData.0.attachments')));
        $component->callMountedTableAction()->assertHasNoTableActionErrors();
        $this->assertSame($attachments->modelKeys(), $record->attachments()->pluck('id')->all());

        $otherRecord = $record->replicate();
        $otherRecord->save();
        $otherAttachment = $image->replicate();
        $otherRecord->attachments()->save($otherAttachment);

        $component->mountTableAction('edit', $record)
            ->set('mountedTableActionsData.0.attachments', [])
            ->setTableActionData(['attachments' => [$pdf->file_path, UploadedFile::fake()->image('replacement.png')]])
            ->callMountedTableAction()->assertHasNoTableActionErrors();
        $this->assertSame(2, $record->attachments()->count());
        $this->assertFalse($record->attachments()->whereKey($image->id)->exists());
        $this->assertTrue($record->attachments()->whereKey($pdf->id)->exists());

        $component->mountTableAction('edit', $record)
            ->setTableActionData(['attachments' => []])
            ->callMountedTableAction()->assertHasNoTableActionErrors();
        $this->assertSame(0, $record->attachments()->count());
        $this->assertSame($otherAttachment->id, $otherRecord->attachments()->sole()->id);
    }

    #[DataProvider('managers')]
    public function test_invalid_uploads_are_rejected(string $manager, string $relationship, array $data, string $source): void
    {
        $project = Project::create(['owner_id' => auth()->id(), 'name' => 'Project A']);

        foreach ([
            UploadedFile::fake()->createWithContent('note.txt', 'Not an accepted file type'),
            UploadedFile::fake()->image('large.png')->size(10241),
        ] as $file) {
            Livewire::test($manager, ['ownerRecord' => $project, 'pageClass' => EditProject::class])
                ->callTableAction('create', data: [...$data, 'attachments' => [$file]])
                ->assertHasTableActionErrors();
        }

        $this->assertSame(0, $project->{$relationship}()->count());
        $this->assertSame(0, Attachment::count());
    }
}
