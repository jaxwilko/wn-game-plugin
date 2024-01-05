<?php

namespace JaxWilko\Game\Classes\Engine\Core\Providers;

use JaxWilko\Game\Classes\Engine\Core\Contracts\DataProviderInterface;
use JaxWilko\Game\Classes\Engine\Core\Objects\Entities\Entity;
use JaxWilko\Game\Classes\Engine\Core\Utils\Script;
use JaxWilko\Game\Models\Item;
use JaxWilko\Game\Models\LootTable;
use System\Classes\PluginManager;

class ItemDataProvider implements DataProviderInterface
{
    protected array $items = [];
    protected array $itemUses = [];
    protected array $lootTables = [];

    public function register(): static
    {
        $gameItems = array_merge(
            ...array_values(PluginManager::instance()->getRegistrationMethodValues('registerGameItems')),
            ...Item::all()->map(fn ($item) => $item->getDataArray())->toArray()
        );

        foreach ($gameItems as $key => $item) {
            if (isset($item['usage'])) {
                // Compile the script if it is a string
                $this->itemUses[$key] = is_string($item['usage'])
                    ? Script::compile($item['usage'], [Entity::class . ' $entity'])
                    : $item['usage'];
                // Unset it from the item data to allow for parent object serialization
                unset($item['usage']);
            }

            $this->items[$key] = $item;
        }

        $lootTables = array_merge(
            ...array_values(PluginManager::instance()->getRegistrationMethodValues('registerGameLootTable')),
            ...LootTable::all()->map(fn ($item) => $item->getDataArray())->toArray()
        );

        foreach ($lootTables as $key => $item) {
            $this->lootTables[$key] = $item;
        }

        return $this;
    }

    public function getLootTable(string $item): ?array
    {
        return $this->lootTables[$item] ?? null;
    }

    public function getItemUse(string $item): ?callable
    {
        return $this->itemUses[$item] ?? null;
    }

    public function isItem(string $code): bool
    {
        return isset($this->items[$code]);
    }

    public function getItem(string $code): ?array
    {
        return $this->items[$code] ?? null;
    }

    public function getValue(string $code): ?int
    {
        return $this->items[$code]['value'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'items' => $this->items,
            'lootTables' => $this->lootTables
        ];
    }
}
