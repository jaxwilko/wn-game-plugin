<?php

namespace JaxWilko\Game\Classes\Engine\Core\Objects;

use JaxWilko\Game\Classes\Engine\Core\Contracts\ToArrayInterface;
use JaxWilko\Game\Classes\Engine\Core\Objects\Entities\AiActor;
use JaxWilko\Game\Classes\Engine\Core\Objects\Entities\Entity;
use JaxWilko\Game\Classes\Engine\Core\Objects\Traits\HasAnimations;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

/**
 * @class StaticWorldObject
 *
 * This class is used by static world objects
 */
class StaticWorldObject extends WorldObject implements ToArrayInterface
{
    public const DEFAULT_OBJECT_COLOUR = '#f50ae5';

    use HasAnimations;

    /**
     * @var string the colour of the object, used if no sprite is provided
     */
    protected string $colour;

    /**
     * Create a new instance, `colour` can be passed as a settings value
     *
     * @param Vector $vector
     * @param Vector $size
     * @param array $settings
     */
    public function __construct(
        protected Vector $vector,
        protected Vector $size,
        array $settings = []
    ) {
        if (isset($settings['colour'])) {
            $this->colour = $settings['colour'];
        }

        parent::__construct($this->vector, $this->size);
    }

    /**
     * Returns the actors inside this object
     *
     * @param Level $level
     * @return array<Entity>
     */
    public function getActorsInside(Level $level): array
    {
        return $level->search($this, [Level::LAYER_ACTORS], asObjects: true, flatten: true);
    }

    /**
     * Returns the players inside this object
     *
     * @param Level $level
     * @return array<Entity>
     */
    public function getPlayersInside(Level $level): array
    {
        return array_filter($this->getActorsInside($level), fn ($object) => !$object instanceof AiActor);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'colour' => $this->colour ?? static::DEFAULT_OBJECT_COLOUR,
            'vector' => $this->vector->toArray(),
            'size' => $this->size->toArray(),
            'spriteMap' => $this->spriteMap,
            'animation' => $this->animation,
            'animationRandomDelay' => $this->animationRandomDelay,
        ];
    }
}
