<?php

namespace JaxWilko\Game\Classes\Engine\Core\Objects\Entities;

use JaxWilko\Game\Classes\Engine\Core\Objects\StaticWorldObject;
use JaxWilko\Game\Classes\Engine\Core\Objects\Traits\PhpScript;
use JaxWilko\Game\Classes\Engine\Core\Objects\WorldObject;
use JaxWilko\Game\Classes\Engine\Core\Utils\Console;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use JaxWilko\Game\Classes\Engine\Engine;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

class AiActor extends Actor
{
    protected ?string $name = null;

    protected ?string $levelId = null;

    protected Entity|WorldObject|null $target = null;

    protected Entity|WorldObject|null $nextPosition = null;

    protected array $data = [];

    protected array $previousPositions = [];

    protected array $movementPath = [];

    protected ?string $targetPosition = null;

    protected int $searchArea = 1000;

    protected int $recalculateRadius = 5;

    protected bool $invulnerable = false;

    protected bool $thinkingEnabled = false;

    public function update(): static
    {
        $level = $this->getLevel();
        $methods = get_class_methods($this);

        foreach ($methods as $method) {
            if (str_ends_with($method, 'AiPackage')) {
                $this->{$method}($level);
            }
        }

        if (in_array(PhpScript::class, class_uses_recursive($this), true)) {
            $this->tick($level);
        }

        $this->save();

        return $this;
    }

    protected function configure(array $settings): void
    {
        if (isset($settings['name'])) {
            $this->name = $settings['name'];
        }

        if (isset($settings['script']) && in_array(PhpScript::class, class_uses_recursive($this), true)) {
            $this->script = $settings['script'];
        }

        if (isset($settings['spriteMap'])) {
            $this->spriteMap = $settings['spriteMap'];
        }

        if (isset($settings['lootTable'])) {
            $this->lootTable = $settings['lootTable'];
        }

        if (isset($settings['invulnerable'])) {
            $this->invulnerable = $settings['invulnerable'];
        }

        if (isset($settings['items'])) {
            foreach ($settings['items'] as $item => $quantity) {
                $this->addInventoryItem($item, $quantity);
            }
        }
    }

    public function setLevelId(string $level): static
    {
        $this->levelId = $level;
        return $this;
    }

    public function getLevelId(): ?string
    {
        return $this->levelId;
    }

    public function getLevel(): ?Level
    {
        if (!$this->levelId) {
            return null;
        }

        // TODO: better
        Console::output(false);

        $level = Engine::loadFreshModule('world')
            ->getLevel($this->levelId);

        Console::output(true);

        return $level;
    }

    public function shouldUpdate(): bool
    {
        return !$this->hasPath() || !$this->hasMoved() || $this->isStuck();
    }

    protected function isStuck(): bool
    {
        return count($this->previousPositions) > 3 && count(array_unique($this->previousPositions)) === 1;
    }

    protected function hasMoved(): bool
    {
        return !empty($this->previousPositions);
    }

    protected function hasPath(): bool
    {
        return !empty($this->movementPath);
    }

    protected function getClosestPlayer(Level $level): ?Entity
    {
        $players = array_filter(
            $level->search(new WorldObject(
                new Vector(
                    max($this->vector->x - ($this->searchArea / 2), 0),
                    max($this->vector->y - ($this->searchArea / 2), 0),
                ),
                new Vector(
                    $this->vector->x + ($this->searchArea / 2),
                    $this->vector->y + ($this->searchArea / 2)
                )
            ), [Level::LAYER_ACTORS], flatten: true),
            fn ($entity) => !$entity instanceof AiActor
        );

        return $this->getClosest($players);
    }

    protected function getRandomTarget(Level $level): WorldObject
    {
        return new WorldObject(
            new Vector(
                rand(0, min($level->getSize(true)->x, $level->getSize(true)->x - $this->size->x())),
                rand(0, min($level->getSize(true)->y, $level->getSize(true)->y - $this->size->y())),
            ),
            $this->size->clone()
        );
    }

    public function handlePathing(Level $level): array
    {
        if (!$this->nextPosition) {
            $this->nextPosition = new StaticWorldObject(
                new Vector(...$this->movementPath[0]),
                $this->getSize()
            );
        }

        if ($this->nextPosition->getVector()->toString() === $this->vector->toString()) {
            array_shift($this->movementPath);

            if (!$this->movementPath) {
                return [];
            }

            $this->nextPosition = new StaticWorldObject(
                new Vector(...$this->movementPath[0]),
                $this->getSize()
            );
        }

        return $this->getDirectionalControls($this->nextPosition);
    }

    protected function hasTargetMoved(WorldObject $target): bool
    {
        $vector = Vector::fromString($this->targetPosition);
        $dimensions = clone $target->getSize();

        $dimensions->x($dimensions->x() * $this->recalculateRadius);
        $dimensions->y($dimensions->y() * $this->recalculateRadius);

        $vector->tapX(-($dimensions->x() / 2));
        $vector->tapY(-($dimensions->y() / 2));

        return !$target->intersects(new WorldObject($vector, $dimensions));
    }

    protected function think(string $message, mixed $data = null): void
    {
        if (!$this->thinkingEnabled) {
            return;
        }

        Console::put($message);

        if ($data) {
            dump($data);
        }
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'settings' => [
                'name' => $this->name,
            ],
        ]);
    }
}
