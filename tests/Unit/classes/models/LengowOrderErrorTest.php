<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowOrderErrorTest extends TestCase
{
    /**
     * @var \LengowOrderError
     */
    protected $orderError;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->orderError = new \LengowOrderError();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowOrderError::class,
            $this->orderError,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
