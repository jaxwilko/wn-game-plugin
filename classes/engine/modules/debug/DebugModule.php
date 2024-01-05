<?php

namespace JaxWilko\Game\Classes\Engine\Modules\Debug;

use JaxWilko\Game\Classes\Engine\Core\Contracts\GameModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Events\Events;
use JaxWilko\Game\Classes\Engine\Core\Modules\GameModule;
use JaxWilko\Game\Classes\Engine\Core\Utils\Console;
use JaxWilko\Game\Classes\Engine\Engine;
use Winter\Storm\Support\Facades\Config;

/**
 * @class DebugModule
 * This module monitors the engine's memory usage and displays info about engine events
 */
class DebugModule extends GameModule implements GameModuleInterface
{
    /**
     * @const defines the AiModule cache key
     */
    public const CACHE_KEY = Engine::CACHE_KEY . '.debug';

    /**
     * @const defines the megabyte size
     */
    public const MB_SIZE = 1_048_576;

    /**
     * @var bool setting to enable displaying memory usage in the console
     */
    protected bool $printMemoryUsage;

    /**
     * @var bool setting to enable displaying event stats in the console
     */
    protected bool $printEventStats;

    /**
     * @var bool setting to enable displaying enhanced event stats in the console
     */
    protected bool $printDeepStats;

    /**
     * @var Events the engine event dispatcher
     */
    protected Events $events;

    /**
     * Registers the module, adding tick to the engine tick event and loading settings from config
     *
     * @param Events $events
     * @return void
     */
    public function register(Events $events): void
    {
        $this->events = $events;
        $this->events->listen('tick', [$this, 'tick']);

        $this->printMemoryUsage = Config::get('jaxwilko.game::debug.printMemoryUsage', false);
        $this->printEventStats = Config::get('jaxwilko.game::debug.printEventStats', false);
        $this->printDeepStats = Config::get('jaxwilko.game::debug.printDeepStats', false);
    }

    /**
     * On tick event called by the engine. Checks memory and event stats and displays them if enabled
     *
     * @return void
     */
    public function tick(): void
    {
        if ($this->printMemoryUsage) {
            $this->trackMemoryUsage();
        }

        if ($this->printEventStats) {
            $this->printEventStats();
        }

        Console::clearStatistics();
    }

    /**
     * Prints event stats to the console if the engine is running under tick rate
     *
     * @return $this
     */
    protected function printEventStats(): static
    {
        $total = array_sum($this->events->getStats());

        if ($total > $this->engine::TICK_RATE) {
            Console::dump($this->events->getStats() + [
                'TOTAL' => $total,
                'TICK_DELAY' => $total * $this->engine::TARGET_TICKS - 1
            ]);

            if ($this->printDeepStats && ($stats = Console::getStatistics())) {
                Console::dump($stats);
            }
        }

        $this->events->flushStats();

        return $this;
    }

    /**
     * Tracks the current memory usage and displays it if the current value is +/- 10000 different from last check
     *
     * @return $this
     */
    protected function trackMemoryUsage(): static
    {
        $used = memory_get_usage();

        $this->state['memory'] = $this->state['memory'] ?? $used;

        if (
            $this->state['memory'] + 10000 <= $used
            || $this->state['memory'] - 10000 >= $used
        ) {
            $limit = $this->getMemoryLimit();
            Console::put(
                '%d (%s%%)',
                $this->state['memory'] = $used,
                round($used / $limit * 100, 2)
            );
        }

        return $this;
    }

    /**
     * Returns the current memory limit defined by the host system
     *
     * @return int
     */
    public function getMemoryLimit(): int
    {
        if (isset($this->state['limit'])) {
            return $this->state['limit'];
        }

        $limit = ini_get('memory_limit');

        if (!str_ends_with($limit, 'M')) {
            throw new \RuntimeException('No energy to convert anything not M');
        }

        $limit = preg_replace('/[A-Z]/', '', $limit);

        return $this->state['limit'] = $limit * static::MB_SIZE;
    }
}
