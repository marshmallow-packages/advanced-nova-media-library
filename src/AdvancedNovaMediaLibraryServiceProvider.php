<?php

namespace Marshmallow\AdvancedNovaMediaLibrary;

use Illuminate\Support\Facades\Route;
use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider;

class AdvancedNovaMediaLibraryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/nova-media-library.php' => config_path('nova-media-library.php'),
        ], 'nova-media-library');

        Nova::serving(function (ServingNova $event) {
            Nova::script('media-lib-images-field', __DIR__ . '/../dist/js/field.js');
        });
    }


    public function register()
    {
        $this->registerRoutes();
    }

    protected function registerRoutes()
    {
        if ($this->app->routesAreCached()) return;

        Route::middleware(['nova'])
            ->namespace('Marshmallow\AdvancedNovaMediaLibrary\Http\Controllers')
            ->prefix('nova-vendor/marshmallow/advanced-nova-media-library')
            ->group(__DIR__ . '/../routes/api.php');
    }
}
