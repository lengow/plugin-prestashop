<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowOrderDetailTest extends TestCase
{
    /**
     * @var \LengowOrderDetail
     */
    protected $orderDetail;

    /**
     *
     * @var string $testName
     */
    protected $testName;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->orderDetail = new \LengowOrderDetail();
        $this->testName = '[Test '. \LengowOrderDetail::class.'] ';
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowOrderDetail::class,
            $this->orderDetail,
            '[Test Class Instantiation] Check class instantiation'
        );
    }

    /**
     * @covers \LengowOrderDetail::findByOrderIdProductId
     */
    public function testFindByOrderIdProductId()
    {

        $rowMock = [
            'id_order_detail' => 1
        ];
        $dbMock = $this->getMockBuilder(\Db::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $dbMock->method('getRow')
               ->willReturn($rowMock);
        $dbMock->method('getValue')->willReturn(1);
        \Db::setInstanceForTesting($dbMock);
        $result1 = \LengowOrderDetail::findByOrderIdProductId(1, 1);
        $this->assertEquals(
            $rowMock['id_order_detail'],
            $result1,
            $this->testName.__METHOD__.' id_order_detail'
        );
        \Db::deleteTestingInstance();
    }
}
