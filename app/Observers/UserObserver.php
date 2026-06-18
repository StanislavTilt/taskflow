<?php

namespace App\Observers;

use App\Jobs\MailUserJob;
use App\Mail\WelcomeMail;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        MailUserJob::dispatch($user, new WelcomeMail($user))->afterCommit();
    }

}
