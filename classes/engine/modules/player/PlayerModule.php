<?php

namespace JaxWilko\Game\Classes\Engine\Modules\Player;

use JaxWilko\Game\Classes\Engine\Core\Contracts\HasInventoryInterface;
use JaxWilko\Game\Classes\Engine\Core\Contracts\Modules\PlayerModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Contracts\Modules\WorldModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Events\Events;
use JaxWilko\Game\Classes\Engine\Core\Modules\GameModule;
use JaxWilko\Game\Classes\Engine\Core\Objects\WorldObject;
use JaxWilko\Game\Classes\Engine\Core\Providers\QuestDataProvider;
use JaxWilko\Game\Classes\Engine\Core\Utils\Console;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use JaxWilko\Game\Classes\Engine\Engine;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;
use System\Classes\PluginManager;

/**
 * @class PlayerModule
 * This module is responsible for handling player input and receiving data to send back to the client.
 *
 * @property array<string> $players
 * @property array<array> $messages
 */
class PlayerModule extends GameModule implements PlayerModuleInterface
{
    /**
     * @const defines the PlayerModule cache key
     */
    public const CACHE_KEY = Engine::CACHE_KEY . '.players';

    /**
     * @var array defines engine events to be executed on matching local methods
     */
    protected array $emit = [
        'addPlayer',
        'removePlayer',
        'controlPlayer',
        'playerMessage',
        'playerSettings',
        'playerUseItem',
        'playerDropItem',
        'playerQuestAction',
    ];

    /**
     * @var array defines engine retrievable events on matching local methods
     */
    protected array $stats = [
        'hasPlayer',
        'playerData'
    ];

    /**
     * @var array allows for access to state keys via magic __get
     */
    protected array $props = [
        'players',
        'messages'
    ];

    /**
     * @var array custom commands for the chat system, driven by plugin registration
     */
    protected array $commands = [];

    /**
     * Registers the module, adding tick to the engine tick event and defines the players state array if null.
     *
     * @param Events $events
     * @return void
     */
    public function register(Events $events): void
    {
        if (!$this->players) {
            $this->players = [];
        }

        $events->listen('tick', [$this, 'tick']);
    }

    /**
     * Check if a player is in the players array by connection id.
     *
     * @param string $id
     * @return bool
     */
    public function hasPlayer(string $id): bool
    {
        return in_array($id, $this->players);
    }

    /**
     * Get a Player object by connection id.
     *
     * @param string $id
     * @return Player|null
     */
    public function getPlayer(string $id): ?Player
    {
        return Player::load($id);
    }

    /**
     * Get all loaded players.
     *
     * @return string[]|null
     */
    public function getPlayers(): ?array
    {
        return $this->players;
    }

    /**
     * Add a player to the game, takes the connection id and optional session id.
     *
     * @param string $id
     * @param string|null $session
     * @return void
     */
    public function addPlayer(string $id, ?string $session): void
    {
        if ($session && $this->restorePlayer($id, $session)) {
            return;
        }

        $player = $this->com->world(function (WorldModuleInterface $world) use ($id, $session) {
            $level = $world->getDefaultLevel();
            $size = new Vector(32, 64);
            $area = $level->getSpawn()?->getSurroundingArea(1.5);
            return $world->addActor($level->id, new Player(
                $id,
                ($area ? $level->getSpawnablePosition($area, $size) : new Vector(10, 10)) ?? new Vector(10, 10),
                $size,
                Player::DEFAULT_HEALTH,
                Player::DEFAULT_DAMAGE,
                [
                    'camera' => [
                        'vector' => [0, 0],
                        'size' => [800, 600]
                    ],
                    'online' => true,
                    'session' => $session
                ]
            ));
        });

        Console::put('Added player: ' . $id . ' [' . request()->ip() . ']');

        $this->players[] = $id;

        $player->save();
        $this->store();

        $this->controlPlayer($id, []);
    }

    /**
     * Restore a player who was previously connected via a different connection (i.e. page refresh).
     *
     * @param string $id
     * @param string $session
     * @return bool
     */
    public function restorePlayer(string $id, string $session): bool
    {
        foreach ($this->players as $playerId) {
            $player = Player::load($playerId);
            if ($player->settings('session') === $session) {
                $this->players[] = $id;
                unset($this->players[array_search($player->id, $this->players)]);

                $newPlayer = $player->clone($id);

                $this->com->world(function (WorldModuleInterface $world) use ($player, $newPlayer) {
                    $level = $world->getLevelFor($player);
                    $world->removeActor($player);
                    $world->addActor($level->id, $newPlayer);
                });

                // Delete the old player data
                $player->delete();

                // Set the new player's online status true
                $newPlayer->settings(['online' => true]);
                $newPlayer->save();

                Console::put('Restored player: ' . $id . ' [' . request()->ip() . ']');

                $this->store();

                return true;
            }
        }

        return false;
    }

    /**
     * Remove a player from the game, keeps the player object in cache but sets their status to offline.
     *
     * @param string $id
     * @return void
     */
    public function removePlayer(string $id): void
    {
        if ($this->players && $player = $this->getPlayer($id)) {
            $player->settings(['online' => false]);
        }

        Console::put('Player offline: ' . $id . ' [' . request()->ip() . ']');

        $this->store();
    }

    /**
     * Applies an array of controls for a player, uses the Actor::applyControls method and aligns the player camera
     * after update.
     *
     * @param string $id
     * @param array $controls
     * @return void
     */
    public function controlPlayer(string $id, array $controls): void
    {
        $this->com->world(function (WorldModuleInterface $world) use ($id, $controls) {
            // Load player object
            $player = $this->getPlayer($id);
            // Apply controls
            $player->applyControls($controls, $world->getLevelFor($player))
                ?->alignCamera()
                ?->save();
        });
    }

    /**
     * Updates a players settings.
     *
     * @param string $id
     * @param array $settings
     * @return void
     */
    public function playerSettings(string $id, array $settings): void
    {
        $this->getPlayer($id)->settings($settings);

        Console::put('Player: ' . $id . ' updated settings [' . implode(', ', array_keys($settings)) . ']');

        // Fix camera alignment on resize
        if (isset($settings['camera']['size'])) {
            $this->getPlayer($id)->alignCamera()->save();
        }
    }

    /**
     * Retrieves the render data needed for a player. Uses the players camera settings and vector to generate data
     * specifically for their view.
     *
     * @param string $id
     * @return array|null
     */
    public function playerData(string $id): ?array
    {
        $player = $this->getPlayer($id);

        if (!$player) {
            return null;
        }

        return [
            'player' => $player->toArray(),
            'world' => $this->com->world(function (WorldModuleInterface $world) use ($player) {
                $level = $world->getLevelFor($player);
                if (!$level) {
                    Console::put($player->id . ' has no level');
                    return [];
                }

                return [
                    'background' => $level->background,
                    'level' => [
                        'id' => $level->id,
                        'name' => $level->name,
                        'size' => array_map(fn (Vector $vector) => $vector->get(), $level->getSize()),
                        'void' => $level->void
                    ],
                    'layers' => $level->search(
                        new WorldObject(
                            new Vector(...($player->settings('camera.vector') ?? [0, 0])),
                            new Vector(...($player->settings('camera.size') ?? [800, 600]))
                        ),
                        asObjects: false
                    ),
                    'messages' => $this->getPlayerMessages($player)
                ];
            })
        ];
    }

    /**
     * Handles player messaging, if required processing player commands.
     *
     * @param string $id
     * @param string $message
     * @return void
     */
    public function playerMessage(string $id, string $message): void
    {
        $time = microtime(true);
        $timeStamp = str_replace('.', '', $time);
        $player = $this->getPlayer($id);

        if (str_starts_with($message, '/')) {
            $this->playerCommand($id, $message, $player, $timeStamp, $time);
            return;
        }

        $playerName = $player->settings('name');

        Console::put('%s: %s', $playerName, strip_tags($message));

        foreach ($this->players as $player) {
            $this->messages[$player][$timeStamp] = $this->packMessage(
                $playerName,
                $message,
                $time
            );
        }
    }

    /**
     * Executes a player command entered into the chat console.
     *
     * @param string $id
     * @param string $message
     * @param Player $player
     * @param string $timeStamp
     * @param float $time
     * @return void
     */
    protected function playerCommand(string $id, string $message, Player $player, string $timeStamp, float $time): void
    {
        preg_match('/^\/(\w*)/', $message, $matches);
        $command = $matches[1] ?? null;

        if (!$command) {
            return;
        }

        if (empty($this->commands)) {
            $this->commands = $this->registerPlayerCommands();
        }

        $callback = $this->commands[$command] ?? null;

        if (!$callback) {
            $this->messages[$id][$timeStamp] = $this->packMessage('System', 'Unknown command', $time);
            return;
        }

        $callback(...)->call($this, $id, $message, $player, $timeStamp, $time);
    }

    /**
     * Returns an array of available commands via plugin registration methods.
     *
     * @return array
     */
    protected function registerPlayerCommands(): array
    {
        $commands = [
            'w' => function (string $id, string $message, Player $player, string $timeStamp, float $time) {
                // @TODO: fix
                $this->messages[$id][$timeStamp] = $this->packMessage('System', '@TODO: Fix this', $time);
            },
            'kill' => function (string $id, string $message, Player $player, string $timeStamp, float $time) {
                $player->thenSave(fn ($player) => $player->damage(100));
            }
        ];

        if (config('jaxwilko.game::debug.debugCommands', false)) {
            $commands = array_merge($commands, [
                'additem' => function (string $id, string $message, Player $player, string $timeStamp, float $time) {
                    preg_match('/^\/(\w*) (\w*) (\d*)/', $message, $matches);
                    if (!$matches) {
                        $this->messages[$id][$timeStamp] = $this->packMessage('System', 'Unknown command', $time);
                        return;
                    }

                    $player->addInventoryItem($matches[2] ?? null, (int)$matches[3] ?? 1)
                        ->save();

                    $this->messages[$id][$timeStamp] = $this->packMessage(
                        'System',
                        'Item(s) added to inventory',
                        $time
                    );
                },
                'setpos' => function (string $id, string $message, Player $player, string $timeStamp, float $time) {
                    preg_match('/^\/(\w*) (\d*) (\d*)/', $message, $matches);
                    if (!$matches) {
                        $this->messages[$id][$timeStamp] = $this->packMessage('System', 'Unknown command', $time);
                        return;
                    }
                    $this->getPlayer($id)
                        ->thenSave(fn($player) => $player->getVector()->set((int)$matches[1], (int)$matches[2]));
                }
            ]);
        }

        return array_merge(
            $commands,
            ...array_values(PluginManager::instance()->getRegistrationMethodValues('registerGamePlayerCommands')),
        );
    }

    /**
     * Creates a message array to be sent to the client
     *
     * @param string $name
     * @param string $message
     * @param float|null $time
     * @return array
     */
    protected function packMessage(string $name, string $message, ?float $time = null): array
    {
        $time = $time ?? microtime(true);
        return [
            'user' => $name,
            'content' => $message,
            'time' => $time,
            'humanTime' => date('H:i:s', (int) $time)
        ];
    }

    /**
     * Fetches new messages for a specific player
     *
     * @param Player|string $player
     * @return array
     */
    public function getPlayerMessages(Player|string $player): array
    {
        $id = is_string($player) ? $player : $player->id;
        $messages = $this->messages[$id] ?? [];
        unset($this->messages[$id]);
        return $messages;
    }

    /**
     * Allows players to execute the usage of an item, if the item is valid, and they have one in their inventory.
     * Called when a player clicks on an item in their inventory.
     *
     * @param string $id
     * @param array $data
     * @return void
     */
    public function playerUseItem(string $id, array $data): void
    {
        if (empty($data['item']) || empty($data['id'])) {
            return;
        }

        $player = $this->getPlayer($id);

        if ($data['id'] === 'player') {
            if (!$player->hasInventoryItem($data['item'])) {
                return;
            }

            $trigger = $this->getInventoryTrigger($player);

            if (!$trigger) {
                $player->useInventoryItem($data['item']);
                return;
            }

            $player->thenSave(fn ($player) => $player->removeInventoryItem($data['item']));
            $trigger->thenSave(fn ($trigger) => $trigger->addInventoryItem($data['item']));
            return;
        }

        $trigger = $this->getInventoryTrigger($player);

        if ($trigger && $trigger->id === $data['id']) {
            if (!$trigger->hasInventoryItem($data['item'])) {
                return;
            }

            $trigger->thenSave(fn ($trigger) => $trigger->removeInventoryItem($data['item']));
            $player->thenSave(fn ($player) => $player->addInventoryItem($data['item']));
        }
    }

    /**
     * Allows players to drop items from inventory into the level. Called when a player right-clicks an item in their
     * Inventory.
     *
     * @param string $id
     * @param array $data
     * @return void
     */
    public function playerDropItem(string $id, array $data): void
    {
        if (empty($data['item']) || empty($data['id'])) {
            return;
        }

        $player = $this->getPlayer($id);

        if ($data['id'] !== 'player') {
            return;
        }

        if (!$player->hasInventoryItem($data['item'])) {
            return;
        }

        $player->thenSave(function ($player) use ($data) {
            $player->dropInventoryItem(
                $this->com->world(fn(WorldModuleInterface $world) => $world->getLevelFor($player)),
                $data['item']
            );
        });
    }

    /**
     * Triggers when a player interacts with a quest, is called when a player clicks on a quest from an NPC.
     *
     * @param string $id
     * @param string $quest
     * @return void
     */
    public function playerQuestAction(string $id, string $quest): void
    {
        $questProvider = $this->engine::getProvider(QuestDataProvider::class);

        if (!$questProvider->hasQuest($quest)) {
            return;
        }

        $player = $this->getPlayer($id);

        if (!$player->hasAcceptedQuest($quest)) {
            $player->thenSave(fn ($player) => $player->acceptQuest($quest, $this));
            return;
        }

        $player->thenSave(fn ($player) => $player->completeQuest($quest, $this));
    }

    /**
     * Returns triggers that implement HasInventoryInterface if they intersect with the player
     *
     * @param Player $player
     * @return HasInventoryInterface|null
     */
    protected function getInventoryTrigger(Player $player): ?HasInventoryInterface
    {
        return $this->com->world(function (WorldModuleInterface $world) use ($player): ?HasInventoryInterface {
            return array_filter(
                $world->getLevelFor($player)->search($player, [Level::LAYER_TRIGGERS], true, false, true),
                fn ($object) => $object instanceof HasInventoryInterface
            )[0] ?? null;
        });
    }

    /**
     * Engine tick event handler for the player module, respawns dead players if required.
     *
     * @return void
     */
    public function tick(): void
    {
        $time = microtime(true);
        $timeStamp = str_replace('.', '', $time);

        foreach ($this->players as $id) {
            $player = Player::load($id);

            if (!$player) {
                continue;
            }

            if (!$player->alive()) {
                Console::put('Player %s has died', $id);
                $this->messages[$id][$timeStamp] = $this->packMessage(
                    'System',
                    'You have died.',
                    $time
                );
                $player->respawn(
                    ...$this->com->world(fn (WorldModuleInterface $world) => [$world, $world->getDefaultLevel()])
                );
            }

            $player->save();
        }
    }

    /**
     * Returns the state of the module
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'players' => array_map(function (string $player) {
                return Player::load($player);
            }, $this->players ?? [])
        ];
    }
}
