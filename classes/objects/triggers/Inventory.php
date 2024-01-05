<?php

namespace JaxWilko\Game\Classes\Objects\Triggers;

use JaxWilko\Game\Classes\Engine\Core\Contracts\HasInventoryInterface;
use JaxWilko\Game\Classes\Engine\Core\Contracts\Modules\WorldModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Objects\StaticTriggerObject;
use JaxWilko\Game\Classes\Engine\Core\Objects\Traits\HasInventory;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

class Inventory extends StaticTriggerObject implements HasInventoryInterface
{
    use HasInventory;

    protected string $containerName = 'Chest';
    protected array $players = [];

    public function __construct(Vector $vector, Vector $size, array $settings = [])
    {
        if (!isset($settings['colour'])) {
            $settings['colour'] = '#1FC0C8';
        }

        if (isset($settings['containerName'])) {
            $this->containerName = $settings['containerName'];
        }

        if (isset($settings['spriteMap'])) {
            $this->spriteMap = $settings['spriteMap'];
        }

        if (isset($settings['items'])) {
            foreach ($settings['items'] as $item => $quantity) {
                $this->addInventoryItem($item, $quantity);
            }
        }

        parent::__construct($vector, $size, $settings);
    }

    public function tick(Level $level, WorldModuleInterface $world): void
    {
        $this->players = array_map(fn ($player) => $player->id, $this->getPlayersInside($level));
        $this->animate(empty($this->players) ? 'idle' : 'open');
        $this->save();
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'containerName' => $this->containerName,
            'inventory' => $this->inventory,
            'players' => $this->players
        ]);
    }
}
