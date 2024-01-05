<?php

namespace JaxWilko\Game\Classes\Engine\Core\Objects\Entities;

use JaxWilko\Game\Classes\Engine\Core\Objects\Sprites\Poof;
use JaxWilko\Game\Classes\Engine\Core\Objects\Sprites\Sprint;
use JaxWilko\Game\Classes\Engine\Core\Objects\StaticWorldObject;
use JaxWilko\Game\Classes\Engine\Core\Objects\WorldObject;
use JaxWilko\Game\Classes\Engine\Core\Utils\Pathing;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

class Actor extends Entity
{
    protected int $health;
    protected int $speed;

    protected array $blockingLayers = [
        Level::LAYER_BLOCKS,
        Level::LAYER_PROPS,
        Level::LAYER_ACTORS
    ];

    public function __construct(Vector $vector, Vector $sizes, int $health = 100, int $speed = 1)
    {
        $this->health = $health;
        $this->speed = $speed;

        parent::__construct($vector, $sizes);
    }

    public function getDirectionalControls(StaticWorldObject|Entity $entity): array
    {
        list($center, $entityCenter) = [$this->getCenter(), $entity->getCenter()];

        return [
            'up' => $center->y() > $entityCenter->y(),
            'down' => $center->y() < $entityCenter->y(),
            'left' => $center->x() > $entityCenter->x(),
            'right' => $center->x() < $entityCenter->x(),
        ];
    }

    public function getRandomControls(): array
    {
        return [
            'up' => (bool) rand(0, 1),
            'down' => (bool) rand(0, 1),
            'left' => (bool) rand(0, 1),
            'right' => (bool) rand(0, 1),
        ];
    }

    public function applyControls(array $controls, Level $level): static
    {
        $worldSize = $level->getSize()[1]->get();

        $this->animate('idle');

        if (($controls['up'] ?? null) && !$this->isBlocked($level, 'y', -$this->speed)) {
            $this->vector->tapY(-$this->speed, 0);
            $this->animate('up');
        }

        if (($controls['down'] ?? null) && !$this->isBlocked($level, 'y', $this->speed)) {
            $this->vector->tapY($this->speed, $worldSize[1] - $this->size->y());
            $this->animate('down');
        }

        if (($controls['left'] ?? null) && !$this->isBlocked($level, 'x', -$this->speed)) {
            $this->vector->tapX(-$this->speed, 0);
            $this->animate('left');
        }

        if (($controls['right'] ?? null) && !$this->isBlocked($level, 'x', $this->speed)) {
            $this->vector->tapX($this->speed, $worldSize[0] - $this->size->x());
            $this->animate('right');
        }

        if ($controls['attack'] ?? null && $controls['attack']) {
            $this->executeAttack($level);
            $this->animate('attack');
        }

        return $this;
    }

    public function getMovementBlockedBy(array $controls, Level $level): array
    {
        if (($controls['up'] ?? null) && $objects = $this->blockedBy($level, 'y', -$this->speed)) {
            return $objects;
        }

        if (($controls['down'] ?? null) && $objects = $this->blockedBy($level, 'y', $this->speed)) {
            return $objects;
        }

        if (($controls['left'] ?? null) && $objects = $this->blockedBy($level, 'x', -$this->speed)) {
            return $objects;
        }

        if (($controls['right'] ?? null) && $objects = $this->blockedBy($level, 'x', $this->speed)) {
            return $objects;
        }

        return [];
    }

    public function isBlocked(Level $level, string $prop, int $amount): bool
    {
        return !empty(
            $level->search(
                new WorldObject($this->vector->dry($prop, $amount), $this->size),
                $this->blockingLayers,
                true,
                $this->id
            )
        );
    }

    public function blockedBy(Level $level, string $prop, int $amount): array
    {
        return $level->search(
            new WorldObject($this->vector->dry($prop, $amount), $this->size),
            $this->blockingLayers,
            true,
            $this->id,
            true
        );
    }

    public function getBlockingLayers(): array
    {
        return $this->blockingLayers;
    }

    public function makePathToEntity(Level $level, WorldObject $target): array
    {
        return Pathing::makePath($level, $this, $target);
    }

    public function executeAttack(Level $level): void
    {
        $indicator = new Poof(
            new Vector($this->vector->x - 5, $this->vector->y - 5),
            new Vector($this->size->x + 10, $this->size->y + 10)
        );

        if (!$level->search($indicator, [Level::LAYER_SPRITES], false, null, true)) {
            $level->pushLayer(Level::LAYER_SPRITES, $indicator);
        }

        foreach ($level->search($indicator, [Level::LAYER_ACTORS], true, $this->id, true) as $actor) {
            $this->attack($actor);
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'spriteMap' => $this->spriteMap,
            'animation' => $this->animation,
            'vector' => $this->vector->toArray(),
            'size' => $this->size->toArray(),
            'health' => $this->health,
            'speed' => $this->speed
        ];
    }
}
