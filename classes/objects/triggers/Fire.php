<?php

namespace JaxWilko\Game\Classes\Objects\Triggers;

use JaxWilko\Game\Classes\Engine\Core\Contracts\Modules\WorldModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Objects\StaticTriggerObject;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

class Fire extends StaticTriggerObject
{
    protected int $damage = 1;

    protected array $spriteMap = [
        'idle' => [
            'sheet' => '/plugins/jaxwilko/game/classes/engine/assets/fire.png',
            'align' => 64,
            'delay' => 10
        ]
    ];

    public function __construct(Vector $vector, Vector $size, array $settings = ['colour' => '#DEA010'])
    {
        parent::__construct($vector, $size, $settings);
    }

    public function tick(Level $level, WorldModuleInterface $world): void
    {
        $entities = $this->getActorsInside($level);

        if (!$entities) {
            return;
        }

        foreach ($entities as $entity) {
            $entity->damage($this->damage);
            $entity->save();
        }
    }
}
