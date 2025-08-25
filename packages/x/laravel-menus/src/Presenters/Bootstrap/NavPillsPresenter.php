<?php

namespace X\LaravelMenus\Presenters\Bootstrap;

class NavPillsPresenter extends NavbarPresenter
{
    /**
     * {@inheritdoc}.
     */
    public function getOpenTagWrapper(): string
    {
        return PHP_EOL.'<ul class="nav nav-pills">'.PHP_EOL;
    }
}
