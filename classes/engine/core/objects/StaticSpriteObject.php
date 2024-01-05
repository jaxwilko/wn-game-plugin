<?php

namespace JaxWilko\Game\Classes\Engine\Core\Objects;

use JaxWilko\Game\Classes\Engine\Core\Contracts\ToArrayInterface;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

/**
 * @class StaticSpriteObject
 * Extends the `StaticWorldObject` and implements an abstract `tick` method
 */
abstract class StaticSpriteObject extends StaticWorldObject implements ToArrayInterface
{
    /**
     * Tick must be implemented by children and should execute custom game logic
     *
     * @param Level $level
     * @return void
     */
    abstract public function tick(Level $level): void;
}
