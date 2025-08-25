<?php

namespace X\LaravelMenus\Presenters\Bootstrap;

class NavTabPresenter extends NavbarPresenter
{
    /**
     * {@inheritdoc}.
     */
    public function getOpenTagWrapper(): string
    {
        return PHP_EOL.'<ul class="nav nav-tabs">'.PHP_EOL;
    }
}
