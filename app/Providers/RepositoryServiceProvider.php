<?php

namespace App\Providers;

use App\Contracts\Repositories\AuthRepositoryInterface;
use App\Repositories\AuthRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        AuthRepositoryInterface::class => AuthRepository::class
    ];
}
