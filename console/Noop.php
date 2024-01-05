<?php

namespace JaxWilko\Game\Console;

use Illuminate\Console\Command;

class Noop extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'game:noop';

    /**
     * @var string The console command description.
     */
    protected $description = 'Game Nop';

    /**
     * Execute the console command.
     * @return int
     */
    public function handle(): int
    {
        return 0;
    }
}
