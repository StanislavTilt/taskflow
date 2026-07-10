<?php

namespace Tests\Feature\Tasks;

use App\Jobs\ProcessCsvReportJob;
use App\Jobs\SendImportMailJob;
use App\Models\Project;
use App\Models\User;
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
}
