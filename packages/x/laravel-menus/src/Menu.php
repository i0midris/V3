<?php

namespace X\LaravelMenus;

use Closure;
use Countable;
use Illuminate\Contracts\Config\Repository;
use Illuminate\View\Factory;

class Menu implements Countable
{
    /**
     * The menus collections.
     *
     * @var array
     */
    protected $menus = [];

    private \Illuminate\Contracts\Config\Repository $config;

    private \Illuminate\View\Factory $views;

    /**
     * The constructor.
     */
    public function __construct(Factory $views, Repository $config)
    {
        $this->views = $views;
        $this->config = $config;
    }

    /**
     * Make new menu.
     *
     * @param  string  $name
     * @return \X\LaravelMenus\MenuBuilder
     */
    public function make($name, \Closure $callback)
    {
        return $this->create($name, $callback);
    }

    /**
     * Create new menu.
     *
     * @param  string  $name
     * @param  callable  $resolver
     * @return \X\LaravelMenus\MenuBuilder
     */
    public function create($name, Closure $resolver)
    {
        $builder = new MenuBuilder($name, $this->config);

        $builder->setViewFactory($this->views);

        $this->menus[$name] = $builder;

        return $resolver($builder);
    }

    /**
     * Check if the menu exists.
     *
     * @param  string  $name
     */
    public function has($name): bool
    {
        return array_key_exists($name, $this->menus);
    }

    /**
     * Get instance of the given menu if exists.
     *
     * @param  string  $name
     * @return string|null
     */
    public function instance($name)
    {
        return $this->has($name) ? $this->menus[$name] : null;
    }

    /**
     * Modify a specific menu.
     *
     * @param  string  $name
     */
    public function modify($name, Closure $callback): void
    {
        $menu = collect($this->menus)->filter(function ($menu) use ($name): bool {
            return $menu->getName() == $name;
        })->first();

        $callback($menu);
    }

    /**
     * Render the menu tag by given name.
     *
     * @param  string  $name
     * @param  string  $presenter
     * @return string|null
     */
    public function get($name, $presenter = null, $bindings = [])
    {
        return $this->has($name) ?
            $this->menus[$name]->setBindings($bindings)->render($presenter) : null;
    }

    /**
     * Render the menu tag by given name.
     *
     *
     * @return string
     */
    public function render($name, $presenter = null, $bindings = [])
    {
        return $this->get($name, $presenter, $bindings);
    }

    /**
     * Get a stylesheet for enable multilevel menu.
     *
     * @return mixed
     */
    public function style()
    {
        return $this->views->make('menus::style')->render();
    }

    /**
     * Get all menus.
     *
     * @return array
     */
    public function all()
    {
        return $this->menus;
    }

    /**
     * Get count from all menus.
     */
    public function count(): int
    {
        return count($this->menus);
    }

    /**
     * Empty the current menus.
     */
    public function destroy(): void
    {
        $this->menus = [];
    }
}
