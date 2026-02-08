<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Base\BaseCrudServiceInterface;
use App\Contracts\Base\BaseRepositoryInterface;
use App\Contracts\Repositories\AddressRepositoryInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\AddressServiceInterface;
use App\Contracts\Services\RoleServiceInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Repositories\AddressRepository;
use App\Repositories\Base\BaseRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Services\AddressService;
use App\Services\Base\BaseCrudService;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Services
        $this->app->bind(abstract: AddressServiceInterface::class, concrete: AddressService::class);
        $this->app->bind(abstract: BaseCrudServiceInterface::class, concrete: BaseCrudService::class);
        $this->app->bind(abstract: UserServiceInterface::class, concrete: UserService::class);
        $this->app->bind(abstract: RoleServiceInterface::class, concrete: RoleService::class);

        // Repositories
        $this->app->bind(abstract: AddressRepositoryInterface::class, concrete: AddressRepository::class);
        $this->app->bind(abstract: BaseRepositoryInterface::class, concrete: BaseRepository::class);
        $this->app->bind(abstract: RoleRepositoryInterface::class, concrete: RoleRepository::class);
        $this->app->bind(abstract: UserRepositoryInterface::class, concrete: UserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
