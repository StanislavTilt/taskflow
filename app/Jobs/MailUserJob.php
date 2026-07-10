<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class MailUserJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 60;
    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly User $user,
        private readonly Mailable $mail,
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user)->send($this->mail);
    }
}
