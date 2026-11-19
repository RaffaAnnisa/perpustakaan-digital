<?php

namespace App\Modules\Catalog\Providers;

use App\Modules\Catalog\Contracts\BookAvailabilityInterface;
use App\Modules\Catalog\Listeners\RestoreBookStock;
use App\Modules\Catalog\Services\BookAvailabilityService;
use App\Modules\Circulation\Events\BookReturned;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Contract -> implementasi
        $this->app->bind(BookAvailabilityInterface::class, BookAvailabilityService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'catalog');

        // Mendengarkan Domain Event dari modul Circulation
        Event::listen(BookReturned::class, RestoreBookStock::class);
    }
}
