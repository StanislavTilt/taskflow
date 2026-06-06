<?php

namespace App\Providers;

use App\Contracts\Services\AuthServiceInterface;
use App\Services\AuthService;
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{
    public array $bindings = [
        AuthServiceInterface::class => AuthService::class
    ];
}
