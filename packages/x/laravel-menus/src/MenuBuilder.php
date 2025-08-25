<?php

namespace X\LaravelMenus;

use Countable;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Arr;
use Illuminate\View\Factory as ViewFactory;

class MenuBuilder implements Countable
{
    /**
     * Menu name.
     *
     * @var string
     */
    protected $menu;

    /**
     * Array menu items.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Default presenter class.
     *
     * @var string
     */
    protected $presenter = Presenters\Bootstrap\NavbarPresenter::class;

    /**
     * Style name for each presenter.
     *
     * @var array
     */
    protected $styles = [];

    /**
     * Prefix URL.
     *
     * @var string|null
     */
    protected $prefixUrl;

    /**
     * The name of view presenter.
     *
     * @var string
     */
    protected $view;

    /**
     * The laravel view factory instance.
     *
     * @var \Illuminate\View\Factory
     */
    protected $views;

    /**
     * Determine whether the ordering feature is enabled or not.
     *
     * @var bool
     */
    protected $ordering = false;

    /**
     * Resolved item binding map.
     *
     * @var array
     */
    protected $bindings = [];

    private \Illuminate\Contracts\Config\Repository $config;

    /**
     * Constructor.
     *
     * @param  string  $menu
     */
    public function __construct($menu, Repository $config)
    {
        $this->menu = $menu;
        $this->config = $config;
    }

    /**
     * Get menu name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->menu;
    }

    /**
     * Find menu item by given its title.
     *
     * @param  string  $title
     * @return mixed
     */
    public function whereTitle($title, ?callable $callback = null)
    {
        $item = $this->findBy('title', $title);

        if (is_callable($callback)) {
            return call_user_func($callback, $item);
        }

        return $item;
    }

    /**
     * Find menu item by given key and value.
     *
     * @param  string  $key
     * @param  string  $value
     * @return \X\LaravelMenus\MenuItem
     */
    public function findBy($key, $value)
    {
        return collect($this->items)->filter(function ($item) use ($key, $value): bool {
            return $item->{$key} == $value;
        })->first();
    }

    /**
     * Set view factory instance.
     *
     *
     * @return $this
     */
    public function setViewFactory(ViewFactory $views): static
    {
        $this->views = $views;

        return $this;
    }

    /**
     * Set view.
     *
     * @param  string  $view
     * @return $this
     */
    public function setView($view): static
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Set Prefix URL.
     *
     * @param  string  $prefixUrl
     * @return $this
     */
    public function setPrefixUrl($prefixUrl): static
    {
        $this->prefixUrl = $prefixUrl;

        return $this;
    }

    /**
     * Set styles.
     */
    public function setStyles(array $styles): void
    {
        $this->styles = $styles;
    }

    /**
     * Set new presenter class.
     *
     * @param  string  $presenter
     */
    public function setPresenter($presenter): void
    {
        $this->presenter = $presenter;
    }

    /**
     * Get presenter instance.
     *
     * @return \X\LaravelMenus\Presenters\PresenterInterface
     */
    public function getPresenter()
    {
        return new $this->presenter;
    }

    /**
     * Set new presenter class by given style name.
     *
     * @param  string  $name
     */
    public function style($name): static
    {
        if ($this->hasStyle($name)) {
            $this->setPresenter($this->getStyle($name));
        }

        return $this;
    }

    /**
     * Determine if the given name in the presenter style.
     */
    public function hasStyle($name): bool
    {
        return array_key_exists($name, $this->getStyles());
    }

    /**
     * Get style aliases.
     *
     * @return mixed
     */
    public function getStyles()
    {
        return $this->styles ?: $this->config->get('menus.styles');
    }

    /**
     * Get the presenter class name by given alias name.
     *
     *
     * @return mixed
     */
    public function getStyle($name)
    {
        $style = $this->getStyles();

        return $style[$name];
    }

    /**
     * Set new presenter class from given alias name.
     */
    public function setPresenterFromStyle($name): void
    {
        $this->setPresenter($this->getStyle($name));
    }

    /**
     * Set the resolved item bindings
     *
     * @return $this
     */
    public function setBindings(array $bindings): static
    {
        $this->bindings = $bindings;

        return $this;
    }

    /**
     * Resolves a key from the bindings array.
     *
     * @param  string|array  $key
     */
    public function resolve($key): string|array|null
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $key[$k] = $this->resolve($v);
            }
        } elseif (is_string($key)) {
            $matches = [];
            preg_match_all('/{[\s]*?([^\s]+)[\s]*?}/i', $key, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                if (array_key_exists($match[1], $this->bindings)) {
                    $key = preg_replace('/'.$match[0].'/', $this->bindings[$match[1]], $key, 1);
                }
            }
        }

        return $key;
    }

    /**
     * Resolves an array of menu items properties.
     *
     * @return void
     */
    protected function resolveItems(array &$items)
    {
        $resolver = function ($property) {
            return $this->resolve($property) ?: $property;
        };

        $totalItems = count($items);
        for ($i = 0; $i < $totalItems; $i++) {
            $items[$i]->fill(array_map($resolver, $items[$i]->getProperties()));
        }
    }

    /**
     * Add new child menu.
     *
     *
     * @return \X\LaravelMenus\MenuItem
     */
    public function add(array $attributes = [])
    {
        $item = MenuItem::make($attributes);

        $this->items[] = $item;

        return $item;
    }

    /**
     * Create new menu with dropdown.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function dropdown($title, \Closure $callback, $order = null, array $attributes = [])
    {
        $properties = ['title' => $title, 'order' => $order, 'attributes' => $attributes];

        if (func_num_args() == 3) {
            $arguments = func_get_args();

            $title = Arr::get($arguments, 0);
            $attributes = Arr::get($arguments, 2);

            $properties = ['title' => $title, 'attributes' => $attributes];
        }

        $item = MenuItem::make($properties);

        call_user_func($callback, $item);

        $this->items[] = $item;

        return $item;
    }

    /**
     * Register new menu item using registered route.
     *
     * @param  array  $parameters
     * @param  array  $attributes
     * @return static
     */
    public function route($route, $title, $parameters = [], $order = null, $attributes = [])
    {
        if (func_num_args() == 4) {
            $arguments = func_get_args();

            return $this->add([
                'route' => [Arr::get($arguments, 0), Arr::get($arguments, 2)],
                'title' => Arr::get($arguments, 1),
                'attributes' => Arr::get($arguments, 3),
            ]);
        }

        $route = [$route, $parameters];

        $item = MenuItem::make(
            ['route' => $route, 'title' => $title, 'parameters' => $parameters, 'attributes' => $attributes, 'order' => $order]
        );

        $this->items[] = $item;

        return $item;
    }

    /**
     * Format URL.
     */
    protected function formatUrl(string $url): string
    {
        $uri = is_null($this->prefixUrl) ? $url : $this->prefixUrl.$url;

        return $uri === '/' ? '/' : ltrim(rtrim($uri, '/'), '/');
    }

    /**
     * Register new menu item using url.
     *
     * @param  array  $attributes
     * @return static
     */
    public function url($url, $title, $order = 0, $attributes = [])
    {
        if (func_num_args() == 3) {
            $arguments = func_get_args();

            return $this->add([
                'url' => $this->formatUrl(Arr::get($arguments, 0)),
                'title' => Arr::get($arguments, 1),
                'attributes' => Arr::get($arguments, 2),
            ]);
        }

        $url = $this->formatUrl($url);

        $item = MenuItem::make(['url' => $url, 'title' => $title, 'order' => $order, 'attributes' => $attributes]);

        $this->items[] = $item;

        return $item;
    }

    /**
     * Add new divider item.
     *
     * @param  int  $order
     * @return \X\LaravelMenus\MenuItem
     */
    public function addDivider($order = null): static
    {
        $this->items[] = new MenuItem(['name' => 'divider', 'order' => $order]);

        return $this;
    }

    /**
     * Add new header item.
     *
     * @return \X\LaravelMenus\MenuItem
     */
    public function addHeader($title, $order = null): static
    {
        $this->items[] = new MenuItem([
            'name' => 'header',
            'title' => $title,
            'order' => $order,
        ]);

        return $this;
    }

    /**
     * Alias for "addHeader" method.
     *
     * @param  string  $title
     * @return $this
     */
    public function header($title)
    {
        return $this->addHeader($title);
    }

    /**
     * Alias for "addDivider" method.
     *
     * @return $this
     */
    public function divider()
    {
        return $this->addDivider();
    }

    /**
     * Get items count.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Empty the current menu items.
     */
    public function destroy(): static
    {
        $this->items = [];

        return $this;
    }

    /**
     * Render the menu to HTML tag.
     *
     * @param  string  $presenter
     * @return string
     */
    public function render($presenter = null)
    {
        $this->resolveItems($this->items);

        if (! is_null($this->view)) {
            return $this->renderView($presenter);
        }

        if ($this->hasStyle($presenter)) {
            $this->setPresenterFromStyle($presenter);
        }

        if (! is_null($presenter) && ! $this->hasStyle($presenter)) {
            $this->setPresenter($presenter);
        }

        return $this->renderMenu();
    }

    /**
     * Render menu via view presenter.
     *
     * @return \Illuminate\View\View
     */
    public function renderView($presenter = null)
    {
        return $this->views->make($presenter ?: $this->view, [
            'items' => $this->getOrderedItems(),
        ]);
    }

    /**
     * Get original items.
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get menu items as laravel collection instance.
     *
     * @return \Illuminate\Support\Collection
     */
    public function toCollection()
    {
        return collect($this->items);
    }

    /**
     * Get menu items as array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->toCollection()->toArray();
    }

    /**
     * Enable menu ordering.
     */
    public function enableOrdering(): static
    {
        $this->ordering = true;

        return $this;
    }

    /**
     * Disable menu ordering.
     */
    public function disableOrdering(): static
    {
        $this->ordering = false;

        return $this;
    }

    /**
     * Get menu items and order it by 'order' key.
     *
     * @return array
     */
    public function getOrderedItems()
    {
        if (config('menus.ordering') || $this->ordering) {
            return $this->toCollection()->sortBy(function ($item) {
                return $item->order;
            })->all();
        }

        return $this->items;
    }

    /**
     * Render the menu.
     */
    protected function renderMenu(): string
    {
        $presenter = $this->getPresenter();
        $menu = $presenter->getOpenTagWrapper();

        foreach ($this->getOrderedItems() as $item) {
            if ($item->hidden()) {
                continue;
            }

            if ($item->hasSubMenu()) {
                $menu .= $presenter->getMenuWithDropDownWrapper($item);
            } elseif ($item->isHeader()) {
                $menu .= $presenter->getHeaderWrapper($item);
            } elseif ($item->isDivider()) {
                $menu .= $presenter->getDividerWrapper();
            } else {
                $menu .= $presenter->getMenuWithoutDropdownWrapper($item);
            }
        }

        return $menu.$presenter->getCloseTagWrapper();
    }
}
