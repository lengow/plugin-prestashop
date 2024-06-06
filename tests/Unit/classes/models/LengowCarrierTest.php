<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowCarrierTest extends TestCase
{
    /**
     * @var \LengowCarrier
     */
    protected $carrier;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->carrier = new \LengowCarrier(1, 1);
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowCarrier::class,
            $this->carrier,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
