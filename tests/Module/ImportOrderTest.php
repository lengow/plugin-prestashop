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
use LengowImportOrder;
use Tools;
use LengowMarketplace;
use LengowOrder;
use LengowConnector;
use Assert;
use LengowConfiguration;

class ImportOrderTest extends ModuleTestCase
{

    public function setUp()
    {
        parent::setUp();
        //load module
        Module::getInstanceByName('lengow');
    }

    public function chargeFixture()
    {
        $fixture = new Fixture();
        $fixture->truncate('orders');
        $fixture->truncate('lengow_logs_import');
        $fixture->truncate('lengow_orders');
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/euro_currency.yml'
        );
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/simple_product.yml'
        );
    }

    public function chargeConfig()
    {
        Configuration::set('LENGOW_SHOP_ACTIVE', true, null, 1);
        Configuration::set('LENGOW_ACCOUNT_ID', 'nothing', null, 1);
        Configuration::set('LENGOW_ACCESS_TOKEN', 'nothing', null, 1);
        Configuration::set('LENGOW_SECRET_TOKEN', 'nothing', null, 1);
    }

    /**
     * Test currency is present
     *
     * @test
     * @covers LengowImportOrder::checkOrderData
     */
    public function checkCurrencyData()
    {
        $this->chargeConfig();
        $this->chargeFixture();
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_currency.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_currency_data.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_price_data.json'
        );
        $import = new LengowImport(array('log_output' => false,));
        $result = $import->exec();
        $this->assertEquals(0, $result['order_new'], '[Check Currency Shop] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Check Currency Shop] nb order update');
        $this->assertEquals(1, $result['order_error'], '[Check Currency Shop] nb order error');
        $this->assertTableContain(
            'lengow_orders',
            array(
                'marketplace_sku' => '1300435653833-A',
                'order_process_state' => 0
            ),
            '[Check Currency Shop] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'lengow_logs_import',
            array(
                'message'           => 'currency GBP is not available in your shop',
                'id_order_lengow'   => '1',
                'type'              => '1'
            ),
            '[Check Currency Shop] Check if currency is present for a shop'
        );

        $import2 = new LengowImport(array('log_output' => false));
        $result2 = $import2->exec();
        $this->assertEquals(0, $result2['order_new'], '[Check Currency Data] nb order new');
        $this->assertEquals(0, $result2['order_update'], '[Check Currency Data] nb order update');
        $this->assertEquals(1, $result2['order_error'], '[Check Currency Data] nb order error');
        $this->assertTableContain(
            'lengow_orders',
            array(
                'marketplace_sku' => '1300435653834-A',
                'order_process_state' => 0
            ),
            '[Check Currency Data] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'lengow_logs_import',
            array(
                'message'           => 'no currency in the order',
                'id_order_lengow'   => '2',
                'type'              => '1'
            ),
            '[Check Currency Data] Check if the order contains a currency'
        );

        $import3 = new LengowImport(array('log_output' => false));
        $result3 = $import3->exec();
        $this->assertEquals(0, $result3['order_new'], '[Check Currency Prices] nb order new');
        $this->assertEquals(0, $result3['order_update'], '[Check Currency Prices] nb order update');
        $this->assertEquals(1, $result3['order_error'], '[Check Currency Prices] nb order error');
        $this->assertTableContain(
            'lengow_orders',
            array(
                'marketplace_sku' => '1300435653835-A',
                'order_process_state' => 0
            ),
            '[Check Currency Prices] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'lengow_logs_import',
            array(
                'message'           => 'prices can\'t be calculated in the correct currency',
                'id_order_lengow'   => '3',
                'type'              => '1'
            ),
            '[Check Currency Prices] Check if the order contains prices with a correct currency'
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
        $this->chargeConfig();
        $this->chargeFixture();
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_billing_address.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_billing_country.json'
        );

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(0, $result['order_new'], '[Check Billing Address] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Check Billing Address] nb order update');
        $this->assertEquals(1, $result['order_error'], '[Check Billing Address] nb order error');
        $this->assertTableContain(
            'lengow_orders',
            array(
                'marketplace_sku' => '1300435653836-A',
                'order_process_state' => 0
            ),
            '[Check Billing Address] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'lengow_logs_import',
            array(
                'message'           => 'no billing address in the order',
                'id_order_lengow'   => '1',
                'type'              => '1'
            ),
            '[Check Billing Address] Check if the order contains billing address'
        );

        $import2 = new LengowImport(array('log_output' => false));
        $result2 = $import2->exec();
        $this->assertEquals(0, $result2['order_new'], '[Check Billing Country] nb order new');
        $this->assertEquals(0, $result2['order_update'], '[Check Billing Country] nb order update');
        $this->assertEquals(1, $result2['order_error'], '[Check Billing Country] nb order error');
        $this->assertTableContain(
            'lengow_orders',
            array(
                'marketplace_sku' => '1300435653837-A',
                'order_process_state' => 0
            ),
            '[Check Billing Country] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'lengow_logs_import',
            array(
                'message'           => 'billing address doesn\'t have country',
                'id_order_lengow'   => '2',
                'type'              => '1'
            ),
            '[Check Billing Country] Check if the order contains billing address country'
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
        $this->chargeConfig();
        $this->chargeFixture();
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_delivery_country.json'
        );

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(0, $result['order_new'], '[Check Delivery Country] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Check Delivery Country] nb order update');
        $this->assertEquals(1, $result['order_error'], '[Check Delivery Country] nb order error');
        $this->assertTableContain(
            'lengow_orders',
            array(
                'marketplace_sku' => '1300435653838-A',
                'order_process_state' => 0
            ),
            '[Check Delivery Country] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'lengow_logs_import',
            array(
                'message'           => 'delivery address doesn\'t have country',
                'id_order_lengow'   => '1',
                'type'              => '1'
            ),
            '[Check Delivery Country] Check if the order contains delivery address'
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
        $this->chargeConfig();
        $this->chargeFixture();
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_product_data.json',
        );

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(0, $result['order_new'], '[Check Product Data] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Check Product Data] nb order update');
        $this->assertEquals(1, $result['order_error'], '[Check Product Data] nb order error');
        $this->assertTableContain(
            'lengow_orders',
            array(
                'marketplace_sku' => '1300435653833-A',
                'order_process_state' => 0
            ),
            '[Check Product Data] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'lengow_logs_import',
            array(
                'message'           => 'no product in the order',
                'id_order_lengow'   => '1',
                'type'              => '1'
            ),
            '[Check Product Data] Check if the order contains a product'
        );
    }

    /**
     * Test not import orders shipped by marketplace
     *
     * @test
     * @covers LengowImportOrder::importOrder
     */
    public function notImportOrdershippedByMP()
    {
        $this->chargeConfig();
        $this->chargeFixture();
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_shipped_mp.json'
        );

        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_STOCK_SHIP_MP', false);
        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_SHIP_MP_ENABLED', false);

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(0, $result['order_new'], '[Not Import Order Shipped By MP] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Not Import Order Shipped By MP] nb order update');
        $this->assertEquals(0, $result['order_error'], '[Not Import Order Shipped By MP] nb order error');
        $this->assertTableContain(
            'lengow_orders',
            array(
                'id_order'              => 'NULL',
                'marketplace_sku'       => '1300435653833-A',
                'order_process_state'   => '2',
                'sent_marketplace'      => '1'
            ),
            '[Not Import Order Shipped By MP] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'stock_available',
            array(
                'id_product' => '1',
                'quantity'   => '10'
            ),
            '[Not Import Order Shipped By MP] Check if the stock is not decremented'
        );
    }

    /**
     * Test import orders shipped by marketplace and decrement stock
     *
     * @test
     * @covers LengowImportOrder::importOrder
     */
    public function importOrdershippedByMPWithStock()
    {
        $this->chargeConfig();
        $this->chargeFixture();
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_shipped_mp.json'
        );

        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_STOCK_SHIP_MP', true);
        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_SHIP_MP_ENABLED', true);

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(1, $result['order_new'], '[Import Order Shipped By MP With Stock] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Import Order Shipped By MP With Stock] nb order update');
        $this->assertEquals(0, $result['order_error'], '[Import Order Shipped By MP With Stock] nb order error');
        $this->assertTableContain(
            'lengow_orders',
            array(
                'id_order'              => '1',
                'marketplace_sku'       => '1300435653833-A',
                'order_process_state'   => '2',
                'sent_marketplace'      => '1'
            ),
            '[Import Order Shipped By MP With Stock] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'stock_available',
            array(
                'id_product' => '1',
                'quantity'   => '9'
            ),
            '[Import Order Shipped By MP With Stock] Check if the stock is decremented'
        );
    }

    /**
     * Test import orders shipped by marketplace and not decrement stock
     *
     * @test
     * @covers LengowImportOrder::importOrder
     */
    public function importOrdershippedByMPWithoutStock()
    {
        $this->chargeConfig();
        $this->chargeFixture();
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_shipped_mp.json'
        );

        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_STOCK_SHIP_MP', false);
        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_SHIP_MP_ENABLED', true);

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(1, $result['order_new'], '[Import Order Shipped By MP With Stock] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Import Order Shipped By MP With Stock] nb order update');
        $this->assertEquals(0, $result['order_error'], '[Import Order Shipped By MP With Stock] nb order error');
        $this->assertTableContain(
            'lengow_orders',
            array(
                'id_order'              => '1',
                'marketplace_sku'       => '1300435653833-A',
                'order_process_state'   => '2',
                'sent_marketplace'      => '1'
            ),
            '[Import Order Shipped By MP With Stock] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'stock_available',
            array(
                'id_product' => '1',
                'quantity'   => '10'
            ),
            '[Import Order Shipped By MP With Stock] Check if the stock is decremented'
        );
    }

    /**
     * Test order is already imported
     *
     * @test
     * @covers LengowImportOrder::importOrder
     */
    public function orderAllreadyImported()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/simple_order.yml'
        );
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/allready_imported.json'
        );
        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(0, $result['order_new'], '[Order Allready Imported] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Order Allready Imported] nb order update');
        $this->assertEquals(0, $result['order_error'], '[Order Allready Imported] nb order error');
    }

    /**
     * Test import orders shipped by marketplace and not decrement stock
     *
     * @test
     * @covers LengowImportOrder::importOrder
     */
    public function checkAndUpdateOrder()
    {
        $this->chargeConfig();
        $this->chargeFixture();
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_update_1.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_update_2.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_update_3.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_update_4.json'
        );

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(0, $result['order_new'], '[Check And Update Order] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Check And Update Order] nb order update');
        $this->assertEquals(0, $result['order_error'], '[Check And Update Order] nb order error');
        $this->assertTableNotContain(
            'lengow_orders',
            array('marketplace_sku' => '1300435653833-A'),
            '[Check And Update Order] Check if order is present in Lengow Orders table'
        );

        $import2 = new LengowImport(array('log_output' => false));
        $result2 = $import2->exec();
        $this->assertEquals(1, $result2['order_new'], '[Check And Update Order Accepted] nb order new');
        $this->assertEquals(0, $result2['order_update'], '[Check And Update Order Accepted] nb order update');
        $this->assertEquals(0, $result2['order_error'], '[Check And Update Order Accepted] nb order error');
        $this->assertTableContain(
            'lengow_orders',
            array(
                'id_order'              => '1',
                'marketplace_sku'       => '1300435653833-A',
                'order_process_state'   => '1',
                'tracking'              => '',
                'order_lengow_state'    => 'accepted'
            ),
            '[Check And Update Order Accepted] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'orders',
            array(
                'id_order'        => '1',
                'shipping_number' => '',
                'current_state'   => '2'
            ),
            '[Check And Update Order Accepted] Check if order is present in Orders Prestashop table'
        );

        $order = new LengowOrder();
        $order->clearCache(true);

        $import3 = new LengowImport(array('log_output' => false));
        $result3 = $import3->exec();
        $this->assertEquals(0, $result3['order_new'], '[Check And Update Order Shipped] nb order new');
        $this->assertEquals(1, $result3['order_update'], '[Check And Update Order Shipped] nb order update');
        $this->assertEquals(0, $result3['order_error'], '[Check And Update Order Shipped] nb order error');
        $this->assertTableContain(
            'lengow_orders',
            array(
                'id_order'              => '1',
                'marketplace_sku'       => '1300435653833-A',
                'order_process_state'   => '2',
                'tracking'              => '8D00432154798',
                'order_lengow_state'    => 'shipped'
            ),
            '[Check And Update Order Shipped] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'orders',
            array(
                'id_order'        => '1',
                'shipping_number' => '8D00432154798',
                'current_state'   => '4'
            ),
            '[Check And Update Order Shipped] Check if order is present in Orders Prestashop table'
        );

        $order = new LengowOrder();
        $order->clearCache(true);

        $import3 = new LengowImport(array('log_output' => false));
        $result3 = $import3->exec();
        $this->assertEquals(0, $result3['order_new'], '[Check And Update Order Canceled] nb order new');
        $this->assertEquals(1, $result3['order_update'], '[Check And Update Order Canceled] nb order update');
        $this->assertEquals(0, $result3['order_error'], '[Check And Update Order Canceled] nb order error');
        $this->assertTableContain(
            'lengow_orders',
            array(
                'id_order'              => '1',
                'marketplace_sku'       => '1300435653833-A',
                'order_process_state'   => '2',
                'order_lengow_state'    => 'canceled'
            ),
            '[Check And Update Order Canceled] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'orders',
            array(
                'id_order'        => '1',
                'current_state'   => '6'
            ),
            '[Check And Update Order Canceled] Check if order is present in Orders Prestashop table'
        );
    }

    /**
     * Test getCarrierId
     *
     * @test
     * @expectedException        LengowException
     * @expectedExceptionMessage Shipping address don't have country
     * @covers LengowImport::getCarrierId
     */
    public function getCarrierIdEmptyAddress()
    {
        $shipping_address = (object) array();
        $order_data = Tools::JsonDecode(
            file_get_contents(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/get_carrier_id.json')
        );
        $import = new LengowImportOrder(array(
            'id_shop' => 1,
            'id_shop_group' => 1,
            'id_lang' => 1,
            'context' => Context::getContext(),
            'force_product' => true,
            'preprod_mode' => false,
            'log_output' => false,
            'marketplace_sku' => 1,
            'delivery_address_id' => 1,
            'order_data' => $order_data,
            'package_data' => 1,
            'first_package' => 1,
        ));
        $this->invokeMethod($import, 'getCarrierId', array($shipping_address));
    }

    /**
     * Test getCarrierId
     *
     * @test
     * @expectedException        LengowException
     * @expectedExceptionMessage Shipping address don't have country
     * @covers LengowImport::getCarrierId
     */
    public function getCarrierIdEmptyAddressCountry()
    {
        $shipping_address = (object) array('id_country' => 0);
        $order_data = Tools::JsonDecode(
            file_get_contents(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/get_carrier_id.json')
        );
        $import = new LengowImportOrder(array(
            'id_shop' => 1,
            'id_shop_group' => 1,
            'id_lang' => 1,
            'context' => Context::getContext(),
            'force_product' => true,
            'preprod_mode' => false,
            'log_output' => false,
            'marketplace_sku' => 1,
            'delivery_address_id' => 1,
            'order_data' => $order_data,
            'package_data' => 1,
            'first_package' => 1,
        ));
        $this->invokeMethod($import, 'getCarrierId', array($shipping_address));
    }

    /**
     * Test getCarrierId
     *
     * @test
     * @expectedException        LengowException
     * @expectedExceptionMessage You must select a default carrier for country : France
     * @covers LengowImport::getCarrierId
     */
    public function getCarrierIdRequireCarrierError()
    {
        $shipping_address = (object) array('id_country' => 8);
        $order_data = Tools::JsonDecode(
            file_get_contents(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/get_carrier_id.json')
        );
        $import = new LengowImportOrder(array(
            'id_shop' => 1,
            'id_shop_group' => 1,
            'id_lang' => 1,
            'context' => Context::getContext(),
            'force_product' => true,
            'preprod_mode' => false,
            'log_output' => false,
            'marketplace_sku' => 1,
            'delivery_address_id' => 1,
            'order_data' => $order_data,
            'package_data' => $order_data->packages[0],
            'first_package' => 1,
        ));

        $fixture = new Fixture();
        $fixture->truncate('lengow_marketplace_carrier');

        $this->invokeMethod($import, 'loadTrackingData');
        $this->invokeMethod($import, 'getCarrierId', array($shipping_address));
    }



    /**
     * Test getCarrierId
     *
     * @test
     * @covers LengowImport::getCarrierId
     */
    public function getCarrierIdRequireCarrier()
    {
        $shipping_address = (object) array('id_country' => 8);
        $order_data = Tools::JsonDecode(
            file_get_contents(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/get_carrier_id.json')
        );
        $import = new LengowImportOrder(array(
            'id_shop' => 1,
            'id_shop_group' => 1,
            'id_lang' => 1,
            'context' => Context::getContext(),
            'force_product' => true,
            'preprod_mode' => false,
            'log_output' => false,
            'marketplace_sku' => 1,
            'delivery_address_id' => 1,
            'order_data' => $order_data,
            'package_data' => $order_data->packages[0],
            'first_package' => 1,
        ));

        $fixture = new Fixture();
        $fixture->loadFixture(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/get_carrier_id_require_carrier.yml'
        );
        //reset marketplace file
        LengowMain::$registers = array();
        $marketplaceFile =  _PS_MODULE_DIR_.
            'lengow/tests/Module/Fixtures/ImportOrder/get_carrier_id_require_carrier.json';
        LengowMarketplace::$MARKETPLACES = Tools::jsonDecode(file_get_contents($marketplaceFile));

        $this->invokeMethod($import, 'loadTrackingData');
        $this->assertEquals(1, $this->invokeMethod($import, 'getCarrierId', array($shipping_address)));
    }

    /**
     * Test if external ID exist
     *
     * @test
     * @covers LengowImportOrder::checkExternalIds
     */
    public function checkExternalIds()
    {
        $this->chargeConfig();
        $this->chargeFixture();
        $fixture = new Fixture();
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/simple_order.yml'
        );
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_external_id.json'
        );
        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(0, $result['order_new'], '[Check External Ids] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Check External Ids] nb order update');
        $this->assertEquals(0, $result['order_error'], '[Check External Ids] nb order error');
        $this->assertTableNotContain(
            'lengow_orders',
            array('marketplace_sku' => '1300435653212-A'),
            '[Check External Ids] Check if order is present in Lengow Orders table'
        );
    }

    /**
     * Test check order amount and shipping cost
     *
     * @test
     * @covers LengowImportOrder::getOrderAmount
     */
    public function getOrderAmount()
    {
        $this->chargeConfig();
        $this->chargeFixture();
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/order_amount.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/order_amount_wt_fees.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/order_amount_wt_shipping.json'
        );

        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_PROCESSING_FEE', true);
        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_FORCE_PRODUCT', true);

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(1, $result['order_new'], '[Get Order Amount] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Get Order Amount] nb order update');
        $this->assertEquals(0, $result['order_error'], '[Get Order Amount] nb order error');
        $this->assertTableContain(
            'lengow_orders',
            array(
                'id_order'          => '1',
                'marketplace_sku'   => '1300435653833-A',
                'total_paid'        => '35.00',
                'order_item'        => '5'
            ),
            '[Get Order Amount] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'orders',
            array(
                'id_order'          => '1',
                'total_paid'        => '35.000000',
                'total_paid_real'   => '35.000000',
                'total_products_wt' => '26.500000',
                'total_wrapping'    => '3.500000',
                'total_shipping'    => '5.000000',
                'module'            => 'lengow_payment',
                'payment'           => 'galeries_lafayette'
            ),
            '[Get Order Amount] Check if order is present in Orders Prestashop table'
        );

        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_PROCESSING_FEE', false);

        $import2 = new LengowImport(array('log_output' => false));
        $result2 = $import2->exec();
        $this->assertEquals(1, $result2['order_new'], '[Get Order Amount] nb order new');
        $this->assertEquals(0, $result2['order_update'], '[Get Order Amount] nb order update');
        $this->assertEquals(0, $result2['order_error'], '[Get Order Amount] nb order error');
        $this->assertTableContain(
            'lengow_orders',
            array(
                'id_order'          => '2',
                'marketplace_sku'   => '1300435653834-A',
                'total_paid'        => '31.50',
                'order_item'        => '5'
            ),
            '[Get Order Amount] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'orders',
            array(
                'id_order'          => '2',
                'total_paid'        => '31.500000',
                'total_paid_real'   => '31.500000',
                'total_products_wt' => '26.500000',
                'total_wrapping'    => '0.000000',
                'total_shipping'    => '5.000000',
                'module'            => 'lengow_payment',
                'payment'           => 'galeries_lafayette'
            ),
            '[Get Order Amount] Check if order is present in Orders Prestashop table'
        );

        $import3 = new LengowImport(array('log_output' => false));
        $result3 = $import3->exec();
        $this->assertEquals(1, $result3['order_new'], '[Get Order Amount] nb order new');
        $this->assertEquals(0, $result3['order_update'], '[Get Order Amount] nb order update');
        $this->assertEquals(0, $result3['order_error'], '[Get Order Amount] nb order error');
        $this->assertTableContain(
            'lengow_orders',
            array(
                'id_order'          => '3',
                'marketplace_sku'   => '1300435653835-A',
                'total_paid'        => '26.50',
                'order_item'        => '5'
            ),
            '[Get Order Amount] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'orders',
            array(
                'id_order'          => '3',
                'total_paid'        => '26.500000',
                'total_paid_real'   => '26.500000',
                'total_products_wt' => '26.500000',
                'total_wrapping'    => '0.000000',
                'total_shipping'    => '0.000000',
                'module'            => 'lengow_payment',
                'payment'           => 'galeries_lafayette'
            ),
            '[Get Order Amount] Check if order is present in Orders Prestashop table'
        );
    }

    /**
     * Test check order tracking datas
     *
     * @test
     * @covers LengowImportOrder::loadTrackingData
     */
    public function loadTrackingData()
    {
        $this->chargeConfig();
        $this->chargeFixture();
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_shipped_mp.json'
        );
        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_STOCK_SHIP_MP', true);
        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_SHIP_MP_ENABLED', true);
        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(1, $result['order_new'], '[Load Tracking Data] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Load Tracking Data] nb order update');
        $this->assertEquals(0, $result['order_error'], '[Load Tracking Data] nb order error');
        $this->assertTableContain(
            'lengow_orders',
            array(
                'id_order'          => '1',
                'marketplace_sku'   => '1300435653833-A',
                'carrier'           => 'LAPOSTE',
                'method'            => 'follow-up letter',
                'tracking'          => '8V4564654654',
                'sent_marketplace'  => '1'
            ),
            '[Load Tracking Data] Check if order is present in Lengow Orders table'
        );
    }

    /**
     * Test check if comment is created
     *
     * @test
     * @covers LengowImportOrder::addCommentOrder
     */
    public function addCommentOrder()
    {
        $this->chargeConfig();
        $this->chargeFixture();
        $fixture = new Fixture();
        $fixture->truncate('message');
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/order_amount_wt_fees.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/check_update_2.json'
        );

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(1, $result['order_new'], '[Add Comment Order] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Add Comment Order] nb order update');
        $this->assertEquals(0, $result['order_error'], '[Add Comment Order] nb order error');
        $this->assertTableContain(
            'lengow_orders',
            array(
                'id_order'          => '1',
                'marketplace_sku'   => '1300435653834-A',
                'message'           => 'product NAME003 is canceled'
            ),
            '[Add Comment Order] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'message',
            array(
                'id_order'  => '1',
                'message'   => 'product NAME003 is canceled',
                'private'   => '1'
            ),
            '[Add Comment Order] Check if order is present in message Prestashop table'
        );

        $import2 = new LengowImport(array('log_output' => false));
        $result2 = $import2->exec();
        $this->assertEquals(1, $result2['order_new'], '[Add Comment Order] nb order new');
        $this->assertEquals(0, $result2['order_update'], '[Add Comment Order] nb order update');
        $this->assertEquals(0, $result2['order_error'], '[Add Comment Order] nb order error');
        $this->assertTableContain(
            'lengow_orders',
            array(
                'id_order'          => '2',
                'marketplace_sku'   => '1300435653833-A',
                'message'           => ''
            ),
            '[Add Comment Order] Check if order is present in Lengow Orders table'
        );
        $this->assertTableNotContain(
            'message',
            array(
                'id_order'  => '2',
                'message'   => '',
                'private'   => '1'
            ),
            '[Add Comment Order] Check if order is present in message Prestashop table'
        );
    }

    /**
     * Test check if lengow order is created with all elements
     *
     * @test
     * @covers LengowImportOrder::createLengowOrder
     */
    public function createLengowOrder()
    {
        $this->chargeConfig();
        $this->chargeFixture();
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/order_amount.json'
        );

        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_PROCESSING_FEE', true);
        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_FORCE_PRODUCT', true);

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(1, $result['order_new'], '[Add Comment Order] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Add Comment Order] nb order update');
        $this->assertEquals(0, $result['order_error'], '[Add Comment Order] nb order error');
        $this->assertTableContain(
            'lengow_orders',
            array(
                'id_order'              => '1',
                'marketplace_sku'       => '1300435653833-A',
                'id_shop'               => '1',
                'id_shop_group'         => '1',
                'id_lang'               => '1',
                'marketplace_name'      => 'galeries_lafayette',
                'message'               => 'product NAME003 is canceled',
                'total_paid'            => '35.00',
                'carrier'               => 'LAPOSTE',
                'tracking'              => '8V4564654654',
                'is_reimported'         => '0',
                'delivery_address_id'   => '7526',
                'method'                => 'follow-up letter',
                'sent_marketplace'      => '0',
                'currency'              => 'EUR',
                'order_process_state'   => '2',
                'order_date'            => date('Y-m-d H:i:s', strtotime('2015-11-23T17:08:00.098728Z')),
                'order_item'            => '5',
                'delivery_country_iso'  => 'FR',
                'customer_name'         => 'Yvette Alcalde',
                'order_lengow_state'    => 'shipped'
            ),
            '[Add Comment Order] Check if order is present in Lengow Orders table'
        );
    }

    /**
     * Test check if order line is saved in lengow table
     *
     * @test
     * @covers LengowImportOrder::saveLengowOrderLine
     */
    public function saveLengowOrderLine()
    {
        $this->chargeConfig();
        $this->chargeFixture();
        $fixture = new Fixture();
        $fixture->truncate('lengow_order_line');
        $fixture->truncate('order_detail');
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/order_line_saved.json'
        );

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(1, $result['order_new'], '[Save Lengow Order Line] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Save Lengow Order Line] nb order update');
        $this->assertEquals(0, $result['order_error'], '[Save Lengow Order Line] nb order error');
        $this->assertTableContain(
            'lengow_order_line',
            array(
                'id_order'          => '1',
                'id_order_line'     => '1300435653833-A-1',
                'id_order_detail'   => '1'
            ),
            '[Save Lengow Order Line] Check if order is present in Lengow Order Line table'
        );
        $this->assertTableContain(
            'lengow_order_line',
            array(
                'id_order'          => '1',
                'id_order_line'     => '1300435653833-A-2',
                'id_order_detail'   => '2'
            ),
            '[Save Lengow Order Line] Check if order is present in Lengow Order Line table'
        );
        $this->assertTableContain(
            'lengow_order_line',
            array(
                'id_order'          => '1',
                'id_order_line'     => '1300435653833-A-3',
                'id_order_detail'   => '3'
            ),
            '[Save Lengow Order Line] Check if order is present in Lengow Order Line table'
        );
    }

    /**
     * Test check if the products exist
     *
     * @test
     * @covers LengowImportOrder::getProducts
     */
    public function getProducts()
    {
        $this->chargeConfig();
        $this->chargeFixture();
        $fixture = new Fixture();
        $fixture->truncate('order_detail');
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/order_line_saved.json'
        );
        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(1, $result['order_new'], '[Get Products] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Get Products] nb order update');
        $this->assertEquals(0, $result['order_error'], '[Get Products] nb order error');
        $this->assertTableContain(
            'order_detail',
            array(
                'id_order_detail'       => '1',
                'id_order'              => '1',
                'product_id'            => '1',
                'product_name'          => 'NAME001',
                'product_reference'     => 'SIMPLESKU001',
                'product_quantity'      => '3',
                'product_price'         => '4.900000',
                'total_price_tax_incl'  => '14.700000'
            ),
            '[Get Products] Check if order is present in Orders Detail Prestashop table'
        );
        $this->assertTableContain(
            'order_detail',
            array(
                'id_order_detail'       => '2',
                'id_order'              => '1',
                'product_id'            => '2',
                'product_name'          => 'NAME002',
                'product_reference'     => 'SIMPLESKU002',
                'product_quantity'      => '2',
                'product_price'         => '5.900000',
                'total_price_tax_incl'  => '11.800000'
            ),
            '[Get Products] Check if order is present in Orders Detail Prestashop table'
        );
        $this->assertTableContain(
            'order_detail',
            array(
                'id_order_detail'       => '3',
                'id_order'              => '1',
                'product_id'            => '3',
                'product_name'          => 'NAME003',
                'product_reference'     => 'SIMPLESKU003',
                'product_quantity'      => '1',
                'product_price'         => '6.900000',
                'total_price_tax_incl'  => '6.900000'
            ),
            '[Get Products] Check if order is present in Orders Detail Prestashop table'
        );
    }

    /**
     * Test check if customer is correctly set
     *
     * @test
     * @covers LengowImportOrder::getCustomer
     */
    public function getCustomer()
    {
        $this->chargeConfig();
        $this->chargeFixture();
        $fixture = new Fixture();
        $fixture->truncate('customer');
        $fixture->truncate('customer_group');
        $fixture->truncate('address');
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/ImportOrder/customer.yml'
        );
        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/order_customer.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/ImportOrder/order_customer_2.json'
        );
        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(1, $result['order_new'], '[Get Customer] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Get Customer] nb order update');
        $this->assertEquals(0, $result['order_error'], '[Get Customer] nb order error');
        $this->assertTableNotContain(
            'customer',
            array('id_customer' => '2'),
            '[Get Customer] Check if order is not present in Customer Prestashop table'
        );
        $this->assertTableContain(
            'orders',
            array(
                'id_order'      => '1',
                'id_customer'   => '1'
            ),
            '[Get Customer] Check if order is present in Orders Prestashop table'
        );
        $this->assertTableContain(
            'lengow_orders',
            array(
                'id_order'          => '1',
                'marketplace_sku'   => '1300435653835-A',
                'customer_name'     => 'Yvette Alcalde'
            ),
            '[Get Customer] Check if order is present in Lengow Orders table'
        );

        $import2 = new LengowImport(array('log_output' => false));
        $result2 = $import2->exec();
        $this->assertEquals(1, $result2['order_new'], '[Get Customer] nb order new');
        $this->assertEquals(0, $result2['order_update'], '[Get Customer] nb order update');
        $this->assertEquals(0, $result2['order_error'], '[Get Customer] nb order error');
        $this->assertTableContain(
            'customer',
            array(
                'id_customer'   => '2',
                'email'         => 'generated-email+1300435653836-A@prestashop.unit',
                'firstname'     => 'pierre',
                'lastname'      => 'dupond'
            ),
            '[Get Customer] Check if order is present in Customer Prestashop table'
        );
        $this->assertTableContain(
            'orders',
            array(
                'id_order'      => '2',
                'id_customer'   => '2'
            ),
            '[Get Customer] Check if order is present in Orders Prestashop table'
        );
        $this->assertTableContain(
            'lengow_orders',
            array(
                'id_order'          => '2',
                'marketplace_sku'   => '1300435653836-A',
                'customer_name'     => 'Pierre Dupond'
            ),
            '[Get Customer] Check if order is present in Lengow Orders table'
        );
    }
}
