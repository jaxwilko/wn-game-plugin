<?php

namespace JaxWilko\Game\Classes\Engine\Core\Objects\Traits\Packages;

use JaxWilko\Game\Classes\Engine\Core\Objects\WorldObject;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

trait FollowPlayerAiPackage
{
    public function followPlayerAiPackage(?Level $level): static
    {
        $this->animate('idle');

        if (!$level) {
            processWrite('Could not obtain level, id: ' . $this->levelId);
            return $this;
        }

        $this->previousPositions[] = $this->vector->toString();

        $target = null;

        if ($this->isStuck()) {
            $target = $this->getRandomTarget($level);
        }

        if (count($this->previousPositions) > 5) {
            array_shift($this->previousPositions);
        }

        if (!$target) {
            $target = $this->getClosestPlayer($level) ?? $this->getRandomTarget($level);
        }

        $this->checkTargetPosition($level, $target);

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
