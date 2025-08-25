<?php

namespace X\LaravelMenus\Tests;

use X\LaravelMenus\Menu;
use X\LaravelMenus\MenuBuilder;

class MenuTest extends BaseTestCase
{
    /**
     * @var Menu
     */
    private $menu;

    protected function setUp(): void
    {
        parent::setUp();
        $this->menu = app(Menu::class);
    }

    /** @test */
    public function it_generates_an_empty_menu(): void
    {
        $this->menu->create('test', function (MenuBuilder $menu): void {});

        $expected = <<<'TEXT'

<ul class="nav navbar-nav">

</ul>

TEXT;

        self::assertEquals($expected, $this->menu->get('test'));
    }

    /** @test */
    public function it_makes_is_an_alias_for_create(): void
    {
        $this->menu->make('test', function (MenuBuilder $menu): void {});

        $expected = <<<'TEXT'

<ul class="nav navbar-nav">

</ul>

TEXT;

        self::assertEquals($expected, $this->menu->get('test'));
    }

    /** @test */
    public function it_render_is_an_alias_of_get(): void
    {
        $this->menu->make('test', function (MenuBuilder $menu): void {});

        $expected = <<<'TEXT'

<ul class="nav navbar-nav">

</ul>

TEXT;

        self::assertEquals($expected, $this->menu->render('test'));
    }

    /** @test */
    public function it_can_get_the_instance_of_a_menu(): void
    {
        $this->menu->create('test', function (MenuBuilder $menu): void {});

        $this->assertInstanceOf(MenuBuilder::class, $this->menu->instance('test'));
    }

    /** @test */
    public function it_can_modify_a_menu_instance(): void
    {
        $this->menu->create('test', function (MenuBuilder $menu): void {});

        $this->menu->modify('test', function (MenuBuilder $builder): void {
            $builder->url('hello', 'world');
        });

        $this->assertCount(1, $this->menu->instance('test'));
    }

    /** @test */
    public function it_gets_a_partial_for_dropdown_styles(): void
    {
        $this->menu->create('test', function (MenuBuilder $menu): void {});

        $this->assertStringContainsString('.dropdown-submenu', $this->menu->style());
    }

    /** @test */
    public function it_can_get_all_menus(): void
    {
        $this->menu->create('main', function (MenuBuilder $menu): void {});
        $this->menu->create('footer', function (MenuBuilder $menu): void {});

        $this->assertCount(2, $this->menu->all());
    }

    /** @test */
    public function it_can_count_menus(): void
    {
        $this->menu->create('main', function (MenuBuilder $menu): void {});
        $this->menu->create('footer', function (MenuBuilder $menu): void {});

        $this->assertEquals(2, $this->menu->count());
    }

    /** @test */
    public function it_can_destroy_all_menus(): void
    {
        $this->menu->create('main', function (MenuBuilder $menu): void {});
        $this->menu->create('footer', function (MenuBuilder $menu): void {});

        $this->assertCount(2, $this->menu->all());
        $this->menu->destroy();
        $this->assertCount(0, $this->menu->all());
    }
}
