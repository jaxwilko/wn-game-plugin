<?php

namespace JaxWilko\Game\Classes\Objects\Triggers;

use JaxWilko\Game\Classes\Engine\Core\Contracts\Modules\WorldModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Objects\StaticTriggerObject;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

class Spawn extends StaticTriggerObject
{
    public function __construct(Vector $vector, Vector $size, array $settings = ['colour' => '#0000000'])
    {
        parent::__construct($vector, $size, $settings);
    }

    public function tick(Level $level, WorldModuleInterface $world): void
    {
        // Do nothing
    }
}
