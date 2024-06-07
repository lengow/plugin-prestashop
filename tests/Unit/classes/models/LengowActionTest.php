<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowActionTest extends TestCase
{
    /**
     * @var \LengowAction
     */
    protected $action;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->action = new \LengowAction();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowAction::class,
            $this->action,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
    /**
     * @covers \LengowAction::load
     */
    public function testLoad()
    {
        $rowMock = [
            \LengowAction::FIELD_ID => 1,
            \LengowAction::FIELD_ORDER_ID => 1,
            \LengowAction::FIELD_ACTION_ID => 1,
            \LengowAction::FIELD_ACTION_TYPE => 'ship',
            \LengowAction::FIELD_RETRY => 0,
            \LengowAction::FIELD_PARAMETERS => [],
            \LengowAction::FIELD_STATE => 1,
            \LengowAction::FIELD_CREATED_AT => '1970-01-01 00:00:00',
            \LengowAction::FIELD_UPDATED_AT => '1970-01-01 00:00:00'
        ];
        $this->action->load($rowMock);
        $this->assertEquals(
            $rowMock[\LengowAction::FIELD_ID],
            $this->action->id,
            '[Test LengowAction] load id'
        );
        $this->assertEquals(
            $rowMock[\LengowAction::FIELD_ACTION_ID],
            $this->action->actionId,
            '[Test LengowAction] load action_id'
        );
        $this->assertEquals(
            $rowMock[\LengowAction::FIELD_ORDER_ID],
            $this->action->idOrder,
            '[Test LengowAction] load order_id'
        );
        $this->assertEquals(
            $rowMock[\LengowAction::FIELD_RETRY],
            $this->action->retry,
            '[Test LengowAction] load retry'
        );
        $this->assertEquals(
            $rowMock[\LengowAction::FIELD_CREATED_AT],
            $this->action->createdAt,
            '[Test LengowAction] load created_at'
        );
        $this->assertEquals(
            $rowMock[\LengowAction::FIELD_UPDATED_AT],
            $this->action->updatedAt,
            '[Test LengowAction] load updated_at'
        );
    }

    /**
     * @covers  \LengowAction::findByActionId
     */
    public function testFindByActionId()
    {
       $id = -1;

       $this->assertFalse(
            $this->action->findByActionId($id),
            '[Test LengowAction] findByActionId -1'
        );

        $rowMock = [
            \LengowAction::FIELD_ID => 123,
            \LengowAction::FIELD_ORDER_ID => 1,
            \LengowAction::FIELD_ACTION_ID => 456,
            \LengowAction::FIELD_ACTION_TYPE => 'ship',
            \LengowAction::FIELD_RETRY => 0,
            \LengowAction::FIELD_PARAMETERS => [],
            \LengowAction::FIELD_STATE => 1,
            \LengowAction::FIELD_CREATED_AT => '1970-01-01 00:00:00',
            \LengowAction::FIELD_UPDATED_AT => '1970-01-01 00:00:00'
        ];

        $dbMock = $this->getMockBuilder(\Db::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $dbMock->method('getRow')
               ->willReturn($rowMock);
        \Db::setInstanceForTesting($dbMock);
        $result = $this->action->findByActionId($rowMock[\LengowAction::FIELD_ID]);
        $this->assertTrue($result, '[Test LengowAction] findByActionId 123');
        $this->assertEquals(
            $rowMock[\LengowAction::FIELD_ACTION_ID],
            $this->action->actionId,
            '[Test LengowAction] findByActionId action_id'
        );
        $this->assertEquals(
            $rowMock[\LengowAction::FIELD_ID],
            $this->action->id,
            '[Test LengowAction] findByActionId id'
        );
        \Db::deleteTestingInstance();
    }
}
