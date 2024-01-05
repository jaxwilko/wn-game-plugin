<?php

namespace JaxWilko\Game\Classes\Engine;

use JaxWilko\Game\Classes\Engine\Core\Contracts\DataProviderInterface;
use JaxWilko\Game\Classes\Engine\Core\Contracts\GameModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Events\Events;
use JaxWilko\Game\Classes\Engine\Core\Communication\Communicate;
use JaxWilko\Game\Classes\Engine\Core\Utils\Console;
use Weird\Processes\Thread;
use Weird\ProcessManager;
use Winter\Storm\Support\Facades\Config;

/**
 * @class Engine
 * This is the core of the system, it handles registration of modules and worker threads, and dispatches events.
 *
 * @method self execTick()
 * @method self execStore()
 * @method self execFlush()
 * @method self execWithLayerCache(callable $callback)
 * @method array getPlayerData(callable $callback)
 */
class Engine
{
    public const CACHE_KEY = 'jaxwilko.game.state';
    public const SCRIPT_CACHE_DIR = 'framework/cache/jaxwilko.game';

    /**
     * @var int the ticks per second the engine should try to achieve
     */
    protected int $targetTicks;

    /**
     * @var float the tick rate the server aims to run at
     */
    protected float $tickRate;

    /**
     * @var array|string module config to load from, can be overwritten in the constructor
     */
    protected array|string $moduleConfig = 'jaxwilko.game::modules';

    /**
     * @var array|string data config to load from, can be overwritten in the constructor
     */
    protected array|string $dataConfig = 'jaxwilko.game::data';

    /**
     * @var Engine static access to current running instance
     */
    protected static Engine $self;

    /**
     * @var array loaded modules
     */
    protected array $modules = [];

    /**
     * @var array loaded data providers
     */
    protected array $dataProviders = [];

    /**
     * @var int count of worker threads to spawn
     */
    protected int $threads;

    /**
     * @var Events the event manager for the game engine
     */
    protected Events $events;

    /**
     * @var ProcessManager a `Weird\ProcessManager` instance, used for managing worker threads
     */
    public readonly ProcessManager $processManager;

    /**
     * @var Communicate allows for inter-module communication
     */
    public readonly Communicate $com;

    /**
     * Creates a new engine and binds this to a static property to allow for global access to the current instance
     *
     * @param int $threads
     * @param array|string|null $moduleConfig
     * @param array|string|null $dataConfig
     * @param Communicate|null $com
     * @param Events|null $events
     */
    public function __construct(
        int $threads = 8,
        array|string|null $moduleConfig = null,
        array|string|null $dataConfig = null,
        ?Communicate $com = null,
        ?Events $events = null
    ) {
        $this->targetTicks = Config::get('jaxwilko.game::server.ticks');
        $this->tickRate = 1 / $this->targetTicks;

        $this->threads = $threads;
        $this->moduleConfig = $moduleConfig ?? $this->moduleConfig;
        $this->dataConfig = $dataConfig ?? $this->dataConfig;
        $this->com = $com ?? new Communicate($this);
        $this->events = $events ?? new Events();

        static::$self = $this;
    }

    /**
     * Magic method to dispatch event processing via the Event class
     *
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call(string $name, array $args): mixed
    {
        if (str_starts_with($name, 'exec')) {
            if (!$this->events->fire(lcfirst(substr($name, 4)), ...$args)) {
                throw new \RuntimeException('Event ' . $name . ' not found');
            }
            return $this;
        }

        if (str_starts_with($name, 'get')) {
            return $this->events->fireRetrievable(lcfirst(substr($name, 3)), ...$args);
        }

        throw new \BadMethodCallException(sprintf('Engine does not support `%s`', $name));
    }

    /**
     * Magic to ensure when the engine is destroyed the worker threads are also killed
     */
    public function __destruct()
    {
        if (isset($this->processManager)) {
            $this->processManager->killAll();
        }
    }

    /**
     * Helper to resolve an engine relative path
     *
     * @param string|null $path
     * @return string
     */
    public static function getPath(string $path = null): string
    {
        return __DIR__ . ($path ? '/' . ltrim($path, '/') : '');
    }

    /**
     * Creates a new Engine instance
     *
     * @param array $settings
     * @return Engine
     */
    public static function create(array $settings = []): Engine
    {
        return (new static())->boot($settings);
    }

    /**
     * Boots the game engine with settings passed into modules
     *
     * @param array $settings
     * @return $this
     * @throws \Weird\Exceptions\ProcessSpawnFailed
     */
    public function boot(array $settings = []): Engine
    {
        Console::put(
            'Targeting <span class="text-red-600">%d</span> tick @ <span class="text-red-600">%f</span> tick rate',
            $this->targetTicks,
            $this->tickRate
        );

        $dataProviders = is_array($this->dataConfig)
            ? $this->dataConfig
            : Config::get($this->dataConfig);

        Console::put(
            'Registering data providers: %s',
            implode(', ', array_map(fn (string $class) => substr(strrchr($class, '\\'), 1), $dataProviders))
        );

        foreach ($dataProviders as $dataProvider) {
            $this->dataProviders[$dataProvider] = new $dataProvider;
            $this->dataProviders[$dataProvider]->register();
        }

        $modules = is_array($this->moduleConfig)
            ? $this->moduleConfig
            : Config::get($this->moduleConfig);

        Console::put('Registering game modules: ' . implode(', ', array_keys($modules)));

        foreach ($modules as $key => $module) {
            $this->modules[$key] = new $module;
            $this->modules[$key]->boot($this, $this->com, $this->events, $settings[$module] ?? []);
        }

        Console::put('Spawning <span class="text-red-600">%d</span> threads', $this->threads);

        $this->processManager = ProcessManager::create()
            ->withBootstrap(__DIR__ . '/runtime.php')
            ->spawn(Thread::class, $this->threads)
            ->registerHintHandler([Console::class, 'hintHandle'])
            ->registerUnknownMessageHandler([Console::class, 'unknownHandle']);

        Console::put('Binding process manager tick event...');

        $this->events->listen('tick', function () {
            $this->processManager->tick();
        });

        return $this;
    }

    /**
     * Gets a module if loaded
     *
     * @param string $module
     * @return GameModuleInterface|null
     */
    public function getModule(string $module): ?GameModuleInterface
    {
        return $this->modules[$module] ?? null;
    }

    /**
     * Statically gets a loaded module from the running Engine instance
     *
     * @param string $module
     * @return GameModuleInterface|null
     * @throws \Weird\Exceptions\ProcessSpawnFailed
     */
    public static function loadModule(string $module): ?GameModuleInterface
    {
        return (
            static::$self ?? (new static(threads: 0))->boot()
        )->getModule($module);
    }

    /**
     * Loads a module from a new instance of the Engine which is destroyed after load
     *
     * @param string $module
     * @return GameModuleInterface|null
     * @throws \Weird\Exceptions\ProcessSpawnFailed
     */
    public static function loadFreshModule(string $module): ?GameModuleInterface
    {
        return (new static(threads: 0))->boot()->getModule($module);
    }

    /**
     * Returns loaded data providers
     *
     * @return array
     */
    public function getProviders(): array
    {
        return $this->dataProviders;
    }

    /**
     * Gets a data provider if loaded
     *
     * @param string $provider
     * @return DataProviderInterface|null
     */
    public static function getProvider(string $provider): ?DataProviderInterface
    {
        return isset(static::$self) ? static::$self->getProviders()[$provider] ?? null : null;
    }

    /**
     * Get the tick rate the engine should be running at
     *
     * @return float
     */
    public function getTickRate(): float
    {
        return $this->tickRate;
    }

    /**
     * Dumps all loaded module data
     *
     * @return array
     */
    public function dump(): array
    {
        $state = [];
        foreach ($this->modules as $key => $module) {
            $state[$key] = $module->toArray();
        }

        return $state;
    }
}
