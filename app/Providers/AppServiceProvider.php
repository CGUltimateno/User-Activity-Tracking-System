<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\ModelActivityObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $modelsPath = app_path('Models');
        if (is_dir($modelsPath)) {
            foreach (glob($modelsPath . '/*.php') as $file) {
                $class = 'App\\Models\\' . basename($file, '.php');

                if (! class_exists($class)) {
                    continue;
                }

                if ($class === \App\Models\ActivityLog::class) {
                    continue;
                }

                try {
                    $class::observe(ModelActivityObserver::class);
                } catch (\Throwable $e) {
                    continue;
                }
            }
        }
    }
}
