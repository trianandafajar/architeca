<?php

namespace Tests\Feature;

use App\Filament\Resources\ProjectResource\RelationManagers\DailyReportsRelationManager;
use App\Models\Attachment;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\User;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class DailyReportAttachmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_attachments_survive_create_and_edit_and_can_be_removed(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user);
        $project = Project::create(['owner_id' => $user->id, 'name' => 'Test project']);

        Livewire::test(DailyReportAttachmentForm::class, ['projectId' => $project->id])
            ->set('data.attachments', [UploadedFile::fake()->create('report.pdf', 10, 'application/pdf')])
            ->call('save')->assertHasNoErrors();

        $report = DailyReport::firstOrFail();
        $attachment = $report->attachments()->sole();
        $this->assertSame($project->id, $attachment->project_id);
        $this->assertSame($user->id, $attachment->user_id);
        $this->assertSame($report->getMorphClass(), $attachment->attachable_type);
        Storage::disk('local')->assertExists($attachment->file_path);

        $edit = Livewire::test(DailyReportAttachmentForm::class, ['record' => $report]);
        $this->assertSame([$attachment->file_path], array_values($edit->get('data.attachments')));
        $edit->call('save')->assertHasNoErrors();
        $this->assertSame($attachment->id, $report->attachments()->sole()->id);

        $otherReport = $report->replicate();
        $otherReport->save();
        $otherAttachment = $attachment->replicate();
        $otherReport->attachments()->save($otherAttachment);

        $edit->set('data.attachments', [UploadedFile::fake()->create('replacement.pdf', 10, 'application/pdf')])
            ->call('save')->assertHasNoErrors();
        $this->assertNotSame($attachment->id, $report->attachments()->sole()->id);

        $edit->set('data.attachments', [])->call('save')->assertHasNoErrors();
        $this->assertSame(0, $report->attachments()->count());
        $this->assertSame($otherAttachment->id, $otherReport->attachments()->sole()->id);
    }

    public function test_table_displays_images_and_links_for_pdf_and_video(): void
    {
        Storage::fake('local')->buildTemporaryUrlsUsing(
            fn (string $path): string => 'https://example.test/private/'.$path.'?signature=test',
        );
        $report = new DailyReport;
        $report->setRelation('attachments', collect([
            new Attachment(['file_path' => 'photo.jpg', 'file_type' => 'image/jpeg']),
            new Attachment(['file_path' => 'report.pdf', 'file_type' => 'application/pdf']),
            new Attachment(['file_path' => 'video.mp4', 'file_type' => 'video/mp4']),
        ]));

        $html = view('filament.tables.columns.daily-report-attachments', [
            'getRecord' => fn (): DailyReport => $report,
        ])->render();

        $this->assertStringContainsString('<img src="https://example.test/private/photo.jpg?signature=test"', $html);
        $this->assertStringContainsString('href="https://example.test/private/report.pdf?signature=test"', $html);
        $this->assertStringContainsString('href="https://example.test/private/video.mp4?signature=test"', $html);
        $this->assertSame(1, substr_count($html, '<img'));
    }
}

class DailyReportAttachmentForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public ?DailyReport $record = null;

    public ?int $projectId = null;

    public function mount(): void
    {
        $this->form->fill($this->record?->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return (new DailyReportsRelationManager)->form($form)
            ->statePath('data')
            ->model($this->record ?? DailyReport::class);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        if ($this->record) {
            $this->record->update($data);
        } else {
            $this->record = DailyReport::create([...$data, 'project_id' => $this->projectId]);
            $this->form->model($this->record)->saveRelationships();
        }
    }

    public function render(): string
    {
        return '<div></div>';
    }
}
