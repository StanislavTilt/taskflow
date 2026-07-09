<?php

namespace App\Jobs;

use App\Enums\ImportReportStatus;
use App\Enums\ProjectStatus;
use App\Models\ImportReport;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessCsvReportJob implements ShouldQueue
{
    use Queueable, SerializesModels, InteractsWithQueue, Dispatchable;

    public $tries = 3;
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly ImportReport $report,
        private readonly Project $project,
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $rows = $this->parseFile();

        collect($rows)->chunk(500)->each(function ($chunk) {
            Task::insert(
                $chunk->map(fn ($task) => [
                    'project_id' => $this->project->id,
                    'name' => $task['name'],
                    'description' => $task['description'],
                    'status' => $task['status'],
                    'created_by_id' => $this->report->user_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->toArray()
            );
        });
        $this->report->update(['status' => ImportReportStatus::Success]);
    }

    private function parseFile()
    {
        $this->report->update(['status' => ImportReportStatus::Pending]);

        $csv = Storage::get($this->report->file_path);
        $rows = array_filter(explode("\n", $csv));

        $header = array_filter(str_getcsv(array_shift($rows), ';'));

        $results = [];
        foreach ($rows as $row) {
            $row = array_filter(str_getcsv($row, ';'));
            if(count($row) !== count($header)) continue;
            $record = array_combine($header, $row);
            $results[] = $this->processRecord($record);
        }
        return $results;
    }

    private function processRecord($record){
        if(in_array($record['status'], ProjectStatus::cases()))
        {
            return $record;
        }
        $record['status'] = ProjectStatus::Active;
        return $record;
    }
}
