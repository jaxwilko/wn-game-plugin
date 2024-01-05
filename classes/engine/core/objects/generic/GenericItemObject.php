<?php

namespace JaxWilko\Game\Classes\Engine\Core\Objects\Generic;

use JaxWilko\Game\Classes\Engine\Core\Contracts\Modules\WorldModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Providers\ItemDataProvider;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use JaxWilko\Game\Classes\Engine\Core\Objects\StaticTriggerObject;
use JaxWilko\Game\Classes\Engine\Engine;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

class GenericItemObject extends StaticTriggerObject
{
    protected string $code;
    protected array $item;
    protected int $quantity = 1;

    public function __construct(Vector $vector, Vector $size, array $settings = ['colour' => '#DEA010'])
    {
        if (isset($settings['code'])) {
            $this->code = $settings['code'];
            $this->item = Engine::getProvider(ItemDataProvider::class)->getItem($this->code);

            $this->spriteMap = $this->item['spriteMap'] ?? [
                'idle' => [
                    'sheet' => $this->item['icon'],
                    'align' => [32, 32],
                    'delay' => 999
                ],
            ];

            if (isset($this->item['size'])) {
                $size->x($this->item['size'][0]);
                $size->y($this->item['size'][1]);
            }
        }

        if (isset($settings['quantity'])) {
            $this->quantity = $settings['quantity'];
        }

        if (isset($settings['colour'])) {
            $this->colour = $settings['colour'];
        }

        parent::__construct($vector, $size, $settings);
    }

    public function tick(Level $level, WorldModuleInterface $world): void
    {
        $players = $this->getPlayersInside($level);

        if (empty($players)) {
            return;
        }

        array_first($players)->thenSave(fn ($player) => $player->addInventoryItem($this->code, $this->quantity));
        $level->removeFromLayer($level::LAYER_TRIGGERS, $this);
        $this->delete();
    }
}
