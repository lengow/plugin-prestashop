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
     * Test getListMarketplaceCarrier
     *
     * @test
     */
    public function getListMarketplaceCarrier()
    {
        LengowConnector::$test_fixture_path =
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Connector/marketplaces.json';
        $carrierCollection = LengowCarrier::getListMarketplaceCarrier();
        $testCarrier = array(
            'LAPOSTE',
            'CHRONOPOST',
            'MONDIALRELAY',
            'GLS'
        );
        $this->assertEquals($testCarrier, $carrierCollection);
    }
}
