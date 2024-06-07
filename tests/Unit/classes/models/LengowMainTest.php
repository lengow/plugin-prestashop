<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowMainTest extends TestCase
{
    /**
     * @var \LengowMain
     */
    protected $main;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->main = new \LengowMain();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowMain::class,
            $this->main,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
