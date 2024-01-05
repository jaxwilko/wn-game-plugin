<?php

namespace JaxWilko\Game\Classes\Engine\Core\Contracts;

use JaxWilko\Game\Classes\Engine\Modules\World\Level;

interface HasInventoryInterface
{
    public function getInventory(): array;

    public function hasInventoryItem(string $code): bool;

    public function addInventoryItem(string $code, int $quantity = 1): static;

    public function removeInventoryItem(string $code, int $quantity = 1): static;

    public function useInventoryItem(string $code): static;

    public function dropInventory(Level $level): static;
}
