<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowOrderTest extends TestCase
{
    /**
     * @var \LengowOrder
     */
    protected $order;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->order = new \LengowOrder();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowOrder::class,
            $this->order,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
