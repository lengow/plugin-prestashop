<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use GuzzleHttp\Client;
use Currency;
use Context;
use Db;
use Module;
use Configuration;
use LengowMain;
use LengowImport;
use Tools;
use LengowMarketplace;
use LengowOrder;
use LengowConnector;
use Assert;

class ImportOrderTest extends ModuleTestCase
{

    public function setUp()
    {
        parent::setUp();
        //load module
        Module::getInstanceByName('lengow');
    }

    /**
     * Test currency is present
     *
     * @test
     * @covers LengowImportOrder::checkOrderData
     */
    public function checkCurrencyData()
    {

        Configuration::set('LENGOW_SHOP_ACTIVE', true, null, 1);
        Configuration::set('LENGOW_ACCOUNT_ID', 'nothing', null, 1);
        Configuration::set('LENGOW_ACCESS_TOKEN', 'nothing', null, 1);
        Configuration::set('LENGOW_SECRET_TOKEN', 'nothing', null, 1);
        Configuration::set('LENGOW_SHOP_ACTIVE', true, null, 1);

        $fixture = new Fixture();
        $fixture->truncate('lengow_logs_import');
        $fixture->truncate('lengow_orders');
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/re_import.yml'
        );
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/simple_product.yml'
        );
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/euro_currency.yml'
        );
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_currency.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_currency_data.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_price_data.json'
        );
        $marketplaceFile =  _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Connector/marketplaces.json';
        LengowMarketplace::$MARKETPLACES = Tools::jsonDecode(file_get_contents($marketplaceFile));

        $import = new LengowImport(array('log_output' => false,));
        $result = $import->exec();
        $this->assertEquals(0, $result['order_new']);
        $this->assertEquals(0, $result['order_update']);
        $this->assertEquals(1, $result['order_error']);
        $this->assertTableContain('lengow_orders', array('id' => 1,'order_process_state' => 0));
        $this->assertTableContain(
            'lengow_logs_import',
            array(
                'message'           => 'currency GBP is not available in your shop',
                'id_order_lengow'   => '1',
                'type'              => '1'
            ),
            'Check if currency is present for a shop'
        );
        
        $import2 = new LengowImport(array('log_output' => false));
        $result2 = $import2->exec();
        $this->assertEquals(0, $result2['order_new']);
        $this->assertEquals(0, $result2['order_update']);
        $this->assertEquals(1, $result2['order_error']);
        // $this->assertTableContain('lengow_orders', array('Id' => '2','order_process_state' => '0'));
        $this->assertTableContain(
            'lengow_logs_import',
            array(
                'message'           => 'no currency in the order',
                'id_order_lengow'   => '2',
                'type'              => '1'
            ),
            'Check if the order contains a currency'
        );

        $import3 = new LengowImport(array('log_output' => false));
        $result3 = $import3->exec();
        $this->assertEquals(0, $result3['order_new']);
        $this->assertEquals(0, $result3['order_update']);
        $this->assertEquals(1, $result3['order_error']);
        // $this->assertTableContain('lengow_orders', array('id' => '3','order_process_state' => '0'));
        $this->assertTableContain(
            'lengow_logs_import',
            array(
                'message'           => 'prices can\'t be calculated in the correct currency',
                'id_order_lengow'   => '3',
                'type'              => '1'
            ),
            'Check if the order contains prices with a correct currency'
        );
    }

    /**
     * Test Check Billing data
     *
     * @test
     * @covers LengowImportOrder::checkOrderData
     */
    public function checkBillingData()
    {
        Configuration::set('LENGOW_SHOP_ACTIVE', true, null, 1);
        Configuration::set('LENGOW_ACCOUNT_ID', 'nothing', null, 1);
        Configuration::set('LENGOW_ACCESS_TOKEN', 'nothing', null, 1);
        Configuration::set('LENGOW_SECRET_TOKEN', 'nothing', null, 1);
        Configuration::set('LENGOW_SHOP_ACTIVE', true, null, 1);

        $fixture = new Fixture();
        $fixture->truncate('lengow_logs_import');
        $fixture->truncate('lengow_orders');
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_billing_address.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_billing_country.json'
        );
        $marketplaceFile =  _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Connector/marketplaces.json';
        LengowMarketplace::$MARKETPLACES = Tools::jsonDecode(file_get_contents($marketplaceFile));

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(0, $result['order_new']);
        $this->assertEquals(0, $result['order_update']);
        $this->assertEquals(1, $result['order_error']);
        $this->assertTableContain(
            'lengow_logs_import',
            array(
                'message'           => 'no billing address in the order',
                'id_order_lengow'   => '1',
                'type'              => '1'
            ),
            'Check if the order contains billing address'
        );

        $import2 = new LengowImport(array('log_output' => false));
        $result2 = $import2->exec();
        $this->assertEquals(0, $result2['order_new']);
        $this->assertEquals(0, $result2['order_update']);
        $this->assertEquals(1, $result2['order_error']);
        $this->assertTableContain(
            'lengow_logs_import',
            array(
                'message'           => 'billing address doesn\'t have country',
                'id_order_lengow'   => '2',
                'type'              => '1'
            ),
            'Check if the order contains billing address country'
        );
    }

    /**
     * Test Check if delivery country is present
     *
     * @test
     * @covers LengowImportOrder::checkOrderData
     */
    public function checkDeliveryCountry()
    {
        Configuration::set('LENGOW_SHOP_ACTIVE', true, null, 1);
        Configuration::set('LENGOW_ACCOUNT_ID', 'nothing', null, 1);
        Configuration::set('LENGOW_ACCESS_TOKEN', 'nothing', null, 1);
        Configuration::set('LENGOW_SECRET_TOKEN', 'nothing', null, 1);
        Configuration::set('LENGOW_SHOP_ACTIVE', true, null, 1);

        $fixture = new Fixture();
        $fixture->truncate('lengow_logs_import');
        $fixture->truncate('lengow_orders');
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_delivery_country.json'
        );
        $marketplaceFile =  _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Connector/marketplaces.json';
        LengowMarketplace::$MARKETPLACES = Tools::jsonDecode(file_get_contents($marketplaceFile));

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(0, $result['order_new']);
        $this->assertEquals(0, $result['order_update']);
        $this->assertEquals(1, $result['order_error']);
        $this->assertTableContain(
            'lengow_logs_import',
            array(
                'message'           => 'delivery address doesn\'t have country',
                'id_order_lengow'   => '1',
                'type'              => '1'
            ),
            'Check if the order contains delivery address'
        );
    }

     /**
     * Test Check if cart is empty
     *
     * @test
     * @covers LengowImportOrder::checkOrderData
     */
    public function checkProductData()
    {
        Configuration::set('LENGOW_SHOP_ACTIVE', true, null, 1);
        Configuration::set('LENGOW_ACCOUNT_ID', 'nothing', null, 1);
        Configuration::set('LENGOW_ACCESS_TOKEN', 'nothing', null, 1);
        Configuration::set('LENGOW_SECRET_TOKEN', 'nothing', null, 1);
        Configuration::set('LENGOW_SHOP_ACTIVE', true, null, 1);

        $fixture = new Fixture();
        $fixture->truncate('lengow_logs_import');
        $fixture->truncate('lengow_orders');
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_product_data.json',
        );
        $marketplaceFile =  _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Connector/marketplaces.json';
        LengowMarketplace::$MARKETPLACES = Tools::jsonDecode(file_get_contents($marketplaceFile));

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(0, $result['order_new']);
        $this->assertEquals(0, $result['order_update']);
        $this->assertEquals(1, $result['order_error']);
        $this->assertTableContain(
            'lengow_logs_import',
            array(
                'message'           => 'no product in the order',
                'id_order_lengow'   => '1',
                'type'              => '1'
            ),
            'Check if the order contains a product'
        );
    }
}
