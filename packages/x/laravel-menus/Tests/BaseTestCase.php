<?php

namespace X\LaravelMenus\Tests;

use Collective\Html\HtmlServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use X\LaravelMenus\MenusServiceProvider;

abstract class BaseTestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [
            HtmlServiceProvider::class,
            MenusServiceProvider::class,
        ];
    }

    /**
     * Set up the environment.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('menus', [
            'styles' => [
                'navbar' => \X\LaravelMenus\Presenters\Bootstrap\NavbarPresenter::class,
                'navbar-right' => \X\LaravelMenus\Presenters\Bootstrap\NavbarRightPresenter::class,
                'nav-pills' => \X\LaravelMenus\Presenters\Bootstrap\NavPillsPresenter::class,
                'nav-tab' => \X\LaravelMenus\Presenters\Bootstrap\NavTabPresenter::class,
                'sidebar' => \X\LaravelMenus\Presenters\Bootstrap\SidebarMenuPresenter::class,
                'navmenu' => \X\LaravelMenus\Presenters\Bootstrap\NavMenuPresenter::class,
            ],

            'ordering' => false,
        ]);
    }
}
