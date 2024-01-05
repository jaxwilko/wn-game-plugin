<?php

namespace JaxWilko\Game\Classes\Engine\Modules\World;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use JaxWilko\Game\Classes\Engine\Core\Contracts\Modules\WorldModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Events\Events;
use JaxWilko\Game\Classes\Engine\Core\Modules\GameModule;
use JaxWilko\Game\Classes\Engine\Core\Objects\Entities\Actor;
use JaxWilko\Game\Classes\Engine\Core\Objects\WorldObject;
use JaxWilko\Game\Classes\Engine\Core\Utils\Console;
use JaxWilko\Game\Classes\Engine\Engine;

/**
 * @class WorldModule
 * This module is responsible for handling levels, level events and actor mappings.
 *
 * @property array<Level> $levels
 * @property array<string<string>> $actors
 */
class WorldModule extends GameModule implements WorldModuleInterface
{
    /**
     * @const defines the WorldModule cache key
     */
    public const CACHE_KEY = Engine::CACHE_KEY . '.world';

    /**
     * @const defines default world X/Y size
     */
    public const DEFAULT_WORLD_SIZE = 512;

    /**
     * @var array defines engine events to be executed on matching local methods
     */
    protected array $emit = [
        'withLayerCache'
    ];

    /**
     * @var array allows for access to state keys via magic __get
     */
    protected array $props = [
        'levels',
        'actors'
    ];

    /**
     * Registers the module, adding tick to the engine tick event and defines the levels state array if null
     * loading a map if specified in the module settings.
     *
     * @param Events $events
     * @return void
     * @throws \Throwable
     */
    public function register(Events $events): void
    {
        if (!$this->actors) {
            $this->actors = [];
        }

        if (!$this->levels) {
            $this->levels = [];

            if (isset($this->settings['map'])) {
                Level::load($this, $this->settings['map']);
            } else {
                $this->setLevel(new Level());
            }

            $this->store();
        }

        $events->listen('tick', [$this, 'tick']);
    }

    /**
     * Adds a level to the WorldModule.
     *
     * @param Level $level
     * @return Level
     */
    public function setLevel(Level $level): Level
    {
        return $this->levels[$level->id] = $level;
    }

    /**
     * Returns a Level by id.
     *
     * @param string $id
     * @return Level|null
     */
    public function getLevel(string $id): ?Level
    {
        return $this->levels[$id] ?? null;
    }

    /**
     * Gets a level and attempts to load the level if the level is not already loaded.
     *
     * @param string $levelId
     * @return Level|null
     * @throws \Throwable
     */
    public function loadLevel(string $levelId): ?Level
    {
        if (!($level = $this->getLevel($levelId))) {
            // Level not loaded, lets try
            $level = Level::load($this, $levelId);
            if (!$level) {
                throw new \RuntimeException('level not found');
            }
        }

        return $level;
    }

    /**
     * Return all loaded levels.
     *
     * @return Level[]
     */
    public function getLevels(): array
    {
        return $this->levels;
    }

    /**
     * Returns the default level (first level loaded by the module).
     *
     * @return Level
     */
    public function getDefaultLevel(): Level
    {
        // @TODO: Fix
        return $this->levels[array_keys($this->levels)[0]];
    }

    /**
     * Adds an actor to a level, used to map single actor references across levels.
     *
     * @param string $levelId
     * @param WorldObject $object
     * @return WorldObject
     * @throws \Throwable
     */
    public function addActor(string $levelId, WorldObject $object): WorldObject
    {
        $level = $this->loadLevel($levelId);
        $this->actors[$object->id] = $level->id;
        $level->pushLayer(Level::LAYER_ACTORS, $object);

        return $object;
    }

    /**
     * Remove actor from level and actor map.
     *
     * @param WorldObject $object
     * @return void
     */
    public function removeActor(WorldObject $object): void
    {
        $this->levels[$this->actors[$object->id]]->removeFromLayer(Level::LAYER_ACTORS, $object);
        unset($this->actors[$object->id]);
    }

    /**
     * Returns the Level containing an actor ref.
     *
     * @param Actor $entity
     * @return Level|null
     */
    public function getLevelFor(Actor $entity): ?Level
    {
        $levelId = $this->actors[$entity->id] ?? null;
        return $levelId ? $this->levels[$levelId] : null;
    }

    /**
     * Caches all levels layers, executes the callable then removes the layer cache.
     *
     * @param callable $callback
     * @return void
     */
    public function withLayerCache(callable $callback): void
    {
        foreach ($this->levels as $level) {
            $level->generateLayerCache();
        }

        $callback();

        foreach ($this->levels as $level) {
            $level->clearLayerCache();
        }
    }

    /**
     * Deletes the module state cache, additionally clears actor cache and deletes compiled scripts.
     *
     * @return void
     */
    public function flush(): void
    {
        if ($this->actors) {
            foreach ($this->actors as $id => $layer) {
                Cache::forget(WorldObject::CACHE_KEY . $id);
            }
        }

        File::deleteDirectory(storage_path(Engine::SCRIPT_CACHE_DIR));

        Cache::forget(static::CACHE_KEY);
        $this->state = [];
    }

    /**
     * Engine tick event handler for the world module, triggering tick on all loaded levels.
     *
     * @return void
     */
    public function tick(): void
    {
        foreach ($this->levels as $level) {
            $time = microtime(true);
            $level->tick($this);

            // Add stats for detailed debugging
            Console::addStatistics($level->id, [
                'Total' => microtime(true) - $time
            ]);
        }
    }
}
