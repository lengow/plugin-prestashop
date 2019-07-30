<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use GuzzleHttp\Client;
use Currency;
use Context;
use Db;
use Module;
use Configuration;
use LengowConfiguration;
use LengowMain;
use LengowImport;
use Tools;
use LengowMarketplace;
use LengowOrder;
use LengowConnector;
use Assert;

class ImportTest extends ModuleTestCase
{

    protected $accountId = 155;

    protected $accessToken = '457565421786654123231';

    protected $secretToken = '456465465146514651465';


    public function setUp()
    {
        parent::setUp();

        // load module
        Module::getInstanceByName('lengow');
    }

    public function chargeFixture()
    {
        $fixture = new Fixture();
        $fixture->truncate('orders');
        $fixture->truncate('order_carrier');
        $fixture->truncate('lengow_logs_import');
        $fixture->truncate('lengow_orders');
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/euro_currency.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/simple_product.yml');
    }

    public function chargeConfig()
    {
        Configuration::set('LENGOW_SHOP_ACTIVE', true, null, 1);
        Configuration::set('LENGOW_ACCOUNT_ID', 'nothing');
        Configuration::set('LENGOW_ACCESS_TOKEN', 'nothing');
        Configuration::set('LENGOW_SECRET_TOKEN', 'nothing');
    }

    /**
     * Test if orders are imported
     *
     * @test
     * @covers LengowImport::importOrders
     */
    public function importOrders()
    {
        $this->chargeConfig();
        $this->chargeFixture();
        LengowConnector::$testFixturePath = array(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Import/import_orders.json',
        );
        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(3, $result['order_new'], '[Import Orders] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Import Orders] nb order update');
        $this->assertEquals(0, $result['order_error'], '[Import Orders] nb order error');
        $this->assertTableNotContain(
            'lengow_orders',
            array('marketplace_sku' => '1300435653830-A'),
            '[Import Orders] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'lengow_orders',
            array(
                'id_order' => '1',
                'marketplace_sku' => '1300435653831-A',
                'order_process_state' => '1',
                'delivery_address_id' => '7528',
            ),
            '[Import Orders] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'lengow_orders',
            array(
                'id_order' => '2',
                'marketplace_sku' => '1300435653832-A',
                'tracking' => '8D00432154798',
                'order_process_state' => '2',
                'delivery_address_id' => '7530',
            ),
            '[Import Orders] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'lengow_orders',
            array(
                'id_order' => '3',
                'marketplace_sku' => '1300435653832-A',
                'tracking' => 'CK00879241952',
                'order_process_state' => '2',
                'delivery_address_id' => '7531',
            ),
            '[Import Orders] Check if order is present in Lengow Orders table'
        );
        $this->assertTableNotContain(
            'lengow_orders',
            array('marketplace_sku' => '1300435653833-A'),
            '[Import Orders] Check if order is present in Lengow Orders table'
        );
        $this->assertTableContain(
            'stock_available',
            array(
                'id_product' => '1',
                'quantity' => '8',
            ),
            '[Import Orders] Check if the stock is decremented'
        );
        $this->assertTableContain(
            'stock_available',
            array(
                'id_product' => '2',
                'quantity' => '7',
            ),
            '[Import Orders] Check if the stock is decremented'
        );
        $this->assertTableContain(
            'orders',
            array(
                'id_order' => '1',
                'shipping_number' => '',
                'current_state' => '2',
            ),
            '[Import Orders] Check if order is present in Orders Prestashop table'
        );
        $this->assertTableContain(
            'orders',
            array(
                'id_order' => '2',
                'shipping_number' => '8D00432154798',
                'current_state' => '4',
            ),
            '[Import Orders] Check if order is present in Orders Prestashop table'
        );
        $this->assertTableContain(
            'orders',
            array(
                'id_order' => '3',
                'shipping_number' => 'CK00879241952',
                'current_state' => '4',
            ),
            '[Import Orders] Check if order is present in Orders Prestashop table'
        );
        $this->assertTableContain(
            'order_carrier',
            array(
                'id_order' => '2',
                'tracking_number' => '8D00432154798',
            ),
            '[Import Orders] Check if tracking number is present in Order Carrier table'
        );
        $this->assertTableContain(
            'order_carrier',
            array(
                'id_order' => '3',
                'tracking_number' => 'CK00879241952',
            ),
            '[Import Orders] Check if tracking number is present in Order Carrier table'
        );
    }

    /**
     * Test Check if import is allready in progress
     *
     * @test
     * @covers LengowImport::importOrders
     */
    public function importIsInProgress()
    {
        $this->chargeConfig();
        $this->chargeFixture();

        $now = time();
        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_IN_PROGRESS', $now - 10);

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(0, $result['order_new'], '[Import Is In Progress] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Import Is In Progress] nb order update');
        $this->assertEquals(0, $result['order_error'], '[Import Is In Progress] nb order error');
        $this->assertEquals(
            'lengow_log.error.import_in_progress',
            $result['error'][0],
            '[Import Is In Progress] Generate an error'
        );
        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_IN_PROGRESS', -1);
    }

    /**
     * Test Check no order for import
     *
     * @test
     * @covers LengowImport::importOrders
     */
    public function noOrderForImport()
    {
        $this->chargeConfig();
        $this->chargeFixture();
        LengowConnector::$testFixturePath = array(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Import/no_orders.json',
        );

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(0, $result['order_new'], '[No Order For Import] nb order new');
        $this->assertEquals(0, $result['order_update'], '[No Order For Import] nb order update');
        $this->assertEquals(0, $result['order_error'], '[No Order For import] nb order error');
        $this->assertTableEmpty('lengow_orders', '[No Order For import] Check if Lengow Orders table is empty');
        $this->assertTableEmpty('orders', '[No Order For import] Check if Prestashop Orders table is empty');
    }

    /**
     * Test Check if the shop is inactive
     *
     * @test
     * @covers LengowImport::importOrders
     */
    public function noShopActive()
    {
        $this->chargeConfig();
        $this->chargeFixture();

        Configuration::set('LENGOW_SHOP_ACTIVE', false, null, 1);

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(0, $result['order_new'], '[No Shop Active] nb order new');
        $this->assertEquals(0, $result['order_update'], '[No Shop Active] nb order update');
        $this->assertEquals(0, $result['order_error'], '[No Shop Active] nb order error');
        $this->assertTableEmpty('lengow_orders', '[No Shop Active] Check if Lengow Orders table is empty');
        $this->assertTableEmpty('orders', '[No Shop Active] Check if Prestashop Orders table is empty');
    }

    /**
     * Test Check if the shop credentials are corrects
     *
     * @test
     * @covers LengowImport::importOrders
     */
    public function noCredentialsForShop()
    {
        $this->chargeFixture();

        Configuration::set('LENGOW_SHOP_ACTIVE', true, null, 1);
        Configuration::set('LENGOW_SHOP_ACTIVE', true, null, 2);
        Configuration::set('LENGOW_ACCOUNT_ID', '');
        Configuration::set('LENGOW_ACCESS_TOKEN', '');
        Configuration::set('LENGOW_SECRET_TOKEN', '');

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(0, $result['order_new'], '[No Credentials For Shop] nb order new');
        $this->assertEquals(0, $result['order_update'], '[No Credentials For Shop] nb order update');
        $this->assertEquals(0, $result['order_error'], '[No Credentials For Shop] nb order error');
        $this->assertEquals(
            'lengow_log.error.account_id_empty[name_shop==prestashop.unit.test|id_shop==1]',
            $result['error'][1],
            '[No Credentials For Shop] Generate an error'
        );
        $this->assertEquals(
            'lengow_log.error.account_id_empty[name_shop==prestashop-two.unit.test|id_shop==2]',
            $result['error'][2],
            '[No Credentials For Shop] Generate an error'
        );
    }

    /**
     * Test Check if delivery address is present
     *
     * @test
     * @covers LengowImport::importOrders
     */
    public function checkDeliveryAddress()
    {
        $this->chargeConfig();
        $this->chargeFixture();
        LengowConnector::$testFixturePath = array(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Import/check_delivery_address.json',
        );

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(0, $result['order_new'], '[Check Delivery Address] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Check Delivery Address] nb order update');
        $this->assertEquals(0, $result['order_error'], '[Check Delivery Address] nb order error');
        $this->assertTableNotContain(
            'lengow_orders',
            array('marketplace_sku' => '1300435653833-A'),
            '[Check Delivery Address] Check if order isn\'t present in Lengow Orders table'
        );
    }

    /**
     * Test Check if package data is present
     *
     * @test
     * @covers LengowImport::importOrders
     */
    public function checkPackageData()
    {
        $this->chargeConfig();
        $this->chargeFixture();
        LengowConnector::$testFixturePath = array(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Import/check_package_data.json',
        );

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(0, $result['order_new'], '[Check Package Data] nb order new');
        $this->assertEquals(0, $result['order_update'], '[Check Package Data] nb order update');
        $this->assertEquals(0, $result['order_error'], '[Check Package Data] nb order error');
        $this->assertTableNotContain(
            'lengow_orders',
            array('marketplace_sku' => '1300435653833-A'),
            '[Check Package Data] Check if order isn\'t present in Lengow Orders table'
        );
    }

    /**
     * Test credentials data for a shop
     *
     * @test
     * @covers LengowImport::checkCredentials
     */
    public function checkCredentials()
    {
        LengowConfiguration::updateValue('LENGOW_ACCOUNT_ID', $this->accountId);
        LengowConfiguration::updateValue('LENGOW_SECRET_TOKEN', $this->secretToken);
        LengowConfiguration::updateValue('LENGOW_ACCESS_TOKEN', $this->accessToken);

        $import = new LengowImport();
        $this->assertTrue(
            $this->invokeMethod($import, 'checkCredentials', array(1, 'first_shop')),
            '[Check Credentials] Check credentials OK'
        );
        $this->assertEquals(
            'lengow_log.error.account_id_already_used[account_id==155|name_shop==first_shop|id_shop==1]',
            $this->invokeMethod($import, 'checkCredentials', array(1, 'second_shop')),
            '[Check Credentials] Account ID is already used by a other shop'
        );
        $this->assertEquals(
            'lengow_log.error.account_id_empty[name_shop==second_shop|id_shop==2]',
            $this->invokeMethod($import, 'checkCredentials', array(2, 'second_shop')),
            '[Check Credentials] Credentials are empty'
        );
    }

    /**
     * Test lengow states is authorized
     *
     * @test
     * @covers LengowImport::checkState
     */
    public function checkState()
    {
        $marketplace = LengowMain::getMarketplaceSingleton('galeries_lafayette');

        $this->assertFalse(LengowImport::checkState(null, $marketplace), '[Check State] Order state is empty');
        $this->assertFalse(
            LengowImport::checkState('STAGING', $marketplace),
            '[Check State] Order state isn\'t authorized '
        );
        $this->assertTrue(
            LengowImport::checkState('WAITING_DEBIT', $marketplace),
            '[Check State] Order state is authorized'
        );
    }

    /**
     * Test import is already in process
     *
     * @test
     * @covers LengowImport::isInProcess
     */
    public function isInProcess()
    {
        $now = time();
        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_IN_PROGRESS', $now);
        $this->assertTrue(LengowImport::isInProcess(), '[Is In Process] Import is already in process');

        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_IN_PROGRESS', ($now - 50));
        $this->assertTrue(LengowImport::isInProcess(), '[Is In Process] Import is already in process - 50s');

        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_IN_PROGRESS', ($now - 70));
        $this->assertFalse(LengowImport::isInProcess(), '[Is In Process] Import is already in process - 70s');

        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_IN_PROGRESS', -1);
        $this->assertFalse(LengowImport::isInProcess(), '[Is In Process] No import in process');
    }

    /**
     * Test get rest time to make a re-import
     *
     * @test
     * @covers LengowImport::restTimeToImport
     */
    public function restTimeToImport()
    {
        $now = time();
        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_IN_PROGRESS', $now);
        $this->assertEquals(60, LengowImport::restTimeToImport(), '[Rest Time To Import] Rest time 60s');

        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_IN_PROGRESS', ($now - 40));
        $this->assertEquals(
            20,
            LengowImport::restTimeToImport(),
            '[Rest Time To Import] Rest time 20s for next import'
        );

        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_IN_PROGRESS', -1);
        $this->assertFalse(LengowImport::isInProcess(), '[Rest Time To Import] Rest time 00s for next import');
    }

    /**
     * Test set import to "in process" state
     *
     * @test
     * @covers LengowImport::setInProcess
     */
    public function setInProcess()
    {
        LengowImport::setInProcess();
        $this->assertEquals(
            time(),
            LengowConfiguration::getGlobalValue('LENGOW_IMPORT_IN_PROGRESS'),
            '[Set In Process] Setting LENGOW_IMPORT_IN_PROGRESS is completed'
        );
        $this->assertTrue(LengowImport::$processing, '[Set In Process] Processing attribute is true');
        $this->assertEquals(60, LengowImport::restTimeToImport(), '[Set In Process] Rest time 60s for next import');
        $this->assertTrue(LengowImport::isInProcess(), '[Set In Process] Import is already in process');
    }

    /**
     * Test import to finished
     *
     * @test
     * @covers LengowImport::setEnd
     */
    public function setEnd()
    {
        LengowImport::setEnd();
        $this->assertEquals(
            -1,
            LengowConfiguration::getGlobalValue('LENGOW_IMPORT_IN_PROGRESS'),
            '[Set End] Setting LENGOW_IMPORT_IN_PROGRESS is completed'
        );
        $this->assertFalse(LengowImport::$processing, '[Set End] Processing attribute is false');
        $this->assertFalse(LengowImport::restTimeToImport(), '[Set End] Rest time 00s for next import');
        $this->assertFalse(LengowImport::isInProcess(), '[Set End] Import is finished');
    }
}
