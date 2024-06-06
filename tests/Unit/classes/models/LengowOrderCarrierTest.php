<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowOrderCarrierTest extends TestCase
{
    /**
     * @var \LengowOrderCarrier
     */
    protected $orderCarrier;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->orderCarrier = new \LengowOrderCarrier();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowOrderCarrier::class,
            $this->orderCarrier,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
