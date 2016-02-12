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
use LengowExportException;
use LengowCarrier;
use LengowConnector;
use Assert;

class CarrierTest extends ModuleTestCase
{

    public function setUp()
    {
        parent::setUp();

        //load module
        Module::getInstanceByName('lengow');
    }

    /**
     * Test getListMarketplaceCarrierAPI
     *
     * @test
     * @covers LengowCarrier::getListMarketplaceCarrierAPI
     */
    public function getListMarketplaceCarrierAPI()
    {
        LengowConnector::$test_fixture_path =
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Connector/marketplaces.json';
        $carrierCollection = LengowCarrier::getListMarketplaceCarrierAPI();
        $testCarrier = array(
            'LAPOSTE',
            'LAPOSTE_RELAY',
            'CHRONOPOST',
            'CHRONOPOST_RELAY',
            'MONDIALRELAY',
            'MONDIALRELAY_RELAY',
            'GLS',
            'GLS_RELAY'
        );
        $this->assertEquals($testCarrier, $carrierCollection);
    }

    /**
     * Test insertCountryInMarketplace
     *
     * @test
     * @covers LengowCarrier::insertCountryInMarketplace
     */
    public function insertCountryInMarketplace()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Carrier/marketplace_carrier.yml'
        );

        LengowCarrier::insertCountryInMarketplace('WTFCARRIER', '8');

        $this->assertTableContain('lengow_marketplace_carrier', array(
            'marketplace_carrier_sku' => 'WTFCARRIER',
            'id_country' => '8'
        ));
    }

    /**
     * Test syncListMarketplace
     *
     * @test
     * @covers LengowCarrier::syncListMarketplace
     */
    public function syncListMarketplace()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Carrier/marketplace_carrier.yml'
        );
        LengowConnector::$test_fixture_path =
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Connector/marketplaces.json';
        LengowCarrier::syncListMarketplace();

        $defaultCountryId = Configuration::get('PS_COUNTRY_DEFAULT');
        $this->assertTableContain('lengow_marketplace_carrier', array(
            'marketplace_carrier_sku' => 'LAPOSTE',
            'id_country' => $defaultCountryId
        ));
        $this->assertTableContain('lengow_marketplace_carrier', array(
            'marketplace_carrier_sku' => 'LAPOSTE_RELAY',
            'id_country' => $defaultCountryId
        ));
        $this->assertTableContain('lengow_marketplace_carrier', array(
            'marketplace_carrier_sku' => 'MONDIALRELAY',
            'id_country' => $defaultCountryId
        ));
        $this->assertTableContain('lengow_marketplace_carrier', array(
            'marketplace_carrier_sku' => 'MONDIALRELAY_RELAY',
            'id_country' => $defaultCountryId
        ));
    }

    /**
     * Test getMarketplaceCarrier
     *
     * @test
     * @covers LengowCarrier::getMarketplaceCarrier
     */
    public function getMarketplaceCarrier()
    {
        $carrierName = LengowCarrier::getMarketplaceCarrier(1, 8);
        $this->assertEquals('LAPOSTE', $carrierName);
        $carrierName = LengowCarrier::getMarketplaceCarrier(2, 8);
        $this->assertEquals('LAPOSTE_RELAY', $carrierName);
        $carrierName = LengowCarrier::getMarketplaceCarrier(3, 8);
        $this->assertEquals(null, $carrierName);
    }
}
