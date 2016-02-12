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

class MarketplaceTest extends ModuleTestCase
{

    public function setUp()
    {
        parent::setUp();

        //load module
        Module::getInstanceByName('lengow');
        //reset marketplace file
        LengowMain::$registers = array();
    }

    /**
     * Test containOrderLine
     *
     * @test
     * @covers LengowMarketplace::containOrderLine
     */
    public function containOrderLine()
    {
        $marketplaceFile =  _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Connector/marketplaces.json';
        LengowMarketplace::$MARKETPLACES = Tools::jsonDecode(file_get_contents($marketplaceFile));

        $marketplace = LengowMain::getMarketplaceSingleton(
            'galeries_lafayette',
            1
        );
        $this->assertTrue(!$marketplace->containOrderLine());

        $marketplace = LengowMain::getMarketplaceSingleton(
            'cdiscount',
            1
        );
        $this->assertTrue($marketplace->containOrderLine());

        $marketplace = LengowMain::getMarketplaceSingleton(
            'menlook',
            1
        );
        $this->assertTrue($marketplace->containOrderLine());
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

        $marketplaceFile =  _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Marketplace/optional_carrier_args.json';
        LengowMarketplace::$MARKETPLACES = Tools::jsonDecode(file_get_contents($marketplaceFile));

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


}
