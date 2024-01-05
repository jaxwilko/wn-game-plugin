<?php

namespace JaxWilko\Game\Classes\Engine\Core\Objects\Generic;

use JaxWilko\Game\Classes\Engine\Core\Objects\Entities\AiActor;
use JaxWilko\Game\Classes\Engine\Core\Objects\Traits\Packages\FriendlyAiPackage;
use JaxWilko\Game\Classes\Engine\Core\Objects\Traits\PhpScript;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

class GenericAiActor extends AiActor
{
    use FriendlyAiPackage;
    use PhpScript;

    protected array $blockingLayers = [
        Level::LAYER_BLOCKS,
        Level::LAYER_PROPS,
        Level::LAYER_ACTORS
    ];

    protected int $speed = 4;

    protected int $attackRange = 800;

    protected int $damage = 1;

    protected array $quests = [];

    public function __construct(Vector $vector, Vector $size, array $settings = ['colour' => '#964B00'])
    {
        $this->configure($settings);

        if (isset($settings['quests'])) {
            foreach ($settings['quests'] as $quest) {
                $this->quests[] = $quest;
            }
        }

        parent::__construct($vector, $size);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'quests' => $this->quests,
            'players' => $this->players
        ]);
    }
}
