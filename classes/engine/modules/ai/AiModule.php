<?php

namespace JaxWilko\Game\Classes\Engine\Modules\Ai;

use JaxWilko\Game\Classes\Engine\Core\Contracts\Modules\AiModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Contracts\Modules\WorldModuleInterface;
use JaxWilko\Game\Classes\Engine\Core\Events\Events;
use JaxWilko\Game\Classes\Engine\Core\Modules\GameModule;
use JaxWilko\Game\Classes\Engine\Core\Objects\Entities\AiActor;
use JaxWilko\Game\Classes\Engine\Core\Objects\WorldObject;
use JaxWilko\Game\Classes\Engine\Engine;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;
use Weird\Processes\Thread;
use Weird\Promise;

/**
 * @class AiModule
 * This module handles all the AI processing logic, via worker processes
 *
 * @property array<string<string>> $actorCommands
 */
class AiModule extends GameModule implements AiModuleInterface
{
    /**
     * @const defines the AiModule cache key
     */
    public const CACHE_KEY = Engine::CACHE_KEY . '.ai';

    /**
     * @var array allows for access to state keys via magic __get
     */
    protected array $props = [
        'actorCommands'
    ];

    /**
     * Registers the module, adding tick to the engine tick event
     *
     * @param Events $events
     * @return void
     */
    public function register(Events $events): void
    {
        $events->listen('tick', [$this, 'tick']);
    }

    /**
     * On tick event called by the engine. Grabs all AiActors from all layers, handles removing them if they are dead
     * and then dispatches update logic across worker threads via the engine process manager.
     *
     * @return void
     */
    public function tick(): void
    {
        $levelActors = $this->com->world(function (WorldModuleInterface $world) {
            return array_map(
                fn (Level $level) => $level->getLayer($level::LAYER_ACTORS),
                $world->getLevels()
            );
        });

        foreach ($levelActors as $level => $actors) {
            // get the actor level
            $level = $this->com->world(fn (WorldModuleInterface $world) => $world->getLevel($level));

            // randomise the order of actors to so that no 1 actor is prioritised above any other
            $actors = array_shuffle($actors);

            foreach ($actors as $actor) {
                // Load the world object of the actor
                $actor = WorldObject::load($actor);

                // If this actor is not AI, skip
                if (!$actor instanceof AiActor) {
                    continue;
                }

                /**
                 * Bind the level id to the actor so level data can be loaded in worker threads without access to
                 * the world module.
                 */
                if ($actor->getLevelId() !== $level->id) {
                    $actor->setLevelId($level->id)
                        ->save();
                }

                // If the actor is dead, drop actor inventory and then remove it from the world actor map and delete
                if (!$actor->alive()) {
                    $actor->dropInventory($level);
                    $this->com->world(fn (WorldModuleInterface $world) => $world->removeActor($actor));
                    $actor->delete();
                    continue;
                }

                // Set the id to a local scope var to allow it to be bound to the serialized callable used by weird
                $actorId = $actor->id;

                // If there is not already a running entityCommand, then dispatch
                if (!isset($this->actorCommands[$actorId])) {
                    $this->actorCommands[$actorId] = true;
                    $this->engine->processManager->dispatch(
                        Promise::make(function (Thread $thread) use ($actorId) {
                            $actor = AiActor::load($actorId);
                            if (!$actor) {
                                // Entity has been deleted between dispatch and call, just exit
                                return;
                            }
                            $actor->update();
                        })->then(function (mixed $results) use ($actorId) {
                            if ($results && is_array($results)) {
                                foreach ($results as $result) {
                                    $result->get() && dump($result->get());
                                }
                            }
                            unset($this->actorCommands[$actorId]);
                        })
                    );
                }
            }
        }
    }
}
