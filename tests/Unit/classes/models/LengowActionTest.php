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
        $this->action = new \LengowAction();
        $this->testName = '[Test '.\LengowAction::class.'] ';
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowAction::class,
            $this->action,
            $this->testName.__METHOD__.' Check class instantiation'
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
            $this->testName.__METHOD__.' load id'
        );
        $this->assertEquals(
            $rowMock[\LengowAction::FIELD_ACTION_ID],
            $this->action->actionId,
            $this->testName.__METHOD__.' load action_id'
        );
        $this->assertEquals(
            $rowMock[\LengowAction::FIELD_ORDER_ID],
            $this->action->idOrder,
            $this->testName.__METHOD__.' load order_id'
        );
        $this->assertEquals(
            $rowMock[\LengowAction::FIELD_RETRY],
            $this->action->retry,
            $this->testName.__METHOD__.' load retry'
        );
        $this->assertEquals(
            $rowMock[\LengowAction::FIELD_CREATED_AT],
            $this->action->createdAt,
            $this->testName.__METHOD__.' load created_at'
        );
        $this->assertEquals(
            $rowMock[\LengowAction::FIELD_UPDATED_AT],
            $this->action->updatedAt,
            $this->testName.__METHOD__.' load updated_at'
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
            $this->testName.__METHOD__.' false'
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
        $this->assertTrue($result, $this->testName.__METHOD__.' findByActionId 123');
        $this->assertEquals(
            $rowMock[\LengowAction::FIELD_ACTION_ID],
            $this->action->actionId,
            $this->testName.__METHOD__.' findByActionId action_id'
        );
        $this->assertEquals(
            $rowMock[\LengowAction::FIELD_ID],
            $this->action->id,
            $this->testName.__METHOD__.' findByActionId id'
        );
        \Db::deleteTestingInstance();
    }

    /**
     * @covers \LengowAction::getActionByActionId
     */
    public function testGetActionByActionId()
    {
        $resultFalse = \LengowAction::getActionByActionId(-1);
        $this->assertFalse($resultFalse, $this->testName.__METHOD__.' false');
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
        $result = \LengowAction::getActionByActionId($rowMock[\LengowAction::FIELD_ACTION_ID]);

        $this->assertEquals(
            $rowMock[\LengowAction::FIELD_ID],
            $result,
            $this->testName.__METHOD__.' action_id'
        );
        \Db::deleteTestingInstance();
    }

    /**
     * @covers \LengowAction::getActionsByOrderId
     */
    public function testGetActionsByOrderId()
    {
        $resultFalse = \LengowAction::getActionsByOrderId(-1);
        $this->assertFalse($resultFalse, $this->testName.__METHOD__.' false');
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
        $actionMock = clone $this->action;
        $actionMock->load($rowMock);
        $actionsWaited = [$actionMock];

        $dbMock = $this->getMockBuilder(\Db::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $dbMock->method('executeS')->willReturn([$rowMock]);
        \Db::setInstanceForTesting($dbMock);
        $result = \LengowAction::getActionsByOrderId($rowMock[\LengowAction::FIELD_ORDER_ID]);
        $this->assertEquals(
            $actionsWaited,
            $result,
            $this->testName.__METHOD__.' actions array found'
        );
        \Db::deleteTestingInstance();
    }

    /**
     * @covers \LengowAction::getActiveActions
     */
    public function testGetActiveActions()
    {

        $dbMock = $this->getMockBuilder(\Db::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $dbMock->method('executeS')->willReturn([]);
        \Db::setInstanceForTesting($dbMock);
        $resultFalse = \LengowAction::getActiveActions();
        $this->assertFalse($resultFalse, $this->testName.__METHOD__.' false');

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
        $dbMock2 = $this->getMockBuilder(\Db::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $dbMock2->method('executeS')->willReturn([$rowMock]);
        \Db::setInstanceForTesting($dbMock2);

        $resultArray = \LengowAction::getActiveActions(false);

        $this->assertEquals(
            [$rowMock],
            $resultArray,
            $this->testName.__METHOD__.' active actions array'
        );
        $actionMock = clone $this->action;
        $actionMock->load($rowMock);
        $actionsWaited = [$actionMock];
        $resultLoad = \LengowAction::getActiveActions(true);
        $this->assertEquals(
            $actionsWaited,
            $resultLoad,
            $this->testName.__METHOD__.' active actions load'
        );
        \Db::deleteTestingInstance();
    }

    /**
     * @covers \LengowAction::getLastOrderActionType
     */
    public function testGetLastOrderActionType()
    {
        $resultFalse = \LengowAction::getLastOrderActionType(-1);
        $this->assertFalse($resultFalse, $this->testName.__METHOD__.' false');
        $rowMock = [
            \LengowAction::FIELD_ID => 123,
            \LengowAction::FIELD_ORDER_ID => 1,
            \LengowAction::FIELD_ACTION_ID => 456,
            \LengowAction::FIELD_ACTION_TYPE => 'cancel',
            \LengowAction::FIELD_RETRY => 0,
            \LengowAction::FIELD_PARAMETERS => [],
            \LengowAction::FIELD_STATE => 1,
            \LengowAction::FIELD_CREATED_AT => '1970-01-01 00:00:00',
            \LengowAction::FIELD_UPDATED_AT => '1970-01-01 00:00:00'
        ];
        $dbMock = $this->getMockBuilder(\Db::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $dbMock->method('executeS')->willReturn([$rowMock]);
        \Db::setInstanceForTesting($dbMock);
        $resultLast = \LengowAction::getLastOrderActionType($rowMock[\LengowAction::FIELD_ORDER_ID]);
        $this->assertEquals(
            $rowMock[\LengowAction::FIELD_ACTION_TYPE],
            $resultLast,
            $this->testName.__METHOD__.' last action'
        );
        \Db::deleteTestingInstance();
    }
}
