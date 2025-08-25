<?php

namespace X\LaravelMenus\Tests;

use Illuminate\Config\Repository;
use X\LaravelMenus\MenuBuilder;
use X\LaravelMenus\MenuItem;

class MenuBuilderTest extends BaseTestCase
{
    /** @test */
    public function it_makes_a_menu_item(): void
    {
        $builder = new MenuBuilder('main', app(Repository::class));

        self::assertInstanceOf(MenuItem::class, $builder->url('hello', 'world'));
    }
}
