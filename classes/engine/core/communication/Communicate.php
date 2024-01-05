<?php

namespace JaxWilko\Game\Classes\Engine\Core\Communication;

use JaxWilko\Game\Classes\Engine\Core\Contracts\GameModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Contracts\Modules\WorldModuleInterface;
use JaxWilko\Game\Classes\Engine\Engine;
use JaxWilko\Game\Classes\Engine\Modules\Ai\AiModule;
use JaxWilko\Game\Classes\Engine\Modules\Player\PlayerModule;

/**
 * @method world(callable $message) mixed
 * @method ai(callable $message) mixed
 * @method player(callable $message) mixed
 *
 * @property WorldModuleInterface $world
 * @property AiModule $ai
 * @property PlayerModule $player
 */
class Communicate
{
    /**
     * @var Engine the engine to pass module calls to
     */
    protected Engine $engine;

    /**
     * @param Engine $engine
     */
    public function __construct(Engine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * This magic method allows calling to any engine module by name, i.e. $com->world(function () {})
     *
     * @param string $name
     * @param mixed $args
     * @return mixed
     */
    public function __call(string $name, mixed $args): mixed
    {
        if (!$module = $this->engine->getModule($name)) {
            return null;
        }

        if (!is_callable($args[0])) {
            throw new \InvalidArgumentException('callback is not callable');
        }

        $message = new Message($name, $module);
        $result = $message->send($args[0]);
        unset($message);

        return $result;
    }

    /**
     * This allows modules to fetch another modules instance
     *
     * @param string $name
     * @return GameModuleInterface|null
     */
    public function __get(string $name): ?GameModuleInterface
    {
        if (!$module = $this->engine->getModule($name)) {
            return null;
        }

        return $module;
    }
}
