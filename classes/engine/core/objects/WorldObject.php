<?php

namespace JaxWilko\Game\Classes\Engine\Core\Objects;

use Illuminate\Support\Facades\Cache;
use JaxWilko\Game\Classes\Engine\Core\Contracts\ToArrayInterface;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use Winter\Storm\Support\Str;

/**
 * @class WorldObject
 * This class is the base for all objects in a level.
 */
class WorldObject implements ToArrayInterface
{
    public const CACHE_KEY = 'worldObject.';

    /**
     * @var string the object id used by the engine
     */
    public readonly string $id;

    /**
     * @var Vector the position of the world object
     */
    protected Vector $vector;

    /**
     * @var Vector the size of the object from its position
     */
    protected Vector $size;

    /**
     * Creates a new world object, with an id if one isn't defined by a child implementation
     *
     * @param Vector $vector
     * @param Vector $size
     */
    public function __construct(Vector $vector, Vector $size)
    {
        if (!isset($this->id)) {
            $this->id = Str::uuid();
        }

        $this->vector = $vector;
        $this->size = $size;
    }

    /**
     * Gets the world object's vector
     *
     * @return Vector
     */
    public function getVector(): Vector
    {
        return $this->vector;
    }

    /**
     * Gets the world object's size
     *
     * @return Vector
     */
    public function getSize(): Vector
    {
        return $this->size;
    }

    /**
     * Returns a new `Vector` at the center of the world object
     *
     * @return Vector
     */
    public function getCenter(): Vector
    {
        return new Vector(
            $this->vector->x() + ($this->size->x() / 2),
            $this->vector->y() + ($this->size->y() / 2)
        );
    }

    /**
     * Gets a new `WorldObject` which is the size of the current object + a scaling factor
     *
     * @param float $scale
     * @return WorldObject
     */
    public function getSurroundingArea(float $scale = 0.5): WorldObject
    {
        $factorX = $this->size->x() * $scale;
        $factorY = $this->size->y() * $scale;

        return new WorldObject(
            new Vector((int) ($this->vector->x() - $factorX), (int) ($this->vector->y() - $factorY)),
            new Vector((int) ($this->size->x() + ($factorX * 2)), (int) ($this->size->y() + ($factorY * 2)))
        );
    }

    /**
     * Checks if the passed `WorldObject` intersects the current object
     *
     * @param WorldObject $object
     * @return bool
     */
    public function intersects(WorldObject $object): bool
    {
        return (
            $this->vector->x() < ($object->vector->x() + $object->size->x())
            && ($this->vector->x() + $this->size->x()) > $object->vector->x()
        ) && (
            $this->vector->y() < ($object->vector->y() + $object->size->y())
            && ($this->vector->y() + $this->size->y()) > $object->vector->y()
        );
    }

    /**
     * Checks if the passed `WorldObject` is entirely contained within the current object
     *
     * @param WorldObject $object
     * @return bool
     */
    public function contains(WorldObject $object): bool
    {
        return (
            $this->vector->x() <= $object->vector->x()
            && $this->vector->x() <= ($object->vector->x() + $object->size->x())
            && ($this->vector->x() + $this->size->x()) >= ($object->vector->x() + $object->size->x())
        ) && (
            $this->vector->y() <= $object->vector->y()
            && $this->vector->y() <= ($object->vector->y() + $object->size->y())
            && ($this->vector->y() + $this->size->y()) >= ($object->vector->y() + $object->size->y())
        );
    }

    /**
     * Loads a `WorldObject` from cache
     *
     * @param string $id
     * @return static|null
     */
    public static function load(string $id): ?static
    {
        return Cache::get(static::CACHE_KEY . $id);
    }

    /**
     * Deletes this object from cache
     *
     * @return bool
     */
    public function delete(): bool
    {
        return Cache::forget(static::CACHE_KEY . $this->id);
    }

    /**
     * Executes the callable then calls save on self
     *
     * @param callable $callable
     * @return $this
     */
    public function thenSave(callable $callable): static
    {
        $callable($this);
        return $this->save();
    }

    /**
     * Stores this object in cache by ID
     *
     * @return $this
     */
    public function save(): static
    {
        Cache::set(static::CACHE_KEY . $this->id, $this);
        return $this;
    }

    /**
     * Casts this object to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'vector' => $this->vector->toArray(),
            'size' => $this->size->toArray(),
        ];
    }
}
