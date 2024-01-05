<?php

namespace JaxWilko\Game\Tests;

use JaxWilko\Game\Plugin;
use System\Tests\Bootstrap\PluginTestCase;

class PluginTest extends PluginTestCase
{
    public function setUp(): void
    {
        $this->plugin = new Plugin($this->createApplication());
    }

    public function testPluginDetails()
    {
        $details = $this->plugin->pluginDetails();

        $this->assertIsArray($details);
        $this->assertArrayHasKey('name', $details);
        $this->assertArrayHasKey('description', $details);
        $this->assertArrayHasKey('icon', $details);
        $this->assertArrayHasKey('author', $details);

        $this->assertEquals('Jack Wilkinson', $details['author']);
    }
}
