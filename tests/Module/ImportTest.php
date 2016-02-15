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

    protected $account_id = 155;
    protected $secret_token = '456465465146514651465';
    protected $access_token = '457565421786654123231';

    public function setUp()
    {
        parent::setUp();

        //load module
        Module::getInstanceByName('lengow');
    }

    /**
     * Test Check if delivery address is present
     *
     * @test
     * @covers LengowImport::importOrders
     */
    public function checkDeliveryAddress()
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
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Import/check_delivery_address.json'
        );
        $marketplaceFile =  _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Connector/marketplaces.json';
        LengowMarketplace::$MARKETPLACES = Tools::jsonDecode(file_get_contents($marketplaceFile));

        $import = new LengowImport(array('log_output' => false));
        $result = $import->exec();
        $this->assertEquals(0, $result['order_new']);
        $this->assertEquals(0, $result['order_update']);
        $this->assertEquals(0, $result['order_error']);
    }

    /**
     * Test credentials data for a shop
     *
     * @test
     * @covers LengowImport::checkCredentials
     */
    public function checkCredentials()
    {
        LengowConfiguration::updateValue('LENGOW_ACCOUNT_ID', $this->account_id, false, null, 1);
        LengowConfiguration::updateValue('LENGOW_SECRET_TOKEN', $this->secret_token, false, null, 1);
        LengowConfiguration::updateValue('LENGOW_ACCESS_TOKEN', $this->access_token, false, null, 1);

        $import = new LengowImport();
        $this->assertTrue(
            $this->invokeMethod($import, 'checkCredentials', array(1, 'first_shop')),
            'Check credentials OK'
        );
        $this->assertEquals(
            'Account ID 155 is already used by shop first_shop (1)',
            $this->invokeMethod($import, 'checkCredentials', array(1, 'second_shop')),
            'Account ID is already used by a other shop'
        );
        $this->assertEquals(
            'ID account, access token or secret is empty in store 2',
            $this->invokeMethod($import, 'checkCredentials', array(2, 'second_shop')),
            'Credentials are empty'
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
        $marketplaceFile =  _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Connector/marketplaces.json';
        LengowMarketplace::$MARKETPLACES = Tools::jsonDecode(file_get_contents($marketplaceFile));

        $marketplace = LengowMain::getMarketplaceSingleton('galeries_lafayette', 1);

        $this->assertFalse(LengowImport::checkState(null, $marketplace), 'Order state is empty');
        $this->assertFalse(LengowImport::checkState('STAGING', $marketplace), 'Order state isn\'t authorized ');
        $this->assertTrue(LengowImport::checkState('WAITING_DEBIT', $marketplace), 'Order state is authorized');
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
        $this->assertTrue(LengowImport::isInProcess(), 'Import is already in process');

        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_IN_PROGRESS', ($now - 50));
        $this->assertTrue(LengowImport::isInProcess(), 'Import is already in process - 50s');

        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_IN_PROGRESS', ($now - 70));
        $this->assertFalse(LengowImport::isInProcess(), 'Import is already in process - 70s');

        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_IN_PROGRESS', -1);
        $this->assertFalse(LengowImport::isInProcess(), 'No import in process');
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
        $this->assertEquals(60, LengowImport::restTimeToImport(), 'Rest time 60s');

        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_IN_PROGRESS', ($now - 40));
        $this->assertEquals(20, LengowImport::restTimeToImport(), 'Rest time 20s for next import');

        LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_IN_PROGRESS', -1);
        $this->assertFalse(LengowImport::isInProcess(), 'Rest time 00s  for next import');
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
            'Setting LENGOW_IMPORT_IN_PROGRESS is completed'
        );
        $this->assertTrue(LengowImport::$processing, 'Processing attribute is true');
        $this->assertEquals(60, LengowImport::restTimeToImport(), 'Rest time 60s for next import');
        $this->assertTrue(LengowImport::isInProcess(), 'Import is already in process');
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
            'Setting LENGOW_IMPORT_IN_PROGRESS is completed'
        );
        $this->assertFalse(LengowImport::$processing, 'Processing attribute is false');
        $this->assertFalse(LengowImport::restTimeToImport(), 'Rest time 00s for next import');
        $this->assertFalse(LengowImport::isInProcess(), 'Import is finished');
    }
}
