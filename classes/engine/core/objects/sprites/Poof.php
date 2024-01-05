<?php

namespace JaxWilko\Game\Classes\Engine\Core\Objects\Sprites;

use JaxWilko\Game\Classes\Engine\Core\Objects\StaticSpriteObject;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

class Poof extends StaticSpriteObject
{
    protected int $lifeTime = 5;
    protected int $life = 0;

    protected array $spriteMap = [
        'idle' => [
            'sheet' => '/plugins/jaxwilko/game/classes/engine/assets/poof.png',
            'align' => 64,
            'delay' => 3
        ]
    ];

    protected bool $animationRandomDelay = true;

    public function __construct(Vector $vector, Vector $size, array $settings = ['colour' => '#964B00'])
    {
        parent::__construct($vector, $size, $settings);
    }

    public function tick(Level $level): void
    {
        if (++$this->life >= $this->lifeTime) {
            $level->removeFromLayer(Level::LAYER_SPRITES, $this);
        }

        $this->save();
    }
}
