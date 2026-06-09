<?php

namespace App\Observers;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class UserObserver
{
    public function created(User $user): void
    {
        Mail::to($user)->send(new WelcomeMail($user));
    }

}
