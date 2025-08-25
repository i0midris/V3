<?php

namespace X\LaravelMenus\Presenters\Admin;

use X\LaravelMenus\Presenters\Presenter;

class AdminltePresenter extends Presenter
{
    /**
     * {@inheritdoc}.
     */
    public function getOpenTagWrapper(): string
    {
        return PHP_EOL.'<ul class="sidebar-menu tree" data-widget="tree">'.PHP_EOL;
    }

    /**
     * {@inheritdoc}.
     */
    public function getCloseTagWrapper(): string
    {
        return PHP_EOL.'</ul>'.PHP_EOL;
    }

    /**
     * {@inheritdoc}.
     */
    public function getMenuWithoutDropdownWrapper($item): string
    {
        return '<li'.$this->getActiveState($item).'><a href="'.$item->getUrl().'" '.$item->getAttributes().'>'.$item->getIcon().' <span>'.$item->title.'</span></a></li>'.PHP_EOL;
    }

    /**
     * {@inheritdoc}.
     */
    public function getActiveState($item, $state = ' class="active"')
    {
        return $item->isActive() ? $state : null;
    }

    /**
     * Get active state on child items.
     *
     * @param  string  $state
     * @return null|string
     */
    public function getActiveStateOnChild($item, $state = 'active')
    {
        return $item->hasActiveOnChild() ? $state : null;
    }

    /**
     * {@inheritdoc}.
     */
    public function getDividerWrapper(): string
    {
        return '<li class="divider"></li>';
    }

    /**
     * {@inheritdoc}.
     */
    public function getHeaderWrapper($item): string
    {
        return '<li class="header">'.$item->title.'</li>';
    }

    /**
     * {@inheritdoc}.
     */
    public function getMenuWithDropDownWrapper($item): string
    {
        return '<li class="treeview'.$this->getActiveStateOnChild($item, ' active').'">
		          <a href="#">
					'.$item->getIcon().' <span>'.$item->title.'</span>
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
			      </a>
			      <ul class="treeview-menu">
			      	'.$this->getChildMenuItems($item).'
			      </ul>
		      	</li>'
        .PHP_EOL;
    }

    /**
     * Get multilevel menu wrapper.
     *
     * @param  \X\LaravelMenus\MenuItem  $item
     * @return string`
     */
    public function getMultiLevelDropdownWrapper($item): string
    {
        return '<li class="treeview'.$this->getActiveStateOnChild($item, ' active').'">
		          <a href="#">
					'.$item->getIcon().' <span>'.$item->title.'</span>
			      	<span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
			      </a>
			      <ul class="treeview-menu">
			      	'.$this->getChildMenuItems($item).'
			      </ul>
		      	</li>'
        .PHP_EOL;
    }
}
