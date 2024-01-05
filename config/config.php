<?php

return [
    'server' => [
        'ticks' => env('JAX_GAME_TICKS', 64),
        'port' => env('JAX_GAME_PORT', 2345)
    ],
    'modules' => [
        'debug' => \JaxWilko\Game\Classes\Engine\Modules\Debug\DebugModule::class,
        'world' => \JaxWilko\Game\Classes\Engine\Modules\World\WorldModule::class,
        'ai' => \JaxWilko\Game\Classes\Engine\Modules\Ai\AiModule::class,
        'player' => \JaxWilko\Game\Classes\Engine\Modules\Player\PlayerModule::class,
    ],
    'data' => [
        \JaxWilko\Game\Classes\Engine\Core\Providers\ItemDataProvider::class,
        \JaxWilko\Game\Classes\Engine\Core\Providers\QuestDataProvider::class
    ],
    'debug' => [
        'debugCommands' => env('JAX_GAME_DEBUG', false),
        'printMemoryUsage' => false,
        'printEventStats' => false,
        'printDeepStats' => false,
    ]
];
