<?php

namespace AreiaLab\SlugUid;

use Illuminate\Support\ServiceProvider;

class SlugUidServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/sluguid.php' => config_path('sluguid.php'),
        ], 'sluguid-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\RegenerateCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sluguid.php', 'sluguid');

        $this->app->singleton('sluguid', function ($app) {
            return new SlugUidManager();
        });
    }
}
