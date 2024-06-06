<?php


namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use LengowConfiguration;


class LengowConfigurationTest extends TestCase
{
    /**
     *
     * @var LengowConfiguration
     */
    protected $config;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->config = new LengowConfiguration();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            LengowConfiguration::class,
            $this->config,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
