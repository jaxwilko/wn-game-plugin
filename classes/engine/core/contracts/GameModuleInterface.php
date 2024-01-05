<?php

namespace JaxWilko\Game\Classes\Engine\Core\Contracts;

use JaxWilko\Game\Classes\Engine\Core\Events\Events;
use JaxWilko\Game\Classes\Engine\Core\Communication\Communicate;
use JaxWilko\Game\Classes\Engine\Engine;

interface GameModuleInterface extends ToArrayInterface
{
    public function boot(Engine $engine, Communicate $com, Events $events, array $settings): void;

    public function register(Events $events): void;

    public function restore(array $data): void;

    public function store(): void;

    public function flush(): void;
}
