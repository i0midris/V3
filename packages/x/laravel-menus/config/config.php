<?php

return [

    'styles' => [
        'navbar' => \X\LaravelMenus\Presenters\Bootstrap\NavbarPresenter::class,
        'navbar-right' => \X\LaravelMenus\Presenters\Bootstrap\NavbarRightPresenter::class,
        'nav-pills' => \X\LaravelMenus\Presenters\Bootstrap\NavPillsPresenter::class,
        'nav-tab' => \X\LaravelMenus\Presenters\Bootstrap\NavTabPresenter::class,
        'sidebar' => \X\LaravelMenus\Presenters\Bootstrap\SidebarMenuPresenter::class,
        'navmenu' => \X\LaravelMenus\Presenters\Bootstrap\NavMenuPresenter::class,
        'adminlte' => \X\LaravelMenus\Presenters\Admin\AdminltePresenter::class,
        'zurbmenu' => \X\LaravelMenus\Presenters\Foundation\ZurbMenuPresenter::class,
    ],

    'ordering' => false,

];
