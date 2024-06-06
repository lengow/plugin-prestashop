<?php


namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use LengowCustomer;


class LengowCustomerTest extends TestCase
{
    /**
     *
     * @var LengowCustomer
     */
    protected $customer;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->customer = new LengowCustomer();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            LengowCustomer::class,
            $this->customer,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
