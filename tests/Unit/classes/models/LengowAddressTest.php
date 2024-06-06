<?php


namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use LengowAddress;


class LengowAddressTest extends TestCase
{
    /**
     *
     * @var LengowAddress
     */
    protected $address;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->address = new LengowAddress();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            LengowAddress::class,
            $this->address,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
