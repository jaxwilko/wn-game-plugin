<?php

namespace JaxWilko\Game\Classes\Engine\Modules\Player;

use JaxWilko\Game\Classes\Engine\Core\Contracts\Modules\PlayerModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Contracts\Modules\WorldModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Objects\Entities\Actor;
use JaxWilko\Game\Classes\Engine\Core\Objects\WorldObject;
use JaxWilko\Game\Classes\Engine\Core\Providers\QuestDataProvider;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use JaxWilko\Game\Classes\Engine\Engine;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

/**
 * @class Player
 */
class Player extends Actor
{
    public const DEFAULT_HEALTH = 100;
    public const DEFAULT_DAMAGE = 1;

    /**
     * @var string player id, uses connection id by default
     */
    public readonly string $id;

    /**
     * @var array settings object used to store data about the player such as camera size
     */
    protected array $settings = [];

    /**
     * @var array map of quest statuses for this player
     */
    protected array $questStatues = [];

    /**
     * @var int the speed of the player
     */
    protected int $speed;

    /**
     * @var int the damage this player can inflict
     */
    protected int $damage;

    /**
     * @var array|string[] settings excluded from toArray
     */
    protected array $hiddenSettings = [
        'online',
        'session'
    ];

    /**
     * @var array|array[] player sprite map
     */
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

    /**
     * Create a new player object, calls parent actor constructor after configuration.
     *
     * @param string $id
     * @param Vector $vector
     * @param Vector $size
     * @param int $health
     * @param int $damage
     * @param array $settings
     * @param array $inventory
     * @param array $questStatues
     */
    public function __construct(
        string $id,
        Vector $vector,
        Vector $size,
        int $health = self::DEFAULT_HEALTH,
        int $damage = 1,
        array $settings = [],
        array $inventory = [],
        array $questStatues = []
    ) {
        $this->id = $id;
        $this->settings = $settings;
        $this->inventory = $inventory;
        $this->questStatues = $questStatues;
        $this->damage = $damage;

        parent::__construct($vector, $size, $health);

        $this->speed = 2;
    }

    /**
     * Create a new instance of Player with a different id.
     *
     * @param string $id
     * @return $this
     */
    public function clone(string $id): static
    {
        return new static(
            $id,
            $this->vector,
            $this->size,
            $this->health,
            $this->damage,
            $this->settings,
            $this->inventory,
            $this->questStatues
        );
    }

    /**
     * Get/Set a settings value, if array is passed it sets the settings value as key val pair, if string is passed
     * it returns the value of the string key. Null returns the entire settings array
     *
     * @param string|array|null $settings
     * @return mixed
     */
    public function settings(string|array|null $settings = null): mixed
    {
        if (is_null($settings)) {
            return $this->settings;
        }

        if (is_string($settings)) {
            return array_get($this->settings, $settings);
        }

        foreach ($settings as $key => $value) {
            $this->settings[$key] = $value;
        }

        $this->save();

        return null;
    }

    /**
     * Sets the camera's position relative to its size and the player's position.
     *
     * @return $this
     */
    public function alignCamera(): static
    {
        $player = $this->getCenter();
        $camera = $this->settings('camera') ?? [
            'size' => [800, 600]
        ];

        $this->settings['camera']['vector'] = [
            (int) ($player->x() - ($camera['size'][0] / 2)),
            (int) ($player->y() - ($camera['size'][1] / 2)),
        ];

        return $this;
    }

    /**
     * Respawns the player, remapping their level in the world module if required.
     *
     * @param WorldModuleInterface $world
     * @param Level $level
     * @return $this
     */
    public function respawn(WorldModuleInterface $world, Level $level): static
    {
        $currentLevel = $world->getLevelFor($this);
        // Drop items
        $this->dropInventory($currentLevel);

        // Check if we need to remap the world for the player
        if ($currentLevel->id !== $level->id) {
            $world->removeActor($this);
            $world->addActor($level->id, $this);
            $world->store();
        }

        // Handle respawn
        $this->vector = $level->getSpawnablePosition(
            $level->getSpawn()?->getSurroundingArea(2) ?? new WorldObject(new Vector(10, 10), $this->size),
            $this->size
        ) ?? new Vector(10, 10);

        $this->alignCamera();
        $this->health = 100;

        return $this;
    }

    /**
     * Accepts a quest, if the quest can be auto completed then it is auto completed.
     *
     * @param string $quest
     * @param PlayerModuleInterface|null $playerModule
     * @return $this
     */
    public function acceptQuest(string $quest, ?PlayerModuleInterface $playerModule = null): static
    {
        $info = Engine::getProvider(QuestDataProvider::class)->getQuest($quest);

        if (!$info) {
            return $this;
        }

        $this->questStatues[$quest] = QuestDataProvider::QUEST_ACCEPTED;

        // Attempt to auto complete quest
        $this->completeQuest($quest, $playerModule);

        if ($this->questStatues[$quest] === QuestDataProvider::QUEST_ACCEPTED) {
            $playerModule?->playerMessage($this->id, sprintf('Accepted "%s"', $info['title']));
        }

        return $this;
    }

    /**
     * Checks if a player has previously accepted a quest.
     *
     * @param string $quest
     * @return bool
     */
    public function hasAcceptedQuest(string $quest): bool
    {
        return in_array($this->questStatues[$quest] ?? null, [
            QuestDataProvider::QUEST_ACCEPTED,
            QuestDataProvider::QUEST_COMPLETE
        ]);
    }

    /**
     * Attempts to complete a quest, validating against the quests `completion` callable if provided.
     *
     * @param string $quest
     * @param PlayerModuleInterface|null $playerModule
     * @return $this
     */
    public function completeQuest(string $quest, ?PlayerModuleInterface $playerModule = null): static
    {
        if (!$this->hasAcceptedQuest($quest)) {
            return $this;
        }

        $info = Engine::getProvider(QuestDataProvider::class)->getQuest($quest);

        $completion = $info['completion'] ?? null;

        if (is_callable($completion) && !$completion($this)) {
            return $this;
        }

        $playerModule?->playerMessage($this->id, sprintf('Completed "%s"', $info['title']));

        if (!empty($info['reward'])) {
            foreach ($info['reward'] as $item => $quantity) {
                $this->addInventoryItem($item, $quantity);
            }
        }

        $this->questStatues[$quest] = QuestDataProvider::QUEST_COMPLETE;
        return $this;
    }

    /**
     * Checks if the player has previously completed a quest.
     *
     * @param string $quest
     * @return bool
     */
    public function hasCompletedQuest(string $quest): bool
    {
        return isset($this->questStatues[$quest]) && $this->questStatues[$quest] === QuestDataProvider::QUEST_COMPLETE;
    }

    /**
     * Returns quests available to the player.
     *
     * @return array
     */
    public function getAvailableQuests(): array
    {
        $quests = Engine::getProvider(QuestDataProvider::class)->getQuests();

        $available = [];

        foreach ($quests as $quest => $info) {
            if ($this->hasCompletedQuest($quest) && (!isset($info['repeatable']) || !$info['repeatable'])) {
                continue;
            }

            if (empty($info['prerequisite'])) {
                $available[] = $quest;
                continue;
            }

            $valid = true;
            foreach ($info['prerequisite'] as $prerequisite) {
                if (!$this->hasCompletedQuest($prerequisite)) {
                    $valid = false;
                    break;
                }
            }

            if ($valid) {
                $available[] = $quest;
            }
        }

        return $available;
    }

    /**
     * Casts this object to an array, extending the parent object's casting.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'inventory' => $this->inventory,
            'availableQuests' => $this->getAvailableQuests(),
            'questStatues' => $this->questStatues,
            'settings' => array_filter(
                $this->settings,
                fn ($key) => !in_array($key, $this->hiddenSettings),
                ARRAY_FILTER_USE_KEY
            )
        ]);
    }
}
