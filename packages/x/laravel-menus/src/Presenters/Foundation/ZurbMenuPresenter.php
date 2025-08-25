<?php

namespace X\LaravelMenus\Presenters\Foundation;

use X\LaravelMenus\Presenters\Presenter;

class ZurbMenuPresenter extends Presenter
{
    /**
     * {@inheritdoc}
     */
    public function getOpenTagWrapper(): string
    {
        return PHP_EOL.'<nav class="custom-main">
        <ul class="dropdown menu" data-dropdown-menu>'.PHP_EOL;
    }

    /**
     * {@inheritdoc}
     */
    public function getCloseTagWrapper(): string
    {
        return PHP_EOL.'</ul></nav>'.PHP_EOL;
    }

    /**
     * {@inheritdoc}
     */
    public function getMenuWithoutDropdownWrapper($item): string
    {
        return '<li'.$this->getActiveState($item).'><a href="'.$item->getUrl().'">'.$item->title.'</a></li>';
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveState($item): ?string
    {
        return \Request::is($item->getRequest()) ? ' class="is-active"' : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getDividerWrapper(): string
    {
        return '<li class="divider"></li>';
    }

    /**
     * {@inheritdoc}
     */
    public function getMenuWithDropDownWrapper($item): string
    {
        return '<li class="dropdown dropdown-primary">
                    <a class="dropdown-toggle" href="#">'.$item->title.'</a>
                    <ul class="menu">
                      '.$this->getChildMenuItems($item).'
                    </ul>
                </li>'.PHP_EOL;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiLevelDropdownWrapper($item): string
    {
        return '<li>
                  <a href="#">'.$item->title.'</a>
                  <ul class="menu">
                    '.$this->getChildMenuItems($item).'
                  </ul>
                </li>'.PHP_EOL;
    }
}
