<?php

namespace Lightworx\FilamentReports;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class FilamentReportsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/filament-reports.php', 'filament-reports');
    }

    public function boot(): void
    {
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
        $reportPaths = config('filament-reports.report_paths', []);
        foreach ($reportPaths as $pathConfig) {
            $this->registerReportsFromPath($pathConfig);
        }
    }

    protected function registerReportsFromPath(array $pathConfig): void
    {
        $path = $pathConfig['path'];
        $baseNamespace = $pathConfig['namespace'];
        if (str_contains($path, '*')) {
            $this->registerReportsFromWildcardPath($path, $baseNamespace);
            return;
        }
        if (!is_dir($path)) {
            return;
        }
        $files = File::allFiles($path);
        foreach ($files as $file) {
            $this->registerReportFile($file, $path, $baseNamespace);
        }
    }

    protected function registerReportsFromWildcardPath(string $wildcardPath, string $namespacePattern): void
    {     
        $parts = explode('*', $wildcardPath);
        $basePath = $parts[0];
        $suffix = $parts[1] ?? '';
        if (!is_dir($basePath)) {
            return;
        }
        $directories = glob($wildcardPath, GLOB_ONLYDIR);
        foreach ($directories as $directory) {
            $pathAfterBase = str_replace($basePath, '', $directory);
            $pathParts = explode(DIRECTORY_SEPARATOR, trim($pathAfterBase, DIRECTORY_SEPARATOR));
            $moduleName = $pathParts[0] ?? '';         
            $namespace = str_replace('{module}', $moduleName, $namespacePattern);
            if (is_dir($directory)) {
                $files = File::allFiles($directory);
                foreach ($files as $file) {
                    $this->registerReportFile($file, $directory, $namespace);
                }
            }
        }
    }

    protected function registerReportFile($file, string $basePath, string $baseNamespace): void
    {
        $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
        $namespace = $baseNamespace . '\\' . str_replace(
            ['/', '.php'],
            ['\\', ''],
            $relativePath
        );
        if (class_exists($namespace) && method_exists($namespace, 'routes')) {
            $namespace::routes();
        }
    }

}