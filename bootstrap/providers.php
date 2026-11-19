<?php

return [
    App\Providers\AppServiceProvider::class,

    // Modul (Modular Monolith)
    App\Modules\Catalog\Providers\CatalogServiceProvider::class,
    App\Modules\Membership\Providers\MembershipServiceProvider::class,
    App\Modules\Circulation\Providers\CirculationServiceProvider::class,
];
