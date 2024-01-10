<?php

namespace JaxWilko\Game\Classes\Objects\Entities;

use JaxWilko\Game\Classes\Engine\Core\Objects\Entities\AiActor;
use JaxWilko\Game\Classes\Engine\Core\Objects\Traits\packages\EnemyAiPackage;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

class Zombie extends AiActor
{
    use EnemyAiPackage;

    protected array $blockingLayers = [
        Level::LAYER_BLOCKS,
        Level::LAYER_PROPS,
        Level::LAYER_ACTORS
    ];

    protected int $speed = 4;

    protected int $attackRange = 2000;

    protected int $damage = 1;

    protected bool $thinkingEnabled = true;

    protected ?string $lootTable = 'zombie';

    protected array $spriteMap = [
        'idle' => [
            'sheet' => '/storage/app/media/game/zombie/idle.png',
            'align' => [32, 64],
            'delay' => 45
        ],
        'attack' => [
            'sheet' => '/storage/app/media/game/zombie/attack.png',
            'align' => [32, 64],
            'delay' => 10
        ],
        'down' => [
            'sheet' => '/storage/app/media/game/zombie/walk-down.png',
            'align' => [32, 64],
            'delay' => 15
        ],
        'up' => [
            'sheet' => '/storage/app/media/game/zombie/walk-up.png',
            'align' => [32, 64],
            'delay' => 15
        ],
        'left' => [
            'sheet' => '/storage/app/media/game/zombie/walk-left.png',
            'align' => [32, 64],
            'delay' => 15
        ],
        'right' => [
            'sheet' => '/storage/app/media/game/zombie/walk-right.png',
            'align' => [32, 64],
            'delay' => 15
        ],
    ];
}
