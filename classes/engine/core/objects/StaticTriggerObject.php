<?php

namespace JaxWilko\Game\Classes\Engine\Core\Objects;

use JaxWilko\Game\Classes\Engine\Core\Contracts\Modules\WorldModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Contracts\ToArrayInterface;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

/**
 * @class StaticTriggerObject
 * Extends the `StaticWorldObject` and implements an abstract `tick` method
 */
abstract class StaticTriggerObject extends StaticWorldObject implements ToArrayInterface
{
    /**
     * Tick must be implemented by children and should execute custom game logic
     *
     * @param Level $level
     * @param WorldModuleInterface $world
     * @return void
     */
    abstract public function tick(Level $level, WorldModuleInterface $world): void;
}
