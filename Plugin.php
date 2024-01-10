<?php

namespace JaxWilko\Game;

use Backend;
use Event;
use JaxWilko\Game\Classes\Engine\Core\Objects\Entities\Entity;
use JaxWilko\Game\Classes\Engine\Modules\Player\Player;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;
use JaxWilko\Game\FormWidgets\LevelEditor;
use System\Classes\PluginBase;
use Url;

class Plugin extends PluginBase
{
    public $require = [
        'Winter.User'
    ];

    public function pluginDetails()
    {
        return [
            'name'          => 'Winter Game',
            'description'   => 'Provides a Game Engine for Winter CMS',
            'author'        => 'Jack Wilkinson',
            'icon'          => 'icon-refresh',
            'homepage'      => 'https://github.com/jaxwilko/wn-game-plugin'
        ];
    }

    public function registerComponents()
    {
        return [
            \JaxWilko\Game\Components\Game::class => 'game'
        ];
    }

    public function register()
    {
        $this->registerConsoleCommand('game.serve', Console\GameServe::class);
        $this->registerConsoleCommand('game.tick', Console\GameTick::class);
        $this->registerConsoleCommand('game.flush', Console\GameFlush::class);
        $this->registerConsoleCommand('game.publish', Console\GamePublish::class);
        $this->registerConsoleCommand('game.noop', Console\Noop::class);
    }

    public function boot()
    {
        Event::listen('system.console.mirror.extendPaths', function ($paths) {
            $paths->directories = array_merge($paths->directories, ['plugins/jaxwilko/game/classes/engine/assets']);
        });
    }

    public function registerNavigation()
    {
        return [
            'game' => [
                'label' =>  'Game',
                'url' => Backend::url('jaxwilko/game/controlpanel'),
                'icon' =>  'icon-tower-broadcast',
                'iconSvg' => Url::asset('plugins/jaxwilko/game/assets/img/icon.png'),
                'permissions' => ['jaxwilko.game.*'],
                'sideMenu' => [
                    'controlPanel' => [
                        'label' => 'Control Panel',
                        'url' => Backend::url('jaxwilko/game/controlpanel'),
                        'icon' => 'icon-tower-broadcast',
                        'permissions' => ['jaxwilko.game.controlpanel'],
                    ],
                    'levels' => [
                        'label' => 'Levels',
                        'url' => Backend::url('jaxwilko/game/levels'),
                        'icon' => 'icon-map-location-dot',
                        'permissions' => ['jaxwilko.game.levels'],
                    ],
                    'items' => [
                        'label' => 'Items',
                        'url' => Backend::url('jaxwilko/game/items'),
                        'icon' => 'icon-cookie-bite',
                        'permissions' => ['jaxwilko.game.items'],
                    ],
                    'lootTables' => [
                        'label' => 'Loot Tables',
                        'url' => Backend::url('jaxwilko/game/loottables'),
                        'icon' => 'icon-sack-dollar',
                        'permissions' => ['jaxwilko.game.items'],
                    ],
                    'quests' => [
                        'label' => 'Quests',
                        'url' => Backend::url('jaxwilko/game/quests'),
                        'icon' => 'icon-person-circle-question',
                        'permissions' => ['jaxwilko.game.quests'],
                    ],
                ],
            ],
        ];
    }

    public function registerFormWidgets(): array
    {
        return [
            \JaxWilko\Game\FormWidgets\LevelEditor::class => 'leveleditor',
        ];
    }

    public function registerGameItems(): array
    {
        return [
            'gold' => [
                'label' => 'Gold',
                'description' => 'It gold',
                'value' => 1,
                'size' => [24, 24],
                // icon 42x42
                'icon' => '/storage/app/media/game/gold/icon.png',
                'spriteMap' => [
                    'idle' => [
                        // sprite 24x24 (probably keep 16x16 with border)
                        'sheet' => '/storage/app/media/game/gold/gold.png',
                        'align' => [24, 24],
                        'delay' => 20
                    ],
                ],
            ],
        ];
    }

    public function registerGameLootTable(): array
    {
        return [
            'npc' => [
                'meat' => 1 / 2,
                'gold' => 1 / 6
            ]
        ];
    }

    public function registerGameQuests(): array
    {
        return [
            'exampleA' => [
                'title' => 'Acquire meat',
                'description' => 'Return 3 meat to the quest giver',
                'reward' => [
                    'gold' => 5
                ],
                'repeatable' => false,
                'completion' => function (Player $player): bool {
                    if (!$player->hasInventoryItem('meat', 3)) {
                        return false;
                    }

                    $player->removeInventoryItem('meat', 3);
                    return true;
                }
            ],
            'exampleB' => [
                'title' => 'Acquire financial stability',
                'description' => 'Just complete me',
                'prerequisite' => [
                    'questA'
                ],
                'reward' => [
                    'gold' => 10
                ]
            ],
            'exampleC' => [
                'title' => 'Buy Meat',
                'description' => 'Exchange 1 gold for 1 meat',
                'reward' => [
                    'meat' => 1
                ],
                'repeatable' => true,
                'completion' => function (Player $player): bool {
                    if (!$player->hasInventoryItem('gold')) {
                        return false;
                    }

                    $player->removeInventoryItem('gold');
                    return true;
                }
            ],
        ];
    }

    public function registerGamePlayerCommands(): array
    {
        return [
            'pos' => function (string $id, string $message, Player $player, string $timeStamp, float $time) {
                $this->messages[$id][$timeStamp] = $this->packMessage(
                    'System',
                    $player->getVector()->toString(),
                    $time
                );
            },
        ];
    }

    public function registerGameObjects(): array
    {
        return [
            Level::LAYER_BACKGROUND => [
                Classes\Objects\Backgrounds\Grass::class => 'Grass'
            ],
            Level::LAYER_TRIGGERS => [
                Classes\Objects\Triggers\Teleport::class => 'Teleport',
                Classes\Objects\Triggers\Spawner::class => 'Spawner',
                Classes\Objects\Triggers\Spawn::class => 'Level Spawn Point',
                Classes\Objects\Triggers\Inventory::class => 'Inventory',
                Classes\Objects\Triggers\Fire::class => 'Fire'
            ],
            Level::LAYER_ACTORS => [
                Classes\Objects\Entities\Npc::class => 'Human NPC',
                Classes\Objects\Entities\Zombie::class => 'Zombie',
            ],
        ];
    }

    public function registerGameObjectOptions(): array
    {
        return [
            Classes\Objects\Triggers\Teleport::class => [
                LevelEditor::OBJECT_OPTION_TELEPORT,
                LevelEditor::OBJECT_OPTION_PLAYERS_ONLY,
                LevelEditor::OBJECT_OPTION_SPRITE_MAP,
                LevelEditor::OBJECT_OPTION_ANIMATION_RANDOM_DELAY,
            ],
            Classes\Objects\Triggers\Inventory::class => [
                LevelEditor::OBJECT_OPTION_INVENTORY,
                LevelEditor::OBJECT_OPTION_SPRITE_MAP,
                LevelEditor::OBJECT_OPTION_ANIMATION_RANDOM_DELAY,
            ],
            Classes\Objects\Triggers\Spawner::class => [
                LevelEditor::OBJECT_OPTION_SPRITE_MAP,
                LevelEditor::OBJECT_OPTION_ANIMATION_RANDOM_DELAY,
            ],
            Classes\Objects\Triggers\Spawn::class => [
                LevelEditor::OBJECT_OPTION_SPRITE_MAP,
                LevelEditor::OBJECT_OPTION_ANIMATION_RANDOM_DELAY,
            ],
            Classes\Objects\Entities\Npc::class => [
                LevelEditor::OBJECT_OPTION_SPRITE_MAP,
                LevelEditor::OBJECT_OPTION_ANIMATION_RANDOM_DELAY,
                LevelEditor::OBJECT_OPTION_INVULNERABLE,
                LevelEditor::OBJECT_OPTION_NAME,
                LevelEditor::OBJECT_OPTION_QUESTS,
                LevelEditor::OBJECT_OPTION_SCRIPT,
            ],
            Classes\Objects\Entities\Zombie::class => [
                LevelEditor::OBJECT_OPTION_SPRITE_MAP,
                LevelEditor::OBJECT_OPTION_ANIMATION_RANDOM_DELAY,
                LevelEditor::OBJECT_OPTION_SCRIPT,
            ]
        ];
    }

    public function registerGameLevels(): array
    {
        if (!config('jaxwilko.game::debug.debugCommands', false)) {
            return [];
        }

        return [
            'pathing' => __DIR__ . '/classes/levels/pathing.json',
            'sprites' => __DIR__ . '/classes/levels/sprites.json',
            'teleport' => __DIR__ . '/classes/levels/teleport.json',
            'triggers' => __DIR__ . '/classes/levels/triggers.json',
        ];
    }
}
