<?php

namespace JaxWilko\Game\Console;

use Illuminate\Console\Command;
use JaxWilko\Game\Classes\Engine\Engine;

class GameTick extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'game:tick';

    /**
     * @var string The console command description.
     */
    protected $description = 'Game Tick Command';

    /**
     * Execute the console command.
     * @return int
     */
    public function handle(): int
    {
        (new Engine())
            ->boot()
            ->execTick()
            ->execStore();
        return 0;
    }
}
