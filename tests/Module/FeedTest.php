<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use Currency;
use Context;
use Db;
use Module;
use Configuration;
use LengowCore;
use LengowExport;
use LengowExportException;
use Assert;

class FeedTest extends ModuleTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    /**
     * Test Module Load
     *
     * @before
     */
    public function beforeExport()
    {
        Configuration::set('LENGOW_CARRIER_DEFAULT', 1);

        Context::getContext()->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/attribute_product.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/before_feed.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/simple_product.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/variation_product.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/pack_product.yml');

        $this->assertEquals(1, Configuration::get('LENGOW_CARRIER_DEFAULT'));
        $this->assertTrue((boolean)Context::getContext()->currency);
    }

    /**
     * Test Module Load
     *
     * @test
     */
    public function load()
    {
        $module = Module::getInstanceByName('lengow');
        $this->assertTrue((boolean)$module, 'Load Lengow Module');
        $this->assertEquals($module->name, 'lengow');
    }

    /**
     * Test Module Load
     *
     * @test
     *
     */
    public function authorizedIp()
    {
        Configuration::set('LENGOW_AUTHORIZED_IP', '0.0.0.0');
        $this->assertTrue(!LengowCore::checkIP());

        Configuration::set('LENGOW_AUTHORIZED_IP', '127.0.0.1');
        $this->assertTrue(LengowCore::checkIP());
    }

    /**
     * Test Export Format Empty
     *
     * @test
     * @expectedException        LengowExportException
     * @expectedExceptionMessage Illegal export format
     */
    public function formatEmpty()
    {
        new LengowExport(array("format" => "mp3"));
    }

    /**
     * Test Export Empty Currency
     *
     * @test
     * @expectedException        LengowExportException
     * @expectedExceptionMessage Illegal Currency
     */
    public function currencyEmpty()
    {
        $context = Context::getContext();
        $context->currency = null;
        $export = new LengowExport();
        $export->exec();
    }

    /**
     * Test Export Empty Carrier
     *
     * @test
     * @expectedException        LengowExportException
     * @expectedExceptionMessage You must select a carrier in Lengow Export Tab
     */
    public function carrierEmpty()
    {
        Configuration::set('LENGOW_CARRIER_DEFAULT', '');
        $export = new LengowExport();
        $export->exec();
    }

    /**
     * Test Export Limit
     *
     * @test
     *
     */
    public function exportLimit()
    {
        $export = new LengowExport(array(
            "show_product_combination" => false,
            "limit" => 4
        ));
        $export->exec();
        $this->assertFileNbLine($export->getFileName(), 4, 'limit_4');
    }

    /**
     * Test Show Combination
     *
     * @test
     *
     */
    public function exportCombination()
    {
        $export = new LengowExport(array(
            "show_product_combination" => true,
            "product_ids" => array(10),
        ));
        $export->exec();
        $this->assertFileNbLine($export->getFileName(), 7, 'show_combination');
    }

    /**
     * Test Export Inactive Product
     *
     * @test
     *
     */
    public function exportInactiveProduct()
    {
        $export = new LengowExport(array(
            "show_inactive_product" => true
        ));
        $export->exec();
        $this->assertFileNbLine($export->getFileName(), 8, 'inactive_product');
    }



//    /**
//     * Test Export Format Empty
//     *
//     * @test
//     *
//     */
//    public function exportLimit()
//    {
//        $export = new LengowExport(array(
//            "fullmode" => true
//        ));
//        $export->exec();
//    }
}
