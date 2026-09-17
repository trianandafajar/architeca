<?php

namespace Tests\Feature;

use App\Filament\Resources\ProjectResource\Pages\EditProject;
use App\Filament\Resources\ProjectResource\RelationManagers\AttachmentsRelationManager;
use App\Models\Attachment;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectAttachmentsTest extends TestCase
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

    public function test_create_defaults_to_project_self_and_detects_the_image_type(): void
    {
        $project = Project::create(['owner_id' => auth()->id(), 'name' => 'Project A']);
        Livewire::test(AttachmentsRelationManager::class, ['ownerRecord' => $project, 'pageClass' => EditProject::class])
            ->mountTableAction('create')
            ->assertTableActionDataSet(['attachable_type' => $project->getMorphClass(), 'attachable_id' => $project->id])
            ->setTableActionData(['file_path' => UploadedFile::fake()->image('site.png'), 'caption' => 'Site photo'])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $attachment = $project->attachments()->sole();
        $this->assertTrue($attachment->attachable->is($project));
        $this->assertSame('image/png', $attachment->file_type);
        Storage::disk('local')->assertExists($attachment->file_path);
    }

    public function test_create_can_target_each_related_model_and_edit_can_retarget_to_self(): void
    {
        $project = Project::create(['owner_id' => auth()->id(), 'name' => 'Project A']);
        $targets = [
            $project->dailyReports()->create(['user_id' => auth()->id(), 'report_date' => today()]),
            $project->progressUpdates()->create(['user_id' => auth()->id(), 'progress_date' => today()]),
            $project->expenses()->create(['user_id' => auth()->id(), 'expense_date' => today(), 'category' => 'Materials']),
        ];

        foreach ($targets as $target) {
            Livewire::test(AttachmentsRelationManager::class, ['ownerRecord' => $project, 'pageClass' => EditProject::class])
                ->callTableAction('create', data: [
                    'attachable_type' => $target->getMorphClass(),
                    'attachable_id' => $target->id,
                    'file_path' => UploadedFile::fake()->image('site.png'),
                ])->assertHasNoTableActionErrors();
            $this->assertSame(1, $target->attachments()->count());
        }

        $attachment = $targets[0]->attachments()->sole();
        Livewire::test(AttachmentsRelationManager::class, ['ownerRecord' => $project, 'pageClass' => EditProject::class])
            ->mountTableAction('edit', $attachment)
            ->assertTableActionDataSet(['attachable_type' => (new DailyReport)->getMorphClass(), 'attachable_id' => $targets[0]->id])
            ->setTableActionData(['attachable_type' => $project->getMorphClass(), 'attachable_id' => $project->id])
            ->callMountedTableAction()->assertHasNoTableActionErrors();
        $this->assertTrue($attachment->fresh()->attachable->is($project));
    }

    public function test_targets_from_another_project_are_rejected(): void
    {
        $project = Project::create(['owner_id' => auth()->id(), 'name' => 'Project A']);
        $other = Project::create(['owner_id' => auth()->id(), 'name' => 'Project B']);
        $report = $other->dailyReports()->create(['user_id' => auth()->id(), 'report_date' => today()]);

        foreach ([$other, $report] as $target) {
            Livewire::test(AttachmentsRelationManager::class, ['ownerRecord' => $project, 'pageClass' => EditProject::class])
                ->callTableAction('create', data: [
                    'attachable_type' => $target->getMorphClass(),
                    'attachable_id' => $target->id,
                    'file_path' => UploadedFile::fake()->image('site.png'),
                ])->assertHasTableActionErrors(['attachable_id' => 'in']);
        }
        $this->assertSame(0, Attachment::count());
    }

    public function test_table_shows_preview_and_source_for_private_and_legacy_public_files(): void
    {
        $project = Project::create(['owner_id' => auth()->id(), 'name' => 'Project A']);
        $report = $project->dailyReports()->create(['user_id' => auth()->id(), 'report_date' => today()]);
        $path = UploadedFile::fake()->image('site.png')->store('daily-reports', 'local');
        $image = $report->attachments()->create([
            'project_id' => $project->id, 'user_id' => auth()->id(), 'file_path' => $path, 'file_type' => 'image/png',
        ]);
        Storage::disk('public')->put('attachments/old.pdf', '%PDF-1.7 legacy');
        $document = $project->attachments()->create([
            'user_id' => auth()->id(), 'attachable_type' => $project->getMorphClass(), 'attachable_id' => $project->id,
            'file_path' => 'attachments/old.pdf', 'file_type' => 'application/pdf',
        ]);

        $component = Livewire::test(AttachmentsRelationManager::class, ['ownerRecord' => $project, 'pageClass' => EditProject::class])
            ->assertCanSeeTableRecords([$image, $document])
            ->assertSee('Daily Report #'.$report->id)
            ->assertSee('Project (self)');
        $preview = $component->instance()->getTable()->getColumn('preview');
        $preview->record($image);
        $this->assertSame($path, $preview->getState());
        $this->assertSame('local', $preview->getDiskName());
        $this->assertSame('private', $preview->getVisibility());
        $preview->record($document);
        $this->assertNull($preview->getState());
        $this->assertSame('public', $preview->getDiskName());
    }
}
