<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowToolboxTest extends TestCase
{
    /**
     * @var \LengowToolbox
     */
    protected $toolbox;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->toolbox = new \LengowToolbox();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowToolbox::class,
            $this->toolbox,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
