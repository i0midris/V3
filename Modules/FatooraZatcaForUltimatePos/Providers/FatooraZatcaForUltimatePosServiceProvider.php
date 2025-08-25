<?php

namespace Modules\FatooraZatcaForUltimatePos\Providers;

use App\Utils\TransactionUtil;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\ServiceProvider;
use Modules\FatooraZatcaForUltimatePos\Utils\ZatcaTransactionUtil;

class FatooraZatcaForUltimatePosServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->overrideTransactionCreatingEvent();
        $this->app->bind(TransactionUtil::class, ZatcaTransactionUtil::class);
        // if(config('fatoorazatcaforultimatepos.auto_reporting')) {
        //     Event::listen(SellCreatedOrModified::class, SellCreatedListener::class);
        // }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('fatoorazatcaforultimatepos.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'fatoorazatcaforultimatepos'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/fatoorazatcaforultimatepos');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path.'/modules/fatoorazatcaforultimatepos';
        }, \Config::get('view.paths')), [$sourcePath]), 'fatoorazatcaforultimatepos');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/fatoorazatcaforultimatepos');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'fatoorazatcaforultimatepos');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'fatoorazatcaforultimatepos');
        }
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
        if (! app()->environment('production') && $this->app->runningInConsole()) {
            app(Factory::class)->load(__DIR__.'/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function overrideTransactionCreatingEvent(): void
    {
        \App\Transaction::creating(function ($transaction): void {
            // Generate a dynamic UUID using your custom logic
            if (empty($transaction->uuid)) {
                $transaction->uuid = (string) \Str::uuid();
            }
        });
    }
}
