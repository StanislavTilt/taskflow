<?php

namespace App\Jobs;

use App\Enums\ImportReportStatus;
use App\Models\ImportReport;
use App\Models\Project;
use App\Models\Task;
use App\Services\Import\TaskImportService;
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
    {}

    /**
     * Execute the job.
     */
    public function handle(
        TaskImportService $importer
    ): void
    {
        $this->report->update(['status' => ImportReportStatus::Pending]);

        $csv = Storage::get($this->report->file_path);
        collect($importer->buildRows($csv, $this->project, $this->report->user_id))
            ->chunk(500)
            ->each(fn($chunk) =>
                Task::insert($chunk->all())
            );
        $this->report->update(['status' => ImportReportStatus::Success]);
    }

}
