<?php

namespace JaxWilko\Game\Console;

use Illuminate\Console\Command;
use JaxWilko\Game\Classes\Engine\Engine;

class GameFlush extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'game:flush';

    /**
     * @var string The console command description.
     */
    protected $description = 'Game Flush Command';

    /**
     * Execute the console command.
     * @return int
     */
    public function handle(): int
    {
        (new Engine())
            ->boot()
            ->execFlush();
        return 0;
    }
}
