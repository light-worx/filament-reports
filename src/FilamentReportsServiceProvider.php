<?php

namespace Lightworx\FilamentReports;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class FilamentReportsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/filament-reports.php', 'filament-reports');
        $this->registerReportRoutes();
        $this->publishes([
            __DIR__ . '/config/filament-reports.php' => config_path('filament-reports.php'),
        ], 'config');
        //$this->loadRoutesFrom(__DIR__.'/Http/routes.php');
        $this->loadMigrationsFrom(__DIR__.'/Database/migrations');
        $this->loadViewsFrom(__DIR__.'/Resources/views', 'filament-reports');
        if (file_exists($file = __DIR__ . '/helpers.php')) {
            require_once $file;
        }
    }

    protected function registerReportRoutes(): void
    {
        $reportPath = app_path('Reports');
        
        if (!is_dir($reportPath)) {
            return;
        }
        
        $files = File::allFiles($reportPath);
        
        foreach ($files as $file) {
            // Get the relative path from the Reports directory
            $relativePath = str_replace($reportPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            
            // Convert path to namespace (handle subdirectories)
            $namespace = 'App\\Reports\\' . str_replace(
                ['/', '.php'],
                ['\\', ''],
                $relativePath
            );
            
            if (class_exists($namespace) && method_exists($namespace, 'routes')) {
                $namespace::routes();
            }
        }
    }
}