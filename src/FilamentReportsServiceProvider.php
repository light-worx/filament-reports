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

        // Handle wildcard paths (for modules)
        if (str_contains($path, '*')) {
            $this->registerReportsFromWildcardPath($path, $baseNamespace);
            return;
        }

        // Handle regular paths
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
        // Extract the base path and the pattern
        $parts = explode('*', $wildcardPath);
        $basePath = $parts[0];
        $suffix = $parts[1] ?? '';

        if (!is_dir($basePath)) {
            return;
        }

        // Get all directories matching the pattern
        $directories = glob($wildcardPath, GLOB_ONLYDIR);

        foreach ($directories as $directory) {
            // Extract module name from the path
            // For path like "Modules/Worship/app/Reports", we want "Worship"
            $pathAfterBase = str_replace($basePath, '', $directory);
            $pathParts = explode(DIRECTORY_SEPARATOR, trim($pathAfterBase, DIRECTORY_SEPARATOR));
            $moduleName = $pathParts[0] ?? '';
            
            // Replace {module} placeholder in namespace
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
        // Get the relative path from the base directory
        $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $file->getPathname());

        // Convert path to namespace (handle subdirectories)
        $namespace = $baseNamespace . '\\' . str_replace(
            ['/', '.php'],
            ['\\', ''],
            $relativePath
        );

        // Debug logging (remove after testing)
        \Log::info('Attempting to register report', [
            'file' => $file->getPathname(),
            'computed_namespace' => $namespace,
            'class_exists' => class_exists($namespace),
            'has_routes_method' => class_exists($namespace) ? method_exists($namespace, 'routes') : false,
        ]);

        if (class_exists($namespace) && method_exists($namespace, 'routes')) {
            \Log::info('Calling routes() for: ' . $namespace);
            $namespace::routes();
        }
    }
}