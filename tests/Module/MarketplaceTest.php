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
}
