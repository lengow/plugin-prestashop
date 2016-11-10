<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use GuzzleHttp\Client;
use Currency;
use Context;
use Db;
use Module;
use Configuration;
use LengowMain;
use LengowExport;
use Tools;
use LengowMarketplace;
use LengowOrder;
use LengowConnector;
use Assert;

class OrderTest extends ModuleTestCase
{

    public function setUp()
    {
        parent::setUp();

        //load module
        Module::getInstanceByName('lengow');

        LengowMain::$registers =array();

        Configuration::set('LENGOW_SHOP_ACTIVE', true, null, 1);
        Configuration::set('LENGOW_ACCOUNT_ID', 'nothing', null, 1);
        Configuration::set('LENGOW_ACCESS_TOKEN', 'nothing', null, 1);
        Configuration::set('LENGOW_SECRET_TOKEN', 'nothing', null, 1);
        Configuration::set('LENGOW_SHOP_ACTIVE', true, null, 1);
    }

    /**
     * Test reImportOrder
     *
     * @test
     * @covers LengowOrder::reImportOrder
     */
    public function reImportOrder()
    {

        Configuration::set('LENGOW_SHOP_ACTIVE', true, null, 1);
        Configuration::set('LENGOW_ACCOUNT_ID', 'nothing', null, 1);
        Configuration::set('LENGOW_ACCESS_TOKEN', 'nothing', null, 1);
        Configuration::set('LENGOW_SECRET_TOKEN', 'nothing', null, 1);
        Configuration::set('LENGOW_SHOP_ACTIVE', true, null, 1);

        $fixture = new Fixture();
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/re_import.yml'
        );
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/simple_product.yml'
        );
        LengowConnector::$testFixturePath =
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/re_import.json';
        $marketplaceFile =  _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Connector/marketplaces.json';
        LengowMarketplace::$MARKETPLACES = array(1 => Tools::jsonDecode(file_get_contents($marketplaceFile)));


        $this->assertTableContain('lengow_orders', array('id' => '1',  'id_order' => 'NULL'));
        LengowOrder::reImportOrder(1);
        $this->assertTableContain('lengow_orders', array('id' => '1',  'id_order' => '1'));
    }

    /**
     * Test callAction
     *
     * @test
     * @covers LengowOrder::callAction
     */
    public function callActionShip()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/simple_order.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/empty_actions.yml');

        $marketplaceFile =  _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Connector/marketplaces.json';
        LengowMarketplace::$MARKETPLACES = array(1 => Tools::jsonDecode(file_get_contents($marketplaceFile)));

        LengowConnector::$testFixturePath = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/empty_tracking.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_post.json',
        );
        $order = new LengowOrder(1);
        $this->assertTrue($order->callAction('ship'));

        $this->assertTableContain('lengow_actions', array('id' => '1',  'id_order' => '1', 'retry' => 0));

        LengowConnector::$testFixturePath = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_queued.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_post.json',
        );
        $this->assertTrue($order->callAction('ship'));
        $this->assertTableContain('lengow_actions', array('id' => '1',  'id_order' => '1', 'retry' => 1));

        LengowConnector::$testFixturePath = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_queued.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_post.json',
        );
        $this->assertTrue($order->callAction('ship'));
        $this->assertTableContain('lengow_actions', array('id' => '1',  'id_order' => '1', 'retry' => 2));


        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/empty_tracking_order.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/empty_actions.yml');

        $order = new LengowOrder(1);
        $this->assertFalse($order->callAction('ship'), 'Cant ship order without tracking');
    }

    /**
     * Test Send tracking without matching marketplace
     * @test
     */
    public function sendWithoutMarketplaceMatch()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/simple_order.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/empty_actions.yml');
        $fixture->truncate('lengow_marketplace_carrier');

        $marketplaceFile =  _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Marketplace/require_carrier_args.json';
        LengowMarketplace::$MARKETPLACES = array(1 => Tools::jsonDecode(file_get_contents($marketplaceFile)));

        LengowConnector::$testFixturePath = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/empty_tracking.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_post.json',
        );
        $order = new LengowOrder(1);
        $this->assertFalse($order->callAction('ship'));

        $this->assertLogContain('you need to match carrier Standard with country');
    }

    /**
     * Test sendTracking Order line
     *
     * @test
     * @covers LengowOrder::callAction
     */
    public function sendTrackingOrderLine()
    {
        Configuration::set('LENGOW_SHOP_ACTIVE', true, null, 1);
        Configuration::set('LENGOW_ACCOUNT_ID', 'nothing', null, 1);
        Configuration::set('LENGOW_ACCESS_TOKEN', 'nothing', null, 1);
        Configuration::set('LENGOW_SECRET_TOKEN', 'nothing', null, 1);
        Configuration::set('LENGOW_SHOP_ACTIVE', true, null, 1);

        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/simple_order.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/empty_actions.yml');

        $marketplaceFile =  _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Connector/marketplaces_line.json';
        LengowMarketplace::$MARKETPLACES = array(1 => Tools::jsonDecode(file_get_contents($marketplaceFile)));

        LengowConnector::$testFixturePath = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/empty_tracking.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_post_ol1.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/empty_tracking.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_post_ol2.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/empty_tracking.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_post_ol3.json',
        );
        $order = new LengowOrder(1);
        $this->assertTrue($order->callAction('ship'));

        $this->assertTableContain('lengow_actions', array('id' => '1',  'id_order' => '1', 'retry' => 0));
        $this->assertTableContain('lengow_actions', array('id' => '2',  'id_order' => '1', 'retry' => 0));
        $this->assertTableContain('lengow_actions', array('id' => '3',  'id_order' => '1', 'retry' => 0));

        LengowConnector::$testFixturePath = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_queued_ol1.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_post_ol1.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_queued_ol2.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_post_ol2.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_queued_ol3.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_post_ol3.json',
        );
        $this->assertTrue($order->callAction('ship'));
        $this->assertTableContain('lengow_actions', array('id' => '1',  'id_order' => '1', 'retry' => 1));

        LengowConnector::$testFixturePath = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_queued_ol1.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_post_ol1.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_queued_ol2.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_post_ol2.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_queued_ol3.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_post_ol3.json',
        );
        $this->assertTrue($order->callAction('ship'));
        $this->assertTableContain('lengow_actions', array('id' => '1',  'id_order' => '1', 'retry' => 2));
    }


    /**
     * Test callAction
     *
     * @test
     * @covers LengowOrder::callAction
     */
    public function callActionCancel()
    {
        Configuration::set('LENGOW_SHOP_ACTIVE', true, null, 1);
        Configuration::set('LENGOW_ACCOUNT_ID', 'nothing', null, 1);
        Configuration::set('LENGOW_ACCESS_TOKEN', 'nothing', null, 1);
        Configuration::set('LENGOW_SECRET_TOKEN', 'nothing', null, 1);
        Configuration::set('LENGOW_SHOP_ACTIVE', true, null, 1);

        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/simple_order.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/empty_actions.yml');

        $marketplaceFile =  _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Connector/marketplaces.json';
        LengowMarketplace::$MARKETPLACES = array(1 => Tools::jsonDecode(file_get_contents($marketplaceFile)));

        LengowConnector::$testFixturePath = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/empty_tracking.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/cancel_tracking_post.json',
        );
        $order = new LengowOrder(1);
        $this->assertTrue($order->callAction('cancel'));

        $this->assertTableContain('lengow_actions', array('id' => '1',  'id_order' => '1', 'retry' => 0));

        LengowConnector::$testFixturePath = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_queued.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/cancel_tracking_post.json',
        );
        $this->assertTrue($order->callAction('cancel'));
        $this->assertTableContain('lengow_actions', array('id' => '1',  'id_order' => '1', 'retry' => 1));

        LengowConnector::$testFixturePath = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/send_tracking_queued.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Order/cancel_tracking_post.json',
        );
        $this->assertTrue($order->callAction('cancel'));
        $this->assertTableContain('lengow_actions', array('id' => '1',  'id_order' => '1', 'retry' => 2));
    }

    /**
     * Test syncOldData
     *
     * @test
     * @covers LengowOrder::syncOldData
     */
    public function syncOldDataCountry()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/sync_old_data.yml');

        $this->assertTableContain('lengow_orders', array('id' => '1',  'delivery_country_iso' => ''));
        LengowOrder::syncOldData();
        $this->assertTableContain('lengow_orders', array('id' => '1',  'delivery_country_iso' => 'fr'));
    }

    /**
     * Test syncOldData
     *
     * @test
     * @covers LengowOrder::syncOldData
     */
    public function syncOldDataLengowState()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/sync_old_data_multiple.yml');

        $this->assertTableContain('lengow_orders', array('id' => '1',  'marketplace_sku' => '1300435653833-A'));
        $this->assertTableContain('lengow_orders', array('id' => '2',  'marketplace_sku' => '1300435653833-A'));
        $this->assertTableContain('lengow_orders', array('id' => '3',  'marketplace_sku' => '1300435653833-A'));
        LengowOrder::syncOldData();
        $this->assertTableContain('lengow_orders', array('id' => '1',  'marketplace_sku' => '1300435653833-A'));
        $this->assertTableNotContain('lengow_orders', array('id' => '2',  'marketplace_sku' => '1300435653833-A'));
        $this->assertTableNotContain('lengow_orders', array('id' => '3',  'marketplace_sku' => '1300435653833-A'));
    }
}
