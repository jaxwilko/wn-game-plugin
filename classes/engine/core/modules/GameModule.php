<?php

namespace JaxWilko\Game\Classes\Engine\Core\Modules;

use Illuminate\Support\Facades\Cache;
use JaxWilko\Game\Classes\Engine\Core\Communication\Communicate;
use JaxWilko\Game\Classes\Engine\Core\Contracts\GameModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Events\Events;
use JaxWilko\Game\Classes\Engine\Core\Exceptions\CallToUndefinedProperty;
use JaxWilko\Game\Classes\Engine\Engine;

abstract class GameModule implements GameModuleInterface
{
    /**
     * @const defines the default module cache key
     */
    public const CACHE_KEY = Engine::CACHE_KEY . '.default';

    /**
     * @var array the module state, used to cache game data
     */
    protected array $state = [];

    /**
     * @var array defines engine events to be executed on matching local methods
     */
    protected array $emit = [];

    /**
     * @var array defines engine retrievable events on matching local methods
     */
    protected array $stats = [];

    /**
     * @var array allows for custom settings to be passed to the module on init
     */
    protected array $settings = [];

    /**
     * @var array allows for access to state keys via magic __get
     */
    protected array $props = [];

    /**
     * @var Engine the current engine instance
     */
    protected Engine $engine;

    /**
     * @var Communicate allows for cross module communication
     */
    protected Communicate $com;

    /**
     * Boots the module, restoring the module from cache and registering default event handling
     *
     * @param Engine $engine
     * @param Communicate $com
     * @param Events $events
     * @param array $settings
     * @return void
     */
    public function boot(Engine $engine, Communicate $com, Events $events, array $settings = []): void
    {
        $this->engine = $engine;
        $this->com = $com;
        $this->settings = $settings;

        $this->restore(Cache::get(static::CACHE_KEY, []));
        $this->register($events);

        $events->listen('store', [$this, 'store']);
        $events->listen('flush', [$this, 'flush']);

        foreach ($this->emit as $emit) {
            $events->listen($emit, [$this, $emit]);
        }

        foreach ($this->stats as $stat) {
            $events->listenRetrievable($stat, [$this, $stat]);
        }
    }

    /**
     * Magic to allow for access to state properties via getters
     *
     * @param $name
     * @return mixed
     * @throws CallToUndefinedProperty
     */
    public function &__get($name): mixed
    {
        if (!in_array($name, $this->props)) {
            throw new CallToUndefinedProperty($name . ' is not defined');
        }

        if (!isset($this->state[$name])) {
            $this->state[$name] = [];
        }

        return $this->state[$name];
    }

    /**
     * Magic to allow for updating state properties
     *
     * @param $name
     * @param $value
     * @return void
     * @throws CallToUndefinedProperty
     */
    public function __set($name, $value): void
    {
        if (!in_array($name, $this->props)) {
            throw new CallToUndefinedProperty($name . ' is not defined');
        }

        $this->state[$name] = $value;
    }

    /**
     * Abstract requiring game modules to implement
     *
     * @param Events $events
     * @return void
     */
    abstract public function register(Events $events): void;

    /**
     * Sets the modules state to the value provided
     *
     * @param array $data
     * @return void
     */
    public function restore(array $data): void
    {
        $this->state = $data;
    }

    /**
     * Saves the module state in cache
     *
     * @return void
     */
    public function store(): void
    {
        Cache::set(static::CACHE_KEY, $this->state);
    }

    /**
     * Deletes the module state cache
     *
     * @return void
     */
    public function flush(): void
    {
        Cache::forget(static::CACHE_KEY);
        $this->state = [];
    }

    /**
     * Updates the state if there is a non-empty version in cache
     *
     * @return void
     */
    public function refresh(): void
    {
        $state = Cache::get(static::CACHE_KEY, []);

        if (empty($state)) {
            return;
        }

        $this->restore($state);
    }

    /**
     * Returns the state of the module
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->state;
    }
}
