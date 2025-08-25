<?php

use App\Http\AdminlteCustomPresenter;
use X\LaravelMenus\Presenters\Admin\AdminltePresenter;
use X\LaravelMenus\Presenters\Bootstrap\NavbarPresenter;
use X\LaravelMenus\Presenters\Bootstrap\NavbarRightPresenter;
use X\LaravelMenus\Presenters\Bootstrap\NavMenuPresenter;
use X\LaravelMenus\Presenters\Bootstrap\NavPillsPresenter;
use X\LaravelMenus\Presenters\Bootstrap\NavTabPresenter;
use X\LaravelMenus\Presenters\Bootstrap\SidebarMenuPresenter;
use X\LaravelMenus\Presenters\Foundation\ZurbMenuPresenter;

return [

    'styles' => [
        'navbar' => NavbarPresenter::class,
        'navbar-right' => NavbarRightPresenter::class,
        'nav-pills' => NavPillsPresenter::class,
        'nav-tab' => NavTabPresenter::class,
        'sidebar' => SidebarMenuPresenter::class,
        'navmenu' => NavMenuPresenter::class,
        'adminlte' => AdminltePresenter::class,
        'zurbmenu' => ZurbMenuPresenter::class,
        'adminltecustom' => AdminlteCustomPresenter::class,
    ],

    'ordering' => true,

];
