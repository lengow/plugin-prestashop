<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use GuzzleHttp\Client;
use Currency;
use Context;
use Db;
use Module;
use Configuration;
use LengowMain;
use LengowAction;
use Tools;
use LengowMarketplace;
use LengowOrder;
use LengowConnector;
use Assert;

class MarketplaceTest extends ModuleTestCase
{

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Test containOrderLine
     *
     * @test
     * @covers LengowMarketplace::containOrderLine
     */
    public function containOrderLine()
    {
        $marketplace = LengowMain::getMarketplaceSingleton(
            'galeries_lafayette',
            1
        );
        $this->assertTrue(!$marketplace->containOrderLine('ship'));

        $marketplace = LengowMain::getMarketplaceSingleton(
            'cdiscount',
            1
        );
        $this->assertTrue($marketplace->containOrderLine('ship'));

        $marketplace = LengowMain::getMarketplaceSingleton(
            'menlook',
            1
        );
        $this->assertTrue($marketplace->containOrderLine('ship'));
    }

    /**
     * Test callAction with optional args carrier
     *
     * @test
     * @covers LengowMarketplace::callAction
     */
    public function callActionWithOptionalCarrierArg()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Marketplace/order_without_carrier.yml');
        $fixture->truncate('lengow_actions');
        $fixture->truncate('lengow_marketplace_carrier');

        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/empty_tracking.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_post.json',
        );
        $order = new LengowOrder(1);
        $this->assertTrue($order->callAction('ship'));

        $this->assertTableContain('lengow_actions', array('id' => '1',  'id_order' => '1', 'retry' => 0));
    }

    /**
     * Test callAction with optional args carrier
     *
     * @test
     * @covers LengowMarketplace::callAction
     */
    public function callActionWithCarrierArg()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Marketplace/order_without_carrier.yml');
        $fixture->truncate('lengow_actions');
        $fixture->truncate('lengow_marketplace_carrier');

        //reset marketplace file
        LengowMain::$registers = array();
        $marketplaceFile =  _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Marketplace/require_carrier_args.json';
        LengowMarketplace::$MARKETPLACES = Tools::jsonDecode(file_get_contents($marketplaceFile));

        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/empty_tracking.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_post.json',
        );
        $order = new LengowOrder(1);
        $this->assertTrue(!$order->callAction('ship'));
        $this->assertTableEmpty('lengow_actions');
    }

    /**
     * Test call action with error
     * @test
     * @covers LengowMarketplace::callAction
     */
    public function callActionErrorApiAccess()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/simple_order.yml');
        $fixture->truncate('lengow_actions');
        $fixture->truncate('lengow_logs_import');

        $order = new LengowOrder(1);
        $marketplace = LengowMain::getMarketplaceSingleton('galeries_lafayette', '1');
        $this->assertFalse($marketplace->callAction('ship', $order));

        $this->assertTableContain(
            'lengow_logs_import',
            array('id' => '1',  'id_order_lengow' => '1', 'message' => 'Forbidden')
        );
    }

    /**
     * Test call action with error
     * @test
     * @covers LengowMarketplace::callAction
     */
    public function callActionWithError()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/empty_tracking_order.yml');
        $fixture->truncate('lengow_actions');
        $fixture->truncate('lengow_logs_import');

        $marketplace = LengowMain::getMarketplaceSingleton('galeries_lafayette', '1');

        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/empty_tracking.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_post.json',
        );
        $order = new LengowOrder(1);
        $this->assertFalse($marketplace->callAction('ship', $order));

        $this->assertTableContain(
            'lengow_logs_import',
            array('id' => '1',  'id_order_lengow' => '1', 'message' => 'Can\'t ship order : tracking_number require')
        );
    }


    /**
     * Test Check finish action
     * @test
     * @covers LengowMarketplace::checkFinishAction
     */
    public function checkFinishAction()
    {
        Configuration::updateValue('LENGOW_IMPORT_PREPROD_ENABLED', true);
        $this->assertFalse(LengowMarketplace::checkFinishAction());


        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/simple_order.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Action/queued_actions.yml');

        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Action/sync_queued_action.json',
        );

        Configuration::updateValue('LENGOW_IMPORT_PREPROD_ENABLED', false);
        $this->assertTrue(LengowMarketplace::checkFinishAction());
        $this->assertTableContain(
            'lengow_actions',
            array('id' => '1',  'id_order' => '1', 'state' => LengowAction::STATE_FINISH)
        );
    }
}
