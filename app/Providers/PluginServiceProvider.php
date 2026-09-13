<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PluginServiceProvider extends ServiceProvider {
    /**
     * Register services.
     */
    public function register(): void {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void {
        $pluginsPath = base_path('plugins');

        if (!File::isDirectory($pluginsPath)) {
            return;
        }

        $plugins = File::directories($pluginsPath);

        foreach ($plugins as $pluginPath) {
            $pluginName = basename($pluginPath);
            $this->bootPlugin($pluginName, $pluginPath);
        }
    }

    /**
     * Boot a single plugin.
     */
    protected function bootPlugin(string $name, string $path): void {
        $viewsPath = $path.'/resources/views';
        if (File::isDirectory($viewsPath)) {
            $this->loadViewsFrom($viewsPath, $name);
        }

        $routesPath = $path.'/routes/web.php';
        if (File::exists($routesPath)) {
            Route::middleware('web')
                ->namespace("Plugins\\{$name}\\src\\Controllers")
                ->group($routesPath);
        }

        $adminPath = $path.'/routes/lorekeeper/admin.php';
        if (File::exists($adminPath)) {
            Route::middleware(['web', 'admin'])
                ->group($adminPath);
        }

        $browsePath = $path.'/routes/lorekeeper/browse.php';
        if (File::exists($browsePath)) {
            Route::middleware('browse')
                ->group($browsePath);
        }

        $migrationsPath = $path.'/database/migrations';
        if (File::isDirectory($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }
    }
}
