<?php

namespace JaxWilko\Game\Classes\Objects\Triggers;

use JaxWilko\Game\Classes\Engine\Core\Contracts\Modules\WorldModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Objects\StaticTriggerObject;
use JaxWilko\Game\Classes\Engine\Core\Objects\WorldObject;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use JaxWilko\Game\Classes\Engine\Engine;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

class Teleport extends StaticTriggerObject
{
    protected ?string $level = null;
    protected Vector|string $target;

    protected array $spriteMap = [
        'idle' => [
            'sheet' => '/storage/app/media/game/portal.png',
            'align' => 64,
            'delay' => 25
        ]
    ];

    protected bool $playersOnly = false;
    protected array $tracker = [];
    protected int $coolDown = 10;

    public function __construct(Vector $vector, Vector $size, array $settings = [])
    {
        if (!isset($settings['colour'])) {
            $settings['colour'] = '#1FC0C8';
        }

        if (isset($settings['spriteMap'])) {
            $this->spriteMap = $settings['spriteMap'];
        }

        if (isset($settings['target'])) {
            $this->target = Vector::fromString($settings['target']);
        }

        if (isset($settings['level'])) {
            $this->level = $settings['level'];
        }

        if (isset($settings['playersOnly'])) {
            $this->playersOnly = $settings['playersOnly'];
        }

        parent::__construct($vector, $size, $settings);
    }

    public function tick(Level $level, WorldModuleInterface $world): void
    {
        $entities = $this->playersOnly
            ? $this->getPlayersInside($level)
            : $this->getActorsInside($level);

        if (!$entities) {
            return;
        }

        foreach ($this->tracker as $id => $coolDown) {
            $this->tracker[$id] = $coolDown - 1;
            if ($this->tracker[$id] <= 0) {
                unset($this->tracker[$id]);
            }
        }

        if ($this->level) {
            $world = Engine::loadModule('world');
            $level = $world->loadLevel($this->level);

            foreach ($entities as $entity) {
                if (isset($this->tracker[$entity->id])) {
                    continue;
                }

                // Swap actor level
                $world->removeActor($entity);
                $world->addActor($this->level, $entity);
                $world->store();

                // Get a non-collision position
                $vector = $level->getSpawnablePosition(
                    (new WorldObject($this->target, $entity->size))->getSurroundingArea(),
                    $entity->getSize()->clone(),
                    fast: true
                ) ?? $this->target;

                // Set actor pos
                $entity->getVector()->set($vector->x, $vector->y);

                // Fix for player camera alignment
                if (method_exists($entity, 'alignCamera')) {
                    $entity->alignCamera();
                }

                $entity->save();

                $this->tracker[$entity->id] = $this->coolDown;
            }
        } else {
            foreach ($entities as $entity) {
                $entity->getVector()->set($this->target->x, $this->target->y);
                $entity->save();
            }
        }

        $this->save();
    }
}
