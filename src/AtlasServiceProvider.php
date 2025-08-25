<?php

namespace Atlas\Laravel;

use Atlas\Laravel\Console\Commands\ExportEnumsCommand;
use Atlas\Laravel\Console\Commands\SyncDocsCommand;
use Illuminate\Support\ServiceProvider;

class AtlasServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/atlas_enums.php', 'atlas_enums');
        $this->mergeConfigFrom(__DIR__.'/../config/atlas_docs.php', 'atlas_docs');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/atlas_enums.php' => config_path('atlas_enums.php'),
            __DIR__.'/../config/atlas_docs.php' => config_path('atlas_docs.php'),
        ], 'atlas-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ExportEnumsCommand::class,
                SyncDocsCommand::class,
            ]);
        }
    }
}
