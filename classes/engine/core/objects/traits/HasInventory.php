<?php

namespace JaxWilko\Game\Classes\Engine\Core\Objects\Traits;

use JaxWilko\Game\Classes\Engine\Core\Objects\Generic\GenericItemObject;
use JaxWilko\Game\Classes\Engine\Core\Providers\ItemDataProvider;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use JaxWilko\Game\Classes\Engine\Engine;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

/**
 * @trait HasInventory
 * Provides inventory support to `WorldObjects`
 */
trait HasInventory
{
    /**
     * @var array the objects current inventory
     */
    protected array $inventory = [];

    /**
     * Returns the objects current inventory
     *
     * @return array
     */
    public function getInventory(): array
    {
        return $this->inventory;
    }

    /**
     * Returns whether the object has an item in its inventory, and the has a specific quantity if specified
     *
     * @param string $code
     * @param int|null $quantity
     * @return bool
     */
    public function hasInventoryItem(string $code, ?int $quantity = null): bool
    {
        if (is_null($quantity)) {
            return isset($this->inventory[$code]);
        }

        return isset($this->inventory[$code]) && $this->inventory[$code]['quantity'] >= $quantity;
    }

    /**
     * Adds X amount of an item to the inventory
     *
     * @param string $code
     * @param int $quantity
     * @return $this
     */
    public function addInventoryItem(string $code, int $quantity = 1): static
    {
        if (!isset($this->inventory[$code])) {
            if (!Engine::getProvider(ItemDataProvider::class)->isItem($code)) {
                return $this;
            }

            $this->inventory[$code] = Engine::getProvider(ItemDataProvider::class)->getItem($code);
            $this->inventory[$code]['quantity'] = 0;
        }

        $this->inventory[$code]['quantity'] += $quantity;

        return $this;
    }

    /**
     * Removes X amount of an item to the inventory
     *
     * @param string $code
     * @param int $quantity
     * @return $this
     */
    public function removeInventoryItem(string $code, int $quantity = 1): static
    {
        $this->inventory[$code]['quantity'] -= $quantity;

        if ($this->inventory[$code]['quantity'] <= 0) {
            unset($this->inventory[$code]);
        }

        return $this;
    }

    /**
     * Triggers the usage of an item if it has one
     *
     * @param string $code
     * @return $this
     */
    public function useInventoryItem(string $code): static
    {
        if (!$this->hasInventoryItem($code)) {
            return $this;
        }

        if (!($callable = Engine::getProvider(ItemDataProvider::class)->getItemUse($code))) {
            return $this;
        }

        $callable($this);

        return $this;
    }

    /**
     * Drops an inventory item, creating a new object in the level
     *
     * @param Level $level
     * @param string $code
     * @return $this
     */
    public function dropInventoryItem(Level $level, string $code): static
    {
        if (!$this->hasInventoryItem($code)) {
            return $this;
        }

        $size = new Vector(32, 32);
        $level->pushLayer(
            $level::LAYER_TRIGGERS,
            new GenericItemObject(
                $level->getSpawnablePosition($this->getSurroundingArea(0.7), $size) ?? $this->vector->clone(),
                $size,
                [
                    'code' => $code,
                    'quantity' => 1
                ]
            )
        );

        // Remove the items from the inventory
        $this->removeInventoryItem($code, 1);

        return $this;
    }

    /**
     * Drops all items in the inventory to the level, uses a loot table if the object implements one
     *
     * @param Level $level
     * @return $this
     */
    public function dropInventory(Level $level): static
    {
        if (
            $this->lootTable
            && ($items = Engine::getProvider(ItemDataProvider::class)->getLootTable($this->lootTable))
        ) {
            foreach ($items as $code => $chance) {
                if (rand(1, 100) < ($chance * 100)) {
                    $this->addInventoryItem($code);
                }
            }
        }

        $size = new Vector(32, 32);
        foreach ($this->inventory as $code => $item) {
            // Create level objects
            $level->pushLayer(
                $level::LAYER_TRIGGERS,
                new GenericItemObject(
                    $level->getSpawnablePosition($this->getSurroundingArea(0.7), $size) ?? $this->vector->clone(),
                    $size->clone(),
                    [
                        'code' => $code,
                        'quantity' => $item['quantity']
                    ]
                )
            );
            // Remove the items from the inventory
            $this->removeInventoryItem($code, $item['quantity']);
        }
        return $this;
    }
}
