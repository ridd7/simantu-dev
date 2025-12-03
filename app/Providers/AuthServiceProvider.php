<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        Gate::define('admin', function (User $user) {
            return $user->level_user === 'admin';
        });

        Gate::define('user', function (User $user) {
            return $user->level_user === 'user';
        });

        Gate::define('admin_or_manager', function (User $user) {
            return $user->level_user === 'admin' || $user->manager_akses === 'Y' ;
        });

        Gate::define('admin_or_koordinator_or_manager', function (User $user) {
            return $user->level_user === 'admin' || $user->koordinator_akses === 'Y' || $user->manager_akses === 'Y' ;
        });
    }
}