<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowShopTest extends TestCase
{
    /**
     * @var \LengowShop
     */
    protected $shop;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->shop = new \LengowShop();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowShop::class,
            $this->shop,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
