<?php

namespace JaxWilko\Game\Classes\Engine\Core\Contracts\Modules;

use JaxWilko\Game\Classes\Engine\Core\Contracts\GameModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Objects\Entities\Actor;
use JaxWilko\Game\Classes\Engine\Core\Objects\WorldObject;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

interface WorldModuleInterface extends GameModuleInterface
{
    public function setLevel(Level $level): Level;

    public function getLevel(string $id): ?Level;

    public function loadLevel(string $levelId): ?Level;

    public function getLevels(): array;

    public function getDefaultLevel(): Level;

    public function addActor(string $levelId, WorldObject $object): WorldObject;

    public function removeActor(WorldObject $object): void;

    public function getLevelFor(Actor $entity): ?Level;
}
