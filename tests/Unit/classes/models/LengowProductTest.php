<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowProductTest extends TestCase
{
    /**
     * @var \LengowProduct
     */
    protected $product;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->product = new \LengowProduct();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowProduct::class,
            $this->product,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
