<?php

namespace JaxWilko\Game\Classes\Objects\Triggers;

use JaxWilko\Game\Classes\Engine\Core\Contracts\Modules\WorldModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Objects\StaticTriggerObject;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;
use JaxWilko\Game\Classes\Objects\Entities\Zombie;

class Spawner extends StaticTriggerObject
{
    protected array $spriteMap = [
        'idle' => [
            'sheet' => '/plugins/jaxwilko/game/classes/engine/assets/portal.png',
            'align' => 64,
            'delay' => 15
        ]
    ];

    protected string $class = Zombie::class;
    protected int $timeout = 300;
    protected int $timer = 0;

    public function __construct(Vector $vector, Vector $size, array $settings = ['colour' => '#0000000'])
    {
        if (isset($settings['class'])) {
            $this->class = $settings['class'];
        }

        if (isset($settings['spriteMap'])) {
            $this->spriteMap = $settings['spriteMap'];
        }

        if (isset($settings['timeout'])) {
            $this->timeout = $settings['timeout'];
        }

        if (isset($settings['animationRandomDelay'])) {
            $this->animationRandomDelay = $settings['animationRandomDelay'];
        }

        parent::__construct($vector, $size, $settings);
    }

    public function tick(Level $level, WorldModuleInterface $world): void
    {
        $this->timer = max(0, $this->timer - 1);

        if ($this->timer !== 0) {
            $this->save();
            return;
        }

        $entities = $level->search($this, [
            Level::LAYER_ACTORS
        ], asObjects: true, flatten: true);

        if (empty($entities)) {
            $size = new Vector(32, 64);
            $world->addActor($level->id, new Zombie(
                $level->getSpawnablePosition($this->getSurroundingArea(2), $size) ?? new Vector(
                    rand($this->vector->x, $this->vector->x + $this->size->x),
                    rand($this->vector->y, $this->vector->y + $this->size->y)
                ),
                $size
            ));

            $this->timer = $this->timeout;

            $this->save();
        }
    }
}
