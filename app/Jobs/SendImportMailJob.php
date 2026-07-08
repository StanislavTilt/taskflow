<?php

namespace App\Jobs;

use App\Mail\ImportReadyMail;
use App\Models\ImportReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendImportMailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly ImportReport $report,
    )
    {
        $user = $this->report->user;
        if(!$user){
            Log::warning("SendReportEmail: user missing for report {$this->report->id}");
            return;
        }

        Mail::to($user->email)->send(new ImportReadyMail($user));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
