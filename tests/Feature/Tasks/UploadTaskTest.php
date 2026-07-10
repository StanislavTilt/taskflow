<?php

namespace Tests\Feature\Tasks;

use App\Enums\ImportReportStatus;
use App\Enums\ProjectStatus;
use App\Jobs\ProcessCsvReportJob;
use App\Jobs\SendImportMailJob;
use App\Models\ImportReport;
use App\Models\Project;
use App\Models\User;
use App\Services\CsvParser;
use App\Services\Import\TaskImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_dispatches_job(): void
    {
        Bus::fake();
        Storage::fake('local');

        $user    = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $csv = UploadedFile::fake()->createWithContent(
            'tasks.csv',
            "name;description;status\nname;description;active\nname;description;archived"
        );

        $response = $this->actingAs($user)
            ->postJson('api/tasks/import/'.$project->id,['file' => $csv]);

        $response->assertStatus(200);

        Storage::disk('local')->assertExists('imports/' . $csv->hashName());

        Bus::assertChained([
            ProcessCsvReportJob::class,
            SendImportMailJob::class
        ]);
    }

    public function test_upload_csv_dispatches_job_with_correct_report()
    {
        Bus::fake();
        Storage::fake('local');

        $user    = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $csv = UploadedFile::fake()->createWithContent(
            'tasks.csv',
            "name;description;status\nname;description;active\nname;description;archived"
        );

        $this->actingAs($user)
            ->postJson('api/tasks/import/'.$project->id,['file' => $csv]);

        Bus::assertChained([
            fn (ProcessCsvReportJob $job) =>
                $job->report->user_id === $user->id
                && $job->report->status === ImportReportStatus::Pending
                && $job->project->id === $project->id,
            SendImportMailJob::class
        ]);
    }

    public function test_user_gets_validation_error_without_file()
    {
        Bus::fake();
        Storage::fake('local');

        $user    = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)
            ->postJson('api/tasks/import/'.$project->id,[]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('file');
    }

    public function test_user_gets_validation_error_with_incorrect_file()
    {
        Bus::fake();
        Storage::fake('local');

        $user    = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $csv = UploadedFile::fake()->createWithContent(
            'tasks.pdf', 'wrong data'
        );

        $response = $this->actingAs($user)
            ->postJson('api/tasks/import/'.$project->id,['file' => $csv]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('file');
    }

    public function test_job_insert_rows_to_database()
    {
        Storage::fake('local');

        $user    = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $file_path = 'imports/import.csv';
        $report = ImportReport::create(
            [
                'user_id' => $user->id,
                'file_path' => $file_path,
                'status' => ImportReportStatus::Pending
            ]
        );

        $csvContent = "name;description;status\nname;description;active\nname1;description1;archived";
        Storage::put($file_path, $csvContent);

        $job = new ProcessCsvReportJob($report, $project);
        $job->handle(new TaskImportService(new CsvParser()));

        $this->assertDatabaseCount('tasks', 2);

        $this->assertDatabaseHas('tasks', [
            'created_by_id' => $user->id,
            'name' => 'name',
            'project_id' => $project->id,
            'description' => 'description',
            'status' => ProjectStatus::Active,
        ]);

        $this->assertDatabaseHas('tasks', [
            'created_by_id' => $user->id,
            'name' => 'name1',
            'project_id' => $project->id,
            'description' => 'description1',
            'status' => ProjectStatus::Archived,
        ]);
    }

    public function test_job_skip_wrong_rows()
    {
        Storage::fake('local');

        $user    = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $file_path = 'imports/import.csv';
        $report = ImportReport::create(
            [
                'user_id' => $user->id,
                'file_path' => $file_path,
                'status' => ImportReportStatus::Pending
            ]
        );

        $csvContent = "name;description;status\nname;description;active\nname1;description1;archived\nwrong;wrong;\nwrong;";
        Storage::put($file_path, $csvContent);

        $job = new ProcessCsvReportJob($report, $project);
        $job->handle(new TaskImportService(new CsvParser()));

        $this->assertDatabaseCount('tasks', 2);
    }
}
