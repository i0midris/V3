<?php

namespace X\LaravelMenus;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class MenusServiceProvider extends ServiceProvider implements \Illuminate\Contracts\Support\DeferrableProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        $this->registerNamespaces();
        $this->registerMenusFile();
    }

    /**
     * Require the menus file if that file is exists.
     */
    public function registerMenusFile(): void
    {
        if (file_exists($file = app_path('Support/menus.php'))) {
            require $file;
        }
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->registerHtmlPackage();

        $this->app->singleton('menus', function (Application $app): \X\LaravelMenus\Menu {
            return new Menu($app['view'], $app['config']);
        });

    }

    /**
     * Register "iluminate/html" package.
     */
    private function registerHtmlPackage(): void
    {
        $this->app->register('Collective\Html\HtmlServiceProvider');

        $aliases = [
            'HTML' => 'Collective\Html\HtmlFacade',
            'Form' => 'Collective\Html\FormFacade',
        ];

        AliasLoader::getInstance($aliases)->register();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['menus'];
    }

    /**
     * Register package's namespaces.
     */
    protected function registerNamespaces()
    {
        $configPath = __DIR__.'/../config/config.php';
        $viewsPath = __DIR__.'/../views';
        $this->mergeConfigFrom($configPath, 'menus');
        $this->loadViewsFrom($viewsPath, 'menus');

        $this->publishes([
            $configPath => config_path('menus.php'),
        ], 'config');

        $this->publishes([
            $viewsPath => base_path('resources/views/vendor/x/laravel-menus'),
        ], 'views');
    }
}
