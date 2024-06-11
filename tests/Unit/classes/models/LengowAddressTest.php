<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowAddressTest extends TestCase
{
    /**
     * @var \LengowAddress
     */
    protected $address;

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
        $this->address = new \LengowAddress();
        $this->testName = '[Test '.\LengowAddress::class.'] ';
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowAddress::class,
            $this->address,
            '[Test Class Instantiation] Check class instantiation'
        );
    }

    /**
     * @covers \LengowAddress::getByAlias
     */
    public function testGetByAlias()
    {
        $rowMock = [
            'alias' => 'test',
            'id_address' => 1,
            'id_country' => 1,
            'id_cusotmer' => 1,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'address1' => '10 RUE DES LILAS',
            'postcode' => '75002',
            'city' => 'PARIS'

        ];

        $dbMock = $this->getMockBuilder(\Db::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $dbMock->method('getRow')->willReturn($rowMock);
        \Db::setInstanceForTesting($dbMock);
        $result = \LengowAddress::getByAlias('test');
        $this->assertIsObject($result, $this->testName.__METHOD__.' object');
        $this->assertEquals(
            $rowMock['alias'],
            $result->alias,
            $this->testName.__METHOD__.' alias'
        );
        $this->assertEquals(
            $rowMock['address1'],
            $result->address1,
            $this->testName.__METHOD__.' address1'
        );
        $this->assertEquals(
            $rowMock['firstname'],
            $result->firstname,
            $this->testName.__METHOD__.' firstname'
        );
        $dbMock2 = $this->getMockBuilder(\Db::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $dbMock2->method('getRow')->willReturn([]);
        \Db::setInstanceForTesting($dbMock2);
        $resultFalse =  \LengowAddress::getByAlias('test');
        $this->assertFalse($resultFalse, $this->testName.__METHOD__.' false');
        \Db::deleteTestingInstance();
    }

    /**
     * @covers \LengowAddress::getByHash
     */
    public function testGetByHash()
    {
        $rowMock = [
            'alias' => '098f6bcd4621d373cade4e832627b4f6',
            'id_address' => 1,
            'id_country' => 1,
            'id_cusotmer' => 1,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'address1' => '10 RUE DES LILAS',
            'postcode' => '75002',
            'city' => 'PARIS'

        ];

        $dbMock = $this->getMockBuilder(\Db::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $dbMock->method('getRow')->willReturn($rowMock);
        \Db::setInstanceForTesting($dbMock);
        $resultHash = \LengowAddress::getByHash('test');
        $this->assertIsObject($resultHash, $this->testName.__METHOD__.' object');
        $this->assertEquals(
            $rowMock['address1'],
            $resultHash->address1,
            $this->testName.__METHOD__.' address1'
        );
        $this->assertEquals(
            $rowMock['firstname'],
            $resultHash->firstname,
            $this->testName.__METHOD__.' firstname'
        );
        $dbMock2 = $this->getMockBuilder(\Db::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $dbMock2->method('getRow')->willReturn([]);
        \Db::setInstanceForTesting($dbMock2);
        $resultFalse =  \LengowAddress::getByHash('test');
        $this->assertFalse($resultFalse, $this->testName.__METHOD__.' false');
        \Db::deleteTestingInstance();
    }
}
