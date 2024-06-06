<?php


namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use LengowCart;


class LengowCartTest extends TestCase
{
    /**
     *
     * @var LengowCart
     */
    protected $cart;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->cart = new LengowCart();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            LengowCart::class,
            $this->cart,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
