<?php

namespace JaxWilko\Game\Classes\Engine\Core\Objects\Traits;

use JaxWilko\Game\Classes\Engine\Core\Contracts\Modules\WorldModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Utils\Script;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

/**
 * @trait PhpScript
 * Adds a `tick` method that can be called to execute a string `$script` property
 */
trait PhpScript
{
    /**
     * @var array static cache of scripts shared between implementing objects
     */
    private static array $callableCache = [];

    /**
     * @var string|null script to execute when the `tick()` method is called
     */
    protected ?string $script = null;

    /**
     * @var array a local property to be used for data storage by the script being executed
     */
    protected array $data = [];

    /**
     * Compiles and executes the script defined in the `$script` property.
     *
     * @param Level $level
     * @param WorldModuleInterface|null $world
     * @return void
     */
    public function tick(Level $level, ?WorldModuleInterface $world = null): void
    {
        if (!$this->script) {
            return;
        }

        /**
         * The following is a performance optimisation, requiring the code directly each `tick` is slow as php may or
         * may not hit the disk, instead of requiring from the file, we create a static cache that will be shared
         * between all object implementing `PhpScript`, then load a callable function from file into the cache,
         * executing it rebounded to the current object.
         */
        if (!isset(static::$callableCache[$this->script])) {
            $callable = Script::compile($this->script, [
                \JaxWilko\Game\Classes\Engine\Modules\World\Level::class . ' $level',
                \JaxWilko\Game\Classes\Engine\Modules\World\WorldModule::class . ' $world'
            ]);

            static::$callableCache[$this->script] = $callable;
        }

        static::$callableCache[$this->script](...)->call($this, $level, $world);
    }
}
