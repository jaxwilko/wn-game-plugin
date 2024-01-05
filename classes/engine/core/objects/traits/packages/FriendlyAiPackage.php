<?php

namespace JaxWilko\Game\Classes\Engine\Core\Objects\Traits\Packages;

use JaxWilko\Game\Classes\Engine\Core\Objects\WorldObject;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

trait FriendlyAiPackage
{
    protected bool $pauseOnPlayerBump = true;
    protected ?string $friendlyTarget = null;
    protected array $players = [];

    public function friendlyAiPackage(?Level $level): static
    {
        $this->animation = 'idle';

        if (!$level) {
            processWrite('Could not obtain level, id: ' . $this->levelId);
            return $this;
        }

        if ($this->pauseOnPlayerBump) {
            $this->players = array_map(fn ($player) => $player->id, $level->search(
                $this->getSurroundingArea(),
                [$level::LAYER_ACTORS],
                ignore: $this->id,
                flatten: true
            ));

            if (!empty($this->players)) {
                return $this;
            }
        }

        $this->previousPositions[] = $this->vector->toString();
        if (count($this->previousPositions) > 5) {
            array_shift($this->previousPositions);
        }

        if ($this->isStuck()) {
            $this->friendlyTarget = null;
        }

        $target = null;

        if ($this->friendlyTarget) {
            $target = new WorldObject(
                Vector::fromString($this->friendlyTarget),
                $this->size->clone()
            );
        }

        if (!$target) {
            $target = $this->getRandomTarget($level);
            $this->friendlyTarget = $target->getVector()->toString();
        }

        $this->checkTargetPosition($level, $target);

        if ($this->getDistance($target) < $this->attackRange) {
            $this->friendlyTarget = null;
        }

        if ($this->movementPath && ($controls = $this->handlePathing($level))) {
            $this->applyControls($controls, $level);
        }

        return $this;
    }

    private function checkTargetPosition(Level $level, WorldObject $target): void
    {
        $currentTargetPosition = $target?->getVector()->toString();

        if ($this->targetPosition !== $currentTargetPosition) {
            $this->movementPath = $this->makePathToEntity($level, $target);
            $this->targetPosition = $currentTargetPosition;
            $this->nextPosition = null;
        }
    }
}
