<?php

namespace JaxWilko\Game\Classes\Objects\Backgrounds;

use JaxWilko\Game\Classes\Engine\Core\Objects\StaticWorldObject;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;

class Grass extends StaticWorldObject
{
    protected array $spriteMap = [
        'idle' => [
            'sheet' => '/plugins/jaxwilko/game/classes/engine/assets/grass.png',
            'align' => 64,
            'delay' => 50
        ]
    ];

    protected bool $animationRandomDelay = true;

    public function __construct(Vector $vector, Vector $size, array $settings = ['colour' => '#964B00'])
    {
        parent::__construct($vector, $size, $settings);
    }
}
