<?php

namespace App\Modules\Membership\Providers;

use App\Modules\Membership\Contracts\MemberEligibilityInterface;
use App\Modules\Membership\Services\MemberEligibilityService;
use Illuminate\Support\ServiceProvider;

class MembershipServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MemberEligibilityInterface::class, MemberEligibilityService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'membership');
    }
}
