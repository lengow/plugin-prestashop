<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use GuzzleHttp\Client;
use Currency;
use Db;
use Module;
use Configuration;
use LengowMain;
use LengowHook;
use Tools;
use LengowMarketplace;
use OrderState;
use LengowConnector;
use Assert;

class HookTest extends ModuleTestCase
{

    public function setUp()
    {
        parent::setUp();

        //load module
        Module::getInstanceByName('lengow');

        LengowMain::$registers = array();
    }

    /**
     * Test hookPostUpdateOrderStatus
     *
     * @test
     * @covers LengowHook::hookPostUpdateOrderStatus
     */
    public function hookPostUpdateOrderStatus()
    {

        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/simple_order.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/empty_actions.yml');

        $marketplaceFile = _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Connector/marketplaces.json';
        LengowMarketplace::$MARKETPLACES = array(
            1 => Tools::jsonDecode(file_get_contents($marketplaceFile)),
            2 => Tools::jsonDecode(file_get_contents($marketplaceFile))
        );

        LengowConnector::$testFixturePath = array(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/empty_tracking.json',
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/send_tracking_post.json',
        );

        $this->assertTableEmpty('lengow_actions');

        $hook = new LengowHook(Module::getInstanceByName('lengow'));
        $hook->hookPostUpdateOrderStatus(
            array(
                'id_order' => 1,
                'newOrderStatus' => new OrderState(LengowMain::getOrderState('shipped'))
            )
        );
        $this->assertTableContain('lengow_actions', array('id' => '1', 'id_order' => '1', 'retry' => 0));

        $ret = $hook->hookPostUpdateOrderStatus(
            array(
                'id_order' => 10,
                'newOrderStatus' => new OrderState(LengowMain::getOrderState('shipped'))
            )
        );
        $this->assertFalse($ret, 'Can\'t update not existing order');

        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/empty_actions.yml');
        $this->assertTableEmpty('lengow_actions');
        $ret = $hook->hookPostUpdateOrderStatus(array('id_order' => 10, 'newOrderStatus' => new OrderState(1)));
        $this->assertFalse($ret, 'Don\'t send tracking when not correct order');
    }
}
