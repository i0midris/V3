<?php

namespace X\LaravelMenus;

use Closure;
use Collective\Html\HtmlFacade as HTML;
use Illuminate\Contracts\Support\Arrayable as ArrayableContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

/**
 * @property string url
 * @property string route
 * @property string title
 * @property string name
 * @property string icon
 * @property int parent
 * @property array attributes
 * @property bool active
 * @property int order
 */
class MenuItem implements ArrayableContract
{
    public ?string $url = null;

    public ?string $route = null;

    public ?string $title = null;

    public ?string $name = null;

    public ?string $icon = null;

    public $parent = null; // Could be string or self-reference

    public array $attributes = [];

    public bool $active = false;

    public int $order = 0;

    public $hideWhen = null;

    /**
     * Array properties.
     *
     * @var array
     */
    protected $properties = [];

    /**
     * The child collections for current menu item.
     *
     * @var array
     */
    protected $childs = [];

    /**
     * The fillable attribute.
     */
    protected array $fillable = [
        'url',
        'route',
        'title',
        'name',
        'icon',
        'parent',
        'attributes',
        'active',
        'order',
        'hideWhen',
    ];

    /**
     * The hideWhen callback.
     *
     * @var Closure
     */

    /**
     * Constructor.
     *
     * @param  array  $properties
     */
    public function __construct($properties = [])
    {
        $this->properties = $properties;
        $this->fill($properties);
    }

    /**
     * Set the icon property when the icon is defined in the link attributes.
     */
    protected static function setIconAttribute(array $properties): array
    {
        $icon = Arr::get($properties, 'attributes.icon');
        if (! is_null($icon)) {
            $properties['icon'] = $icon;

            Arr::forget($properties, 'attributes.icon');

            return $properties;
        }

        return $properties;
    }

    /**
     * Get random name.
     */
    protected static function getRandomName(array $attributes): string
    {
        return substr(md5(Arr::get($attributes, 'title', Str::random(6))), 0, 5);
    }

    /**
     * Create new static instance.
     */
    public static function make(array $properties): static
    {
        $properties = self::setIconAttribute($properties);

        return new static($properties);
    }

    /**
     * Fill the attributes.
     *
     * @param  array  $attributes
     */
    public function fill($attributes): void
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Create new menu child item using array.
     *
     *
     * @return $this
     */
    public function child(array $attributes): static
    {
        $this->childs[] = static::make($attributes);

        return $this;
    }

    /**
     * Register new child menu with dropdown.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function dropdown($title, \Closure $callback, $order = 0, array $attributes = [])
    {
        $properties = ['title' => $title, 'order' => $order, 'attributes' => $attributes];

        if (func_num_args() === 3) {
            $arguments = func_get_args();

            $title = Arr::get($arguments, 0);
            $attributes = Arr::get($arguments, 2);

            $properties = ['title' => $title, 'attributes' => $attributes];
        }

        $child = static::make($properties);

        call_user_func($callback, $child);

        $this->childs[] = $child;

        return $child;
    }

    /**
     * Create new menu item and set the action to route.
     *
     * @param  array  $parameters
     * @param  array  $attributes
     * @return MenuItem
     */
    public function route($route, $title, $parameters = [], $order = 0, $attributes = [])
    {
        if (func_num_args() === 4) {
            $arguments = func_get_args();

            return $this->add([
                'route' => [Arr::get($arguments, 0), Arr::get($arguments, 2)],
                'title' => Arr::get($arguments, 1),
                'attributes' => Arr::get($arguments, 3),
            ]);
        }

        $route = [$route, $parameters];

        return $this->add(['route' => $route, 'title' => $title, 'order' => $order, 'attributes' => $attributes]);
    }

    /**
     * Create new menu item  and set the action to url.
     *
     * @param  array  $attributes
     * @return MenuItem
     */
    public function url($url, $title, $order = 0, $attributes = [])
    {
        if (func_num_args() === 3) {
            $arguments = func_get_args();

            return $this->add([
                'url' => Arr::get($arguments, 0),
                'title' => Arr::get($arguments, 1),
                'attributes' => Arr::get($arguments, 2),
            ]);
        }

        return $this->add(['url' => $url, 'title' => $title, 'order' => $order, 'attributes' => $attributes]);
    }

    /**
     * Add new child item.
     *
     *
     * @return $this
     */
    public function add(array $properties)
    {
        $item = static::make($properties);

        $this->childs[] = $item;

        return $item;
    }

    /**
     * Add new divider.
     *
     * @param  int  $order
     * @return self
     */
    public function addDivider($order = null)
    {
        $item = static::make(['name' => 'divider', 'order' => $order]);

        $this->childs[] = $item;

        return $item;
    }

    /**
     * Alias method instead "addDivider".
     *
     * @param  int  $order
     * @return MenuItem
     */
    public function divider($order = null)
    {
        return $this->addDivider($order);
    }

    /**
     * Add dropdown header.
     *
     *
     * @return $this
     */
    public function addHeader($title)
    {
        $item = static::make([
            'name' => 'header',
            'title' => $title,
        ]);

        $this->childs[] = $item;

        return $item;
    }

    /**
     * Same with "addHeader" method.
     *
     *
     * @return $this
     */
    public function header($title)
    {
        return $this->addHeader($title);
    }

    /**
     * Get childs.
     *
     * @return array
     */
    public function getChilds()
    {
        if (config('menus.ordering')) {
            return collect($this->childs)->sortBy('order')->all();
        }

        return $this->childs;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl()
    {
        if ($this->route !== null) {
            return route($this->route[0], $this->route[1]);
        }

        if (empty($this->url)) {
            return url('/#');
        }

        return url($this->url);
    }

    /**
     * Get request url.
     */
    public function getRequest(): string
    {
        return ltrim(str_replace(url('/'), '', $this->getUrl()), '/');
    }

    /**
     * Get icon.
     *
     * @param  null|string  $default
     */
    public function getIcon($default = null): ?string
    {
        if ($this->icon !== null && $this->icon !== '') {
            return '<i class="'.$this->icon.'"></i>';
        }
        if ($default === null) {
            return $default;
        }

        return '<i class="'.$default.'"></i>';
    }

    /**
     * Get properties.
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Get HTML attribute data.
     *
     * @return mixed
     */
    public function getAttributes()
    {
        $attributes = $this->attributes ? $this->attributes : [];

        Arr::forget($attributes, ['active', 'icon']);

        return HTML::attributes($attributes);
    }

    /**
     * Check is the current item divider.
     *
     * @return bool
     */
    public function isDivider()
    {
        return $this->is('divider');
    }

    /**
     * Check is the current item divider.
     *
     * @return bool
     */
    public function isHeader()
    {
        return $this->is('header');
    }

    /**
     * Check is the current item divider.
     */
    public function is($name): bool
    {
        return $this->name == $name;
    }

    /**
     * Check is the current item has sub menu .
     */
    public function hasSubMenu(): bool
    {
        return ! empty($this->childs);
    }

    /**
     * Same with hasSubMenu.
     *
     * @return bool
     */
    public function hasChilds()
    {
        return $this->hasSubMenu();
    }

    /**
     * Check the active state for current menu.
     *
     * @return mixed
     */
    public function hasActiveOnChild()
    {
        if ($this->inactive()) {
            return false;
        }

        return $this->hasChilds() && $this->getActiveStateFromChilds();
    }

    /**
     * Get active state from child menu items.
     */
    public function getActiveStateFromChilds(): bool
    {
        foreach ($this->getChilds() as $child) {
            if ($child->inactive()) {
                continue;
            }
            if ($child->hasChilds()) {
                if ($child->getActiveStateFromChilds()) {
                    return true;
                }
            } elseif ($child->isActive()) {
                return true;
            } elseif ($child->hasRoute() && $child->getActiveStateFromRoute()) {
                return true;
            } elseif ($child->getActiveStateFromUrl()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get inactive state.
     *
     * @return bool
     */
    public function inactive()
    {
        $inactive = $this->getInactiveAttribute();

        if (is_bool($inactive)) {
            return $inactive;
        }

        if ($inactive instanceof \Closure) {
            return call_user_func($inactive);
        }

        return false;
    }

    /**
     * Get active attribute.
     *
     * @return string
     */
    public function getActiveAttribute()
    {
        return Arr::get($this->attributes, 'active');
    }

    /**
     * Get inactive attribute.
     *
     * @return string
     */
    public function getInactiveAttribute()
    {
        return Arr::get($this->attributes, 'inactive');
    }

    /**
     * Get active state for current item.
     *
     * @return mixed
     */
    public function isActive()
    {
        if ($this->inactive()) {
            return false;
        }

        $active = $this->getActiveAttribute();

        if (is_bool($active)) {
            return $active;
        }

        if ($active instanceof \Closure) {
            return call_user_func($active);
        }

        if ($this->hasRoute()) {
            return $this->getActiveStateFromRoute();
        }

        return $this->getActiveStateFromUrl();
    }

    /**
     * Determine the current item using route.
     */
    protected function hasRoute(): bool
    {
        return ! empty($this->route);
    }

    /**
     * Get active status using route.
     *
     * @return bool
     */
    protected function getActiveStateFromRoute()
    {
        return Request::is(str_replace(url('/').'/', '', $this->getUrl()));
    }

    /**
     * Get active status using request url.
     *
     * @return bool
     */
    protected function getActiveStateFromUrl()
    {
        return Request::is($this->url);
    }

    /**
     * Set order value.
     *
     * @param  int  $order
     */
    public function order($order): static
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Set hide condition for current menu item.
     *
     * @param  Closure
     * @return bool
     */
    public function hideWhen(Closure $callback): static
    {
        $this->hideWhen = $callback;

        return $this;
    }

    /**
     * Determine whether the menu item is hidden.
     *
     * @return bool
     */
    public function hidden()
    {
        if (is_null($this->hideWhen)) {
            return false;
        }

        return call_user_func($this->hideWhen) == true;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getProperties();
    }

    /**
     * Get property.
     *
     * @param  string  $key
     * @return string|null
     */
    public function __get($key)
    {
        return isset($this->$key) ? $this->$key : null;
    }
}
