<?php

namespace JaxWilko\Game\Classes\Objects\Entities;

use JaxWilko\Game\Classes\Engine\Core\Contracts\HasInventoryInterface;
use JaxWilko\Game\Classes\Engine\Core\Objects\Entities\AiActor;
use JaxWilko\Game\Classes\Engine\Core\Objects\Traits\HasInventory;
use JaxWilko\Game\Classes\Engine\Core\Objects\Traits\Packages\FriendlyAiPackage;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

class Npc extends AiActor implements HasInventoryInterface
{
    use HasInventory;
    use FriendlyAiPackage;

    protected ?string $name = 'Test Man';

    protected array $blockingLayers = [
        Level::LAYER_BLOCKS,
        Level::LAYER_PROPS,
        Level::LAYER_ACTORS
    ];

    protected int $speed = 4;

    protected int $attackRange = 800;

    protected int $damage = 1;

    protected bool $invulnerable = true;

    protected array $quests = [
        'questA',
        'questB'
    ];

    protected array $spriteMap = [
        'idle' => [
            'sheet' => '/storage/app/media/game/dude/idle.png',
            'align' => [32, 64],
            'delay' => 45
        ],
        'attack' => [
            'sheet' => '/storage/app/media/game/dude/attack.png',
            'align' => [32, 64],
            'delay' => 10
        ],
        'down' => [
            'sheet' => '/storage/app/media/game/dude/walk-down.png',
            'align' => [32, 64],
            'delay' => 15
        ],
        'up' => [
            'sheet' => '/storage/app/media/game/dude/walk-up.png',
            'align' => [32, 64],
            'delay' => 15
        ],
        'left' => [
            'sheet' => '/storage/app/media/game/dude/walk-left.png',
            'align' => [32, 64],
            'delay' => 15
        ],
        'right' => [
            'sheet' => '/storage/app/media/game/dude/walk-right.png',
            'align' => [32, 64],
            'delay' => 15
        ],
    ];

    public function __construct(Vector $vector, Vector $size, array $settings = [])
    {
        if (isset($settings['name'])) {
            $this->name = $settings['name'];
        }

        if (isset($settings['spriteMap'])) {
            $this->spriteMap = $settings['spriteMap'];
        }

        if (isset($settings['items'])) {
            foreach ($settings['items'] as $item => $quantity) {
                $this->addInventoryItem($item, $quantity);
            }
        }

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
            'settings' => [
                'name' => $this->name,
            ],
            'inventory' => $this->inventory,
            'quests' => $this->quests,
            'players' => $this->players
        ]);
    }
}
