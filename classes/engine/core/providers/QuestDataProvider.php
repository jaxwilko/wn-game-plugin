<?php

namespace JaxWilko\Game\Classes\Engine\Core\Providers;

use JaxWilko\Game\Classes\Engine\Core\Contracts\DataProviderInterface;
use JaxWilko\Game\Classes\Engine\Core\Utils\Script;
use JaxWilko\Game\Classes\Engine\Modules\Player\Player;
use JaxWilko\Game\Models\Quest;
use System\Classes\PluginManager;

class QuestDataProvider implements DataProviderInterface
{
    public const QUEST_ACCEPTED = 1;
    public const QUEST_COMPLETE = 2;

    protected array $quests = [];

    public function register(): static
    {
        $quests = array_merge(
            ...array_values(PluginManager::instance()->getRegistrationMethodValues('registerGameQuests')),
            ...Quest::all()->map(fn ($item) => $item->getDataArray())->toArray()
        );

        foreach ($quests as $key => $quest) {
            if (!empty($quest['completion'])) {
                // Compile the script if it is a string
                $quest['completion'] = is_string($quest['completion'])
                    ? Script::compile($quest['completion'], [Player::class . ' $player'])
                    : $quest['completion'];
            }
            $this->quests[$key] = $quest;
        }

        return $this;
    }

    public function hasQuest(string $quest): bool
    {
        return isset($this->quests[$quest]);
    }

    public function getQuest(string $quest): ?array
    {
        return $this->quests[$quest] ?? null;
    }

    public function getQuests(): array
    {
        return $this->quests;
    }

    public function toArray(): array
    {
        return [
            'quests' => $this->quests,
        ];
    }
}
