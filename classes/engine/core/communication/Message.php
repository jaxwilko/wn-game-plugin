<?php

namespace JaxWilko\Game\Classes\Engine\Core\Communication;

use JaxWilko\Game\Classes\Engine\Core\Contracts\GameModuleInterface;

class Message
{
    /**
     * @var string name of the module being called
     */
    protected string $name;

    /**
     * @var GameModuleInterface the module to send the message to
     */
    protected GameModuleInterface $module;

    /**
     * This creates a new message object containing the module
     *
     * @param string $name
     * @param GameModuleInterface $module
     */
    public function __construct(string $name, GameModuleInterface $module)
    {
        $this->name = $name;
        $this->module = $module;
    }

    /**
     * Executes the callable message while passing the module requested
     *
     * @param callable $callback
     * @return mixed
     */
    public function send(callable $callback): mixed
    {
        return $callback($this->module);
    }
}
