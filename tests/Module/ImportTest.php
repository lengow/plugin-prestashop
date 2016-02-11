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

class ImportTest extends ModuleTestCase
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
     */
    public function testCurrency()
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
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Order/euro_currency.yml'
        );
        LengowConnector::$test_fixture_path =
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Import/check_currency.json';
        $marketplaceFile =  _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Connector/marketplaces.json';
        LengowMarketplace::$MARKETPLACES = Tools::jsonDecode(file_get_contents($marketplaceFile));

        //$this->assertTableContain('lengow_logs_import', array('id' => '1',  'id_order_lengow' => '1'));
        $import = new LengowImport(array(
            'log_output' => false,
        ));
        $import->exec();
        $this->assertTableContain('lengow_logs_import', array(
            'id' => '1',
            'message' => 'currency GBP is not available in your shop',
            'id_order_lengow' => '1'
        ));
    }
}
