<?php

namespace JaxWilko\Game\Tests\Classes\Core\Objects;

use JaxWilko\Game\Classes\Engine\Core\Objects\WorldObject;
use JaxWilko\Game\Classes\Engine\Core\Utils\Vector;
use System\Tests\Bootstrap\PluginTestCase;

class VectorTest extends PluginTestCase
{

    public function testClone(): void
    {
        $vector = new Vector(10, 10);
        $vector2 = $vector->clone();

        $this->assertEquals($vector->toString(), $vector2->toString());
        $this->assertNotEquals(spl_object_id($vector), spl_object_id($vector2));
    }

    public function testSet(): void
    {
        $vector = new Vector(10, 10);
        $vector->set(9, 9);

        $this->assertEquals('9,9', $vector->toString());
    }

    public function testGet(): void
    {
        $vector = new Vector(12, 10);
        $pos = $vector->get();

        $this->assertEquals(12, $pos[0]);
        $this->assertEquals(10, $pos[1]);
    }

    public function testX(): void
    {
        $vector = new Vector(12, 10);
        $this->assertEquals(12, $vector->x());

        $vector->x(13);

        $this->assertEquals(13, $vector->x());
    }

    public function testY(): void
    {
        $vector = new Vector(12, 10);
        $this->assertEquals(10, $vector->y());

        $vector->y(13);

        $this->assertEquals(13, $vector->y());
    }

    public function testTapX(): void
    {
        $vector = new Vector(12, 10);
        $this->assertEquals(15, $vector->tapX(3));
        $this->assertEquals(12, $vector->tapX(-3));

        $vector->x(5);
        $this->assertEquals(0, $vector->tapX(-10, 0));

        $vector->x(5);
        $this->assertEquals(10, $vector->tapX(8, 10));
    }

    public function testTapY(): void
    {
        $vector = new Vector(12, 10);
        $this->assertEquals(13, $vector->tapY(3));
        $this->assertEquals(10, $vector->tapY(-3));

        $vector->y(5);
        $this->assertEquals(0, $vector->tapY(-10, 0));

        $vector->y(5);
        $this->assertEquals(10, $vector->tapY(8, 10));
    }

    public function testDry(): void
    {
        $vector = new Vector(12, 10);
        $vector2 = $vector->dry('x', 10);

        $this->assertNotEquals('22,10', $vector->toString());
        $this->assertEquals('22,10', $vector2->toString());

        $vector = new Vector(12, 10);
        $vector2 = $vector->dry('x', -10);

        $this->assertNotEquals('2,10', $vector->toString());
        $this->assertEquals('2,10', $vector2->toString());
    }

    public function testToString(): void
    {
        $vector = new Vector(12, 10);

        $this->assertEquals('12,10', $vector->toString());

        $vector->y(9);
        $this->assertEquals('12,9', $vector->toString());

        $vector->set(9, 8);
        $this->assertEquals('9,8', $vector->toString());
    }

    public function testFromString(): void
    {
        $vector = Vector::fromString('7,8');

        $this->assertEquals('7,8', $vector->toString());

        $vector = Vector::fromString('8,7');

        $this->assertEquals('8,7', $vector->toString());

        $vector = Vector::fromString(' 8 , 7 ');

        $this->assertEquals('8,7', $vector->toString());
    }

    public function testToArray(): void
    {
        $vector = new Vector(12, 10);
        $pos = $vector->toArray();

        $this->assertArrayHasKey('x', $pos);
        $this->assertEquals(12, $pos['x']);

        $this->assertArrayHasKey('y', $pos);
        $this->assertEquals(10, $pos['y']);
    }
}
