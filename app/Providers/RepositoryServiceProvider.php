<?php

namespace App\Providers;

use App\Contracts\Repositories\ProjectRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\ProjectRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        UserRepositoryInterface::class => UserRepository::class,
        ProjectRepositoryInterface::class => ProjectRepository::class,
    ];
}
