<?php

namespace JaxWilko\Game\Classes\Engine\Core\Objects\Entities;

use JaxWilko\Game\Classes\Engine\Core\Contracts\HasInventoryInterface;
use JaxWilko\Game\Classes\Engine\Core\Contracts\ToArrayInterface;
use JaxWilko\Game\Classes\Engine\Core\Objects\Traits\HasAnimations;
use JaxWilko\Game\Classes\Engine\Core\Objects\Traits\HasInventory;
use JaxWilko\Game\Classes\Engine\Core\Objects\WorldObject;

class Entity extends WorldObject implements ToArrayInterface, HasInventoryInterface
{
    use HasInventory;
    use HasAnimations;

    protected int $health;
    protected int $attackRange;

    protected int $damage;

    protected ?string $lootTable = null;

    protected bool $invulnerable = false;

    public function update(): static
    {
        return $this;
    }

    public function getDistance(WorldObject $entity): int
    {
        list($center, $entityCenter) = [$this->getCenter(), $entity->getCenter()];

        $diffX = $center->x() - $entityCenter->x();
        $diffY = $center->y() - $entityCenter->y();

        return $diffX * $diffX + $diffY * $diffY;
    }

    public function getClosest(array $entities): ?Entity
    {
        $min = $closest = null;
        foreach ($entities as $entity) {
            // Restore
            if (is_string($entity)) {
                $entity = WorldObject::load($entity);
            }

            $distance = $this->getDistance($entity);
            if (!$min || $min > $distance) {
                $min = $distance;
                $closest = $entity;
            }
        }

        return $closest;
    }

    public function health(): int
    {
        return $this->health;
    }

    public function animation(string $animation): static
    {
        $this->animation = $animation;
        return $this;
    }

    public function attack(Entity $entity): static
    {
        $this->animation = 'attack';
        $entity->damage($this->damage)
            ->save();

        return $this;
    }

    public function damage(int $amount): static
    {
        if ($this->invulnerable) {
            return $this;
        }

        $this->health -= $amount;

        if ($this->health < 1) {
            $this->health = 0;
        }

        return $this;
    }

    public function heal(int $amount): static
    {
        $this->health += $amount;
        $this->health = min($this->health, 100);
        return $this;
    }

    public function increaseDamage(int $amount): static
    {
        $this->damage += $amount;
        return $this;
    }

    public function alive(): int
    {
        return $this->health > 0;
    }

    public function toArray(): array
    {
        return [
            'spriteMap' => $this->spriteMap,
            'animation' => $this->animation,
            'vector' => $this->vector->toArray(),
            'size' => $this->size->toArray()
        ];
    }
}
