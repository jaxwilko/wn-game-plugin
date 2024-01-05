<?php

namespace JaxWilko\Game\Tests\Classes\Core\Objects;

use JaxWilko\Game\Classes\Engine\Core\Objects\WorldObject;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use System\Tests\Bootstrap\PluginTestCase;

class WorldObjectTest extends PluginTestCase
{
    public function testIntersects(): void
    {
        // * * *
        // * 1 *
        // * * *

        $world = new WorldObject(new Vector(0, 0), new Vector(3, 3));
        $object = new WorldObject(new Vector(1, 1), new Vector(1, 1));

        $this->assertTrue($world->intersects($object));

        // * * *
        // * 1 1 1
        // * * *

        $world = new WorldObject(new Vector(0, 0), new Vector(3, 3));
        $object = new WorldObject(new Vector(1, 1), new Vector(3, 3));

        $this->assertTrue($world->intersects($object));


        // * * *
        // * * * 1
        // * * *

        $world = new WorldObject(new Vector(0, 0), new Vector(3, 3));
        $object = new WorldObject(new Vector(1, 4), new Vector(1, 1));

        $this->assertFalse($world->intersects($object));
    }

    public function testContains(): void
    {
        // * * *
        // * 1 *
        // * * *

        $world = new WorldObject(new Vector(0, 0), new Vector(3, 3));
        $object = new WorldObject(new Vector(1, 1), new Vector(1, 1));

        $this->assertTrue($world->contains($object));

        // * * *
        // * 1 1 1
        // * * *

        $world = new WorldObject(new Vector(0, 0), new Vector(3, 3));
        $object = new WorldObject(new Vector(1, 1), new Vector(3, 3));

        $this->assertFalse($world->contains($object));


        // * * *
        // * * * 1
        // * * *

        $world = new WorldObject(new Vector(0, 0), new Vector(3, 3));
        $object = new WorldObject(new Vector(1, 4), new Vector(1, 1));

        $this->assertFalse($world->contains($object));

        // 1
        //   * * *
        //   * * *
        //   * * *

        $world = new WorldObject(new Vector(0, 0), new Vector(3, 3));
        $object = new WorldObject(new Vector(-1, -1), new Vector(1, 1));

        $this->assertFalse($world->contains($object));

        // 1
        //   1 * *
        //   * * *
        //   * * *

        $world = new WorldObject(new Vector(0, 0), new Vector(3, 3));
        $object = new WorldObject(new Vector(-1, -1), new Vector(2, 2));

        $this->assertFalse($world->contains($object));
    }
}
