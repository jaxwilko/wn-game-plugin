<?php

namespace JaxWilko\Game\Classes\Engine\Modules\World;

use JaxWilko\Game\Classes\Engine\Core\Contracts\Modules\WorldModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Objects\StaticSpriteObject;
use JaxWilko\Game\Classes\Engine\Core\Objects\StaticTriggerObject;
use JaxWilko\Game\Classes\Engine\Core\Objects\WorldObject;
use JaxWilko\Game\Classes\Engine\Core\Utils\Console;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use JaxWilko\Game\Classes\Engine\Modules\Player\Player;
use JaxWilko\Game\Classes\Objects\Triggers\Spawn;
use JaxWilko\Game\Models\Level as LevelModel;
use System\Classes\PluginManager;
use Winter\Storm\Support\Str;

class Level
{
    public const LAYER_BACKGROUND = 0;
    public const LAYER_BLOCKS = 1;
    public const LAYER_PROPS = 2;
    public const LAYER_TRIGGERS = 3;
    public const LAYER_MARKERS = 4;
    public const LAYER_ACTORS = 5;
    public const LAYER_SPRITES = 6;
    public const LAYER_PROPS_TOP = 7;

    /**
     * @var array|array[] array of layer data
     */
    protected array $layers = [
        self::LAYER_BACKGROUND => [],
        self::LAYER_BLOCKS => [],
        self::LAYER_PROPS => [],
        self::LAYER_TRIGGERS => [],
        self::LAYER_MARKERS => [],
        self::LAYER_ACTORS => [],
        self::LAYER_SPRITES => [],
        self::LAYER_PROPS_TOP => [],
    ];

    /**
     * @var array|string[] layer id to label map
     */
    public array $layerMap = [
        self::LAYER_BACKGROUND => 'background',
        self::LAYER_BLOCKS => 'blocks',
        self::LAYER_PROPS => 'props',
        self::LAYER_TRIGGERS => 'triggers',
        self::LAYER_MARKERS => 'markers',
        self::LAYER_ACTORS => 'actors',
        self::LAYER_SPRITES => 'sprites',
        self::LAYER_PROPS_TOP => 'props_top',
    ];

    /**
     * @var string level id, set via the code property in the level editor
     */
    public readonly string $id;

    /**
     * @var string level name, set via level editor
     */
    public readonly string $name;

    /**
     * @var string the world colour for the level
     */
    public readonly string $background;

    /**
     * @var string the void colour for the level
     */
    public readonly string $void;

    /**
     * @var array the size of the level in px
     */
    public readonly array $size;

    /**
     * @var array cache of layer data, used for optimisations
     */
    private array $layerCache;

    /**
     * Initializes a new Level object, falls back to defaults if options aren't provided.
     *
     * @param string|null $id
     * @param Vector|null $mapEnd
     * @param string|null $name
     * @param string|null $background
     * @param string|null $void
     */
    public function __construct(
        ?string $id = null,
        Vector $mapEnd = null,
        ?string $name = null,
        ?string $background = null,
        ?string $void = null
    ) {
        $this->id = $id ?? Str::random(16);
        $this->name = $name ?? $this->id;
        $this->background = $background ?? '#6C6C6C';
        $this->void = $void ?? '#161616';
        $this->size = [
            new Vector(),
            $mapEnd ?? new Vector(WorldModule::DEFAULT_WORLD_SIZE, WorldModule::DEFAULT_WORLD_SIZE)
        ];

        Console::put('Loading level: <span class="text-red-600">%s</span>', $this->id);
    }

    /**
     * Generates a new Level instance from a map file, binding itself to the passed WorldModule.
     *
     * @param WorldModuleInterface $world
     * @param string $map
     * @return static
     * @throws \Throwable
     */
    public static function load(WorldModuleInterface $world, string $map): static
    {
        $data = static::getLevelData($map);

        $level = new static(
            $map,
            new Vector(...$data['level']['size'][1]),
            $data['name'] ?? null,
            $data['background'] ?? null,
            $data['void'] ?? null
        );

        $world->setLevel($level);

        $levels = [];

        foreach ($level->layerMap as $layer => $name) {
            if (!isset($data['layers'][$layer])) {
                continue;
            }
            foreach ($data['layers'][$layer] as $obj) {
                try {
                    $init = isset($obj['settings'])
                        ? new $obj['class'](
                            new Vector(...$obj['vector']),
                            new Vector(...$obj['size']),
                            (array) $obj['settings']
                        )
                        : new $obj['class'](
                            new Vector(...$obj['vector']),
                            new Vector(...$obj['size']),
                        );

                    if ($layer === static::LAYER_ACTORS) {
                        $world->addActor($level->id, $init);
                        continue;
                    }

                    $level->pushLayer($layer, $init);

                    if (isset($obj['settings']['level'])) {
                        $levels[] = $obj['settings']['level'];
                    }
                } catch (\Throwable $e) {
                    Console::put('Level spawn failed: ' . $e->getMessage() . '@' . $e->getFile() . ':' . $e->getLine());
                    throw $e;
                }
            }
        }

        foreach ($levels as $levelId) {
            $world->loadLevel($levelId);
        }

        return $level;
    }

    /**
     * Gets level size, if true is passed then will return max size as a Vector.
     *
     * @param bool $end
     * @return array|Vector
     */
    public function getSize(bool $end = false): array|Vector
    {
        return $end ? $this->size[1] : $this->size;
    }

    /**
     * Returns a layer of the level containing object if layer cache is enabled, else references.
     *
     * @param int $layer
     * @return array
     */
    public function getLayer(int $layer): array
    {
        return $this->layerCache[$layer] ?? $this->layers[$layer];
    }

    /**
     * Pushes an object into a layer.
     *
     * @param int $layer
     * @param WorldObject $object
     * @return $this
     */
    public function pushLayer(int $layer, WorldObject $object): static
    {
        $this->layers[$layer][] = $object->id;
        $object->save();

        return $this;
    }

    /**
     * Removes an object from a layer.
     *
     * @param int $layer
     * @param WorldObject $object
     * @return $this
     */
    public function removeFromLayer(int $layer, WorldObject $object): static
    {
        $index = array_search($object->id, $this->layers[$layer]);

        if (!is_int($index)) {
            return $this;
        }

        unset($this->layers[$layer][$index]);

        return $this;
    }

    /**
     * Clears all objects from a layer.
     *
     * @param int $layer
     * @return $this
     */
    public function clearLayer(int $layer): static
    {
        $this->layers[$layer] = [];

        return $this;
    }

    /**
     * Generates layer cache, this will freeze all objects and needs to be unset after a task is performed.
     *
     * @return $this
     */
    public function generateLayerCache(): static
    {
        $this->layerCache = $this->layers;
        foreach ($this->layerCache as $layer => $objects) {
            foreach ($objects as $index => $object) {
                $this->layerCache[$layer][$index] = WorldObject::load($object);
            }
        }

        return $this;
    }

    /**
     * Clears the layer cache.
     *
     * @return $this
     */
    public function clearLayerCache(): static
    {
        unset($this->layerCache);
        return $this;
    }

    /**
     * Searches the level for intersections of the passed WorldObject. This is used throughout the system and powers
     * everything from the Player camera to collision detection.
     *
     * @param WorldObject $worldObject
     * @param array $layers
     * @param bool $asObjects
     * @param string|null $ignore
     * @param bool $flatten
     * @return array
     */
    public function search(
        WorldObject $worldObject,
        array $layers = [],
        bool $asObjects = true,
        ?string $ignore = null,
        bool $flatten = false
    ): array {
        $result = [];
        foreach (!empty($this->layerCache) ? $this->layerCache : $this->layers as $layer => $objects) {
            if (empty($objects) || ($layers && !in_array($layer, $layers))) {
                continue;
            }

            foreach ($objects as $object) {
                if (empty($this->layerCache)) {
                    $object = WorldObject::load($object);
                }

                if ($layer === static::LAYER_ACTORS && $object instanceof Player && !$object->settings('online')) {
                    continue;
                }

                if (is_null($object) || $ignore && $object->id === $ignore) {
                    continue;
                }

                if ($object->intersects($worldObject)) {
                    if ($flatten) {
                        $result[] = $asObjects ? $object : $object->toArray();
                    } else {
                        $result[$layer][] = $asObjects ? $object : $object->toArray();
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Helper to load available level maps.
     *
     * @return array
     */
    public static function getAvailableLevels(): array
    {
        return array_sort(
            array_merge(
                LevelModel::select('code')->where('is_active', '=', true)->get()->pluck('code')->toArray(),
                array_keys(
                    ...array_values(PluginManager::instance()->getRegistrationMethodValues('registerGameLevels'))
                ),
            )
        );
    }

    /**
     * Helper to load level map from DB or File.
     *
     * @param string $map
     * @return array
     * @throws \Exception
     */
    private static function getLevelData(string $map): array
    {
        $model = LevelModel::where([
            ['code', '=', $map],
            ['is_active', '=', true]
        ])->first();

        if ($model) {
            $data = json_decode($model->data, JSON_OBJECT_AS_ARRAY);
            $data['name'] = $model->name;
            return $data;
        }

        $registered = array_merge(
            [],
            ...array_values(PluginManager::instance()->getRegistrationMethodValues('registerGameLevels'))
        );

        if (!isset($registered[$map]) || !file_exists($registered[$map])) {
            throw new \Exception(sprintf('Requested map (%s) not found', $map));
        }

        return json_decode(file_get_contents($registered[$map]), JSON_OBJECT_AS_ARRAY);
    }

    /**
     * Searches the level for a Spawn instance, returning on first hit.
     *
     * @return WorldObject|null
     */
    public function getSpawn(): ?WorldObject
    {
        foreach ($this->search(new WorldObject(...$this->size), [static::LAYER_TRIGGERS], flatten: true) as $trigger) {
            if ($trigger instanceof Spawn) {
                return $trigger;
            }
        }

        return null;
    }

    /**
     * Validates non-blocking positions of a WorldObject in relation to the passed size Vector, used for player
     * spawning and item drops. Ensures players spawn in a free space and validates that all positions are within
     * the level space.
     *
     * @param WorldObject $area
     * @param Vector $size
     * @param array $layers
     * @param bool $fast
     * @return Vector|null
     */
    public function getSpawnablePosition(
        WorldObject $area,
        Vector $size,
        array $layers = [],
        bool $fast = false
    ): ?Vector {
        $level = new WorldObject(new Vector(0, 0), $this->getSize(true));
        $target = new WorldObject(new Vector(0, 0), $size->clone());

        $objects = $this->search($area, $layers ?: array_keys($this->layers), flatten: true);
        $positions = [];

        for ($y = 0; $y < $area->getSize()->y;) {
            for ($x = 0; $x < $area->getSize()->x;) {
                $target->getVector()
                    ->set($area->getVector()->x + $x, $area->getVector()->y + $y);

                $valid = true;
                foreach ($objects as $object) {
                    if ($object->intersects($target)) {
                        $valid = false;
                        break;
                    }
                }

                if ($valid && $level->contains($target) && $area->contains($target)) {
                    if ($fast) {
                        return $target->getVector();
                    }

                    $positions[] = $target->getVector()->clone();
                }

                $x += $target->getSize()->x;
            }

            $y += $target->getSize()->y;
        }

        return !empty($positions) ? array_random($positions) : null;
    }

    /**
     * Level tick event, triggers ticking on Trigger & Sprite objects.
     *
     * @param WorldModuleInterface $world
     * @return void
     */
    public function tick(WorldModuleInterface $world): void
    {
        $start = microtime(true);

        // Generate the level cache to improve performance during tick events
        $this->generateLayerCache();

        $stats = [
            'cacheLayer' => microtime(true) - $start
        ];

        foreach ($this->getLayer(static::LAYER_TRIGGERS) as $trigger) {
            if (!is_object($trigger)) {
                $trigger = StaticTriggerObject::load($trigger);
            }
            $time = microtime(true);
            $trigger->tick($this, $world);
            $stats[count($stats) . ': ' . get_class($trigger)] = microtime(true) - $time;
        }

        $stats['Total'] = microtime(true) - $start;

        Console::addStatistics($this->id, $stats);

        foreach ($this->getLayer(static::LAYER_SPRITES) as $sprite) {
            if (!is_object($sprite)) {
                $sprite = StaticSpriteObject::load($sprite);
            }
            $sprite->tick($this);
        }

        // Flush the layer cache
        $this->clearLayerCache();
    }
}
