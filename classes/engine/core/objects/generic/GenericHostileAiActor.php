<?php

namespace JaxWilko\Game\Classes\Engine\Core\Objects\Generic;

use JaxWilko\Game\Classes\Engine\Core\Objects\Entities\AiActor;
use JaxWilko\Game\Classes\Engine\Core\Objects\Traits\packages\EnemyAiPackage;
use JaxWilko\Game\Classes\Engine\Core\Objects\Traits\PhpScript;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

class GenericHostileAiActor extends AiActor
{
    use PhpScript;
    use EnemyAiPackage;

    protected array $blockingLayers = [
        Level::LAYER_BLOCKS,
        Level::LAYER_PROPS,
        Level::LAYER_ACTORS
    ];

    protected int $speed = 4;

    protected int $attackRange = 800;

    protected int $damage = 1;

    public function __construct(Vector $vector, Vector $size, array $settings = ['colour' => '#964B00'])
    {
        $this->configure($settings);
        parent::__construct($vector, $size);
    }
}
