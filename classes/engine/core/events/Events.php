<?php

namespace JaxWilko\Game\Classes\Engine\Core\Events;

use JaxWilko\Game\Classes\Engine\Core\Utils\Console;

class Events
{
    /**
     * @var array<callable> list of events and their callables
     */
    protected array $events = [];

    /**
     * @var array<callable> list of events that return values and their callables
     */
    protected array $retrievableEvents = [];

    /**
     * @var array event stats
     */
    protected array $stats = [];

    /**
     * Register a callable for an event
     *
     * @param string $name
     * @param callable $callback
     * @return void
     */
    public function listen(string $name, callable $callback): void
    {
        $this->events[$name][] = $callback;
    }

    /**
     * Register a callable for a retrievable event
     *
     * @param string $name
     * @param callable $callback
     * @return void
     */
    public function listenRetrievable(string $name, callable $callback): void
    {
        $this->retrievableEvents[$name] = $callback;
    }

    /**
     * Trigger all callables registered for an event, returns false if no events were triggered
     *
     * @param string $name
     * @param mixed ...$args
     * @return bool
     */
    public function fire(string $name, mixed ...$args): bool
    {
        if (!isset($this->events[$name])) {
            return false;
        }

        foreach ($this->events[$name] as $event) {
            $start = microtime(true);
            $event(...$args);
            $this->stats[$name . ': ' . Console::getCallableName($event)] = microtime(true) - $start;
        }

        return true;
    }

    /**
     * Trigger an event callable and return its value
     *
     * @param string $name
     * @param mixed ...$args
     * @return mixed
     */
    public function fireRetrievable(string $name, mixed ...$args): mixed
    {
        if (!($callable = $this->retrievableEvents[$name] ?? null)) {
            return null;
        }

        $start = microtime(true);
        $result = $callable(...$args);
        $this->stats[$name . ': ' . Console::getCallableName($callable)] = microtime(true) - $start;

        return $result;
    }

    /**
     * Return event stats
     *
     * @return array
     */
    public function getStats(): array
    {
        return $this->stats;
    }

    /**
     * Clear event stats
     *
     * @return void
     */
    public function flushStats(): void
    {
        $this->stats = [];
    }
}
