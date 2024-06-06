<?php


namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use LengowOrderDetail;

class LengowOrderDetailTest extends TestCase
{
    /**
     *
     * @var LengowOrderDetail
     */
    protected $orderDetail;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
       $this->orderDetail = new LengowOrderDetail();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            LengowOrderDetail::class,
            $this->orderDetail,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
