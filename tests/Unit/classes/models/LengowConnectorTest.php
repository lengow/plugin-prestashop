<?php

namespace Lengow\Connector\Test\Unit\Model;

use Lengow\Connector\Test\Unit\Fixture;
use PHPUnit\Framework\TestCase;
use LengowConnector;

class LengowConnectorTest extends TestCase
{
    /**
     *
     * @var LengowConnector
     */
    protected $connector;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->connector = new LengowConnector('12345678','123456789');
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            LengowConnector::class,
            $this->connector,
            '[Test Class Instantiation] Check class instantiation'
        );
    }

    /**
     * @covers LengowConnector::format
     */
    public function testFormat()
    {
        $fixture = new Fixture();
        $this->assertEquals(
            ['id' => 1, 'name' => 'A green door', 'price' => '12.5', 'tags' => ['home', 'green']],
            $fixture->invokeMethod(
                $this->connector,
                'format',
                ['{"id": 1,"name": "A green door","price": 12.50,"tags": ["home", "green"]}', 'json']
            ),
            '[Test Format] Check json format'
        );

        $this->assertEquals(
            'simple,plop,/1233;variable',
            $fixture->invokeMethod($this->connector, "format", ['simple,plop,/1233;variable', 'stream']),
            '[Test Format] Check no specific format format'
        );
    }
}