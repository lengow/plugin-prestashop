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

    /**
     * @covers \LengowAction::find
     */
    public function testFind()
    {
        $this->assertFalse(
            $this->action->find(-1),
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
        $dbMock->method('getRow')->willReturn($rowMock);
        \Db::setInstanceForTesting($dbMock);
        $result = $this->action->find($rowMock[\LengowAction::FIELD_ID]);
        $this->assertTrue($result, $this->testName.__METHOD__.' true');
        $this->assertEquals(
            $rowMock[\LengowAction::FIELD_ACTION_ID],
            $this->action->actionId,
            $this->testName.__METHOD__.' action_id'
        );
        \Db::deleteTestingInstance();
    }

    /**
     * @covers \LengowAction::canSendAction
     */
    public function testCanSendAction()
    {
        $mockParams = [
            \LengowAction::ARG_CARRIER => 'DHL',
            \LengowAction::ARG_CARRIER_NAME => 'DHL',
            \LengowAction::ARG_SHIPPING_METHOD => 'DHL',
            \LengowAction::ARG_ACTION_TYPE => 'ship'
        ];
        $lengowConnectorMock = $this->getMockBuilder(\LengowConnector::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $lengowConnectorMock->method('requestApi')
                            ->willReturn(json_decode('{"count":0}'));
        \LengowConnector::setInstanceForTesting($lengowConnectorMock);
        $lengowOrder = new \LengowOrder();
        $result = \LengowAction::canSendAction($mockParams, $lengowOrder);
        $this->assertTrue($result, $this->testName.__METHOD__.' true');
        $lengowConnectorMock2 = $this->getMockBuilder(\LengowConnector::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $lengowConnectorMock2->method('requestApi')
                            ->willReturn(json_decode('{"count":1, "results":[{"id":1}]}'));
        \LengowConnector::setInstanceForTesting($lengowConnectorMock2);
        $result2 = \LengowAction::canSendAction($mockParams, $lengowOrder);
        $this->assertFalse($result2, $this->testName.__METHOD__.' false');
        \LengowConnector::disableTestingInstance();
    }

    /**
     * @covers \LengowAction::createAction()
     */
    public function testCreateAction()
    {
        $mockParams = [
            \LengowAction::ARG_CARRIER => 'DHL',
            \LengowAction::ARG_CARRIER_NAME => 'DHL',
            \LengowAction::ARG_SHIPPING_METHOD => 'DHL',
            \LengowAction::ARG_ACTION_TYPE => 'ship'
        ];

        $mockToSend = [
            \LengowAction::FIELD_PARAMETERS => $mockParams,
            \LengowAction::FIELD_ORDER_ID => 1,
            \LengowAction::FIELD_ACTION_ID => 1,
            \LengowAction::FIELD_ACTION_TYPE => 'ship',
            'marketplace_sku' => 'amazon_fr-123456'
        ];
        $dbMock = $this->getMockBuilder(\Db::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $dbMock->method('insert')->willReturn(true);
        \Db::setInstanceForTesting($dbMock);
        $result = \LengowAction::createAction($mockToSend);
        $this->assertTrue($result, $this->testName.__METHOD__.' true');

        $dbMock2 = $this->getMockBuilder(\Db::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $dbMock2->method('insert')
            ->willThrowException(new \PrestaShopDatabaseException('plop'));
        \Db::setInstanceForTesting($dbMock2);
        $resultFalse = \LengowAction::createAction($mockToSend);
        $this->assertFalse($resultFalse, $this->testName.__METHOD__.' false');
        \Db::deleteTestingInstance();
    }

    /**
     * covers \LengowAction::updateAction
     */
    public function testUpdateAction()
    {
        $mockParams = [
            \LengowAction::ARG_CARRIER => 'DHL',
            \LengowAction::ARG_CARRIER_NAME => 'DHL',
            \LengowAction::ARG_SHIPPING_METHOD => 'DHL',
            \LengowAction::ARG_ACTION_TYPE => 'ship'
        ];
        $mockToSend = [
            \LengowAction::FIELD_PARAMETERS => $mockParams,
            \LengowAction::FIELD_ORDER_ID => 1,
            \LengowAction::FIELD_ACTION_ID => 1,
            \LengowAction::FIELD_ACTION_TYPE => 'ship',
            'marketplace_sku' => 'amazon_fr-123456'
        ];
        $dbMock = $this->getMockBuilder(\Db::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $dbMock->method('getRow')->willReturn([]);
        \Db::setInstanceForTesting($dbMock);
        $resultFalse = \LengowAction::updateAction($mockToSend);
        $this->assertFalse($resultFalse, $this->testName.__METHOD__.' false');
        $dbMock2 = $this->getMockBuilder(\Db::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $dbMock2->method('getRow')->willReturn(
            [
                \LengowAction::FIELD_ID => 1,
                \LengowAction::FIELD_RETRY => false,
                \LengowAction::FIELD_ORDER_ID => 1,
                \LengowAction::FIELD_ACTION_ID => 1,
                \LengowAction::FIELD_ACTION_TYPE => 'ship',
                \LengowAction::FIELD_PARAMETERS => $mockParams,
                \LengowAction::FIELD_STATE => 'new',
                \LengowAction::FIELD_CREATED_AT => date('Y-m-d H:i:s'),
                \LengowAction::FIELD_UPDATED_AT => date('Y-m-d H:i:s')
            ]
        );
        $dbMock2->method('update')->willReturn(true);
        \Db::setInstanceForTesting($dbMock2);
        $result = \LengowAction::updateAction($mockToSend);
        $this->assertTrue($result, $this->testName.__METHOD__.' true');
         \Db::deleteTestingInstance();
    }

    /**
     * @covers \LengowAction::finishAction
     */
    public function testFinishAction()
    {
        $dbMock = $this->getMockBuilder(\Db::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $dbMock->method('update')->willReturn(false);
        \Db::setInstanceForTesting($dbMock);
        $resultFalse = \LengowAction::finishAction(1);
        $this->assertFalse($resultFalse, $this->testName.__METHOD__.' false');
        $dbMock->method('update')->willReturn(true);
        \Db::setInstanceForTesting($dbMock);
        $result = \LengowAction::finishAction(2);
        $this->assertFalse($result, $this->testName.__METHOD__.' true');
        \Db::deleteTestingInstance();
    }

    /**
     * @covers \LengowAction::finishAllAction
     */
    public function testFinishAllAction()
    {
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
        $dbMock->method('update')->willReturn(true);
        $dbMock->method('executeS')->willReturn([]);
        \Db::setInstanceForTesting($dbMock);
        $resultFalse = \LengowAction::finishAllActions(1);
        $this->assertFalse($resultFalse, $this->testName.__METHOD__.' false');
        $dbMock->method('executeS')->willReturn([$rowMock]);
        \Db::setInstanceForTesting($dbMock);
        $result = \LengowAction::finishAllActions(2);
        $this->assertFalse($result, $this->testName.__METHOD__.' true');
        \Db::deleteTestingInstance();
    }

    /**
     * @covers \LengowAction::getIntervalTime
     */
    public function testGetIntervalTime()
    {
        $dbMock = $this->getMockBuilder(\Db::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $dateLast = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $dateLast->sub(new \DateInterval("PT1H"));

        $configMock = [
            'name' => \LengowConfiguration::LAST_UPDATE_ACTION_SYNCHRONIZATION,
            'value'=> $dateLast->getTimestamp(),
            'id_lang' => 0
        ];
        $dbMock->method('executeS')->willReturn([$configMock]);
        \Db::setInstanceForTesting($dbMock);
        $result = \LengowAction::getIntervalTime();
        $waited = \LengowAction::MAX_INTERVAL_TIME;
        $this->assertEquals($waited, $result, $this->testName.__METHOD__.' 1 hour');
        \Db::deleteTestingInstance();
    }


}
