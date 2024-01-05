<?php

namespace JaxWilko\Game\Classes\Engine\Core\Objects\Generic;

use JaxWilko\Game\Classes\Engine\Core\Objects\StaticSpriteObject;
use JaxWilko\Game\Classes\Engine\Core\Objects\Traits\PhpScript;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;

class GenericStaticSpriteObject extends StaticSpriteObject
{
    use PhpScript;

    public function __construct(Vector $vector, Vector $size, array $settings = ['colour' => '#964B00'])
    {
        if (isset($settings['script'])) {
            $this->script = $settings['script'];
        }

        if (isset($settings['spriteMap'])) {
            $this->spriteMap = $settings['spriteMap'];
        }

        if (isset($settings['colour'])) {
            $this->colour = $settings['colour'];
        }

        if (isset($settings['animationRandomDelay'])) {
            $this->animationRandomDelay = $settings['animationRandomDelay'];
        }

        parent::__construct($vector, $size, $settings);
    }
}
