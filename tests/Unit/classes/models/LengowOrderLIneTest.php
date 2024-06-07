<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowOrderLineTest extends TestCase
{
    /**
     * @var \LengowOrderLine
     */
    protected $orderLine;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->orderLine = new \LengowOrderLine();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowOrderLine::class,
            $this->orderLine,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
