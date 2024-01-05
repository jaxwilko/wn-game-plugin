<?php

namespace JaxWilko\Game\Classes\Engine\Core\Objects\Traits;

/**
 * @trait HasAnimations
 * Provides animation support to objects
 */
trait HasAnimations
{
    /**
     * @var array defines the sprite maps available for this object
     */
    protected array $spriteMap = [];

    /**
     * @var string the current animation state
     */
    protected string $animation = 'idle';

    /**
     * @var bool should the animation have random delays
     */
    protected bool $animationRandomDelay = false;

    /**
     * Set the current animation state
     *
     * @param string $animation
     * @return $this
     */
    public function animate(string $animation): static
    {
        $this->animation = $animation;
        return $this;
    }
}
