<?php

namespace JaxWilko\Game\Classes\Engine\Core\Contracts\Modules;

use JaxWilko\Game\Classes\Engine\Core\Contracts\GameModuleInterface;
use JaxWilko\Game\Classes\Engine\Modules\Player\Player;

interface PlayerModuleInterface extends GameModuleInterface
{
    public function hasPlayer(string $id): bool;

    public function addPlayer(string $id, ?string $session): void;

    public function restorePlayer(string $id, string $session): bool;

    public function getPlayer(string $id): ?Player;

    public function getPlayers(): ?array;

    public function removePlayer(string $id): void;

    public function controlPlayer(string $id, array $controls): void;

    public function playerSettings(string $id, array $settings): void;

    public function playerData(string $id): ?array;

    public function playerMessage(string $id, string $message): void;

    public function getPlayerMessages(Player|string $player): array;

    public function playerUseItem(string $id, array $data): void;

    public function playerDropItem(string $id, array $data): void;

    public function playerQuestAction(string $id, string $quest): void;

    public function tick(): void;
}
