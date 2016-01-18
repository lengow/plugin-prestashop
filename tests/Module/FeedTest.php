<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use Currency;
use Context;
use Db;
use Module;
use Configuration;
use LengowMain;
use LengowExport;
use LengowExportException;
use LengowFeed;
use Assert;
use Feature;

class FeedTest extends ModuleTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        parent::setUp();

        Configuration::updatevalue('LENGOW_CARRIER_DEFAULT', 1);
        Configuration::updatevalue('LENGOW_EXPORT_FORMAT', 'csv');
        Configuration::updatevalue('LENGOW_EXPORT_FULLNAME', 0);
        Configuration::updatevalue('LENGOW_EXPORT_FILE', 0);
        Configuration::updatevalue('LENGOW_EXPORT_SELECTION', 0);
        Context::getContext()->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

        //load module
        Module::getInstanceByName('lengow');

        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/attribute_product.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/features.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/before_feed.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/simple_product.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/variation_product.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/pack_product.yml');
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
        $this->assertTrue(!LengowMain::checkIP());

        Configuration::set('LENGOW_AUTHORIZED_IP', '127.0.0.1');
        $this->assertTrue(LengowMain::checkIP());
    }

    /**
     * Test Export Format Empty
     *
     * @test
     * @expectedException        LengowExportException
     * @expectedExceptionMessage Illegal export format
     * @covers LengowExport::setFormat
     */
    public function setFormat()
    {
        new LengowExport(array("format" => "mp3"));
    }

    /**
     * Test Export Empty Carrier
     *
     * @test
     * @expectedException        LengowExportException
     * @expectedExceptionMessage You must select a carrier in Lengow Export Tab
     * @covers LengowExport::setCarrier
     */
    public function setCarrier()
    {
        Configuration::set('LENGOW_CARRIER_DEFAULT', '');
        $export = new LengowExport();
        $export->exec();
    }

    /**
     * Test Export Empty Currency
     *
     * @test
     * @expectedException        LengowExportException
     * @expectedExceptionMessage Illegal Currency
     * @covers LengowExport::checkCurrency
     */
    public function checkCurrency()
    {
        $context = Context::getContext();
        $context->currency = null;
        $export = new LengowExport();
        $export->exec();
    }

    /**
     * Test Export Limit
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
            "export_variation" => true,
            "product_ids" => array(10),
        ));
        $export->exec();
        $this->assertFileNbLine($export->getFileName(), 8, 'show_combination');
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


    /**
     * Test Two Products
     *
     * @test
     *
     */
    public function exportProductIds()
    {
        $export = new LengowExport(array(
            "product_ids" => array(1,2),
        ));
        $export->exec();
        $this->assertFileNbLine($export->getFileName(), 2, 'two_product');
    }

    /**
     * Test Export All
     *
     * @test
     */
    public function exportAll()
    {
        $export = new LengowExport();
        $export->exec();
        $this->assertFileNbLine($export->getFileName(), 5, 'all');
    }

    /**
     * Test full title option
     *
     * @test
     */
    public function withFullTitle()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/variation_product.yml'
        );

        $export = new LengowExport(array(
            "export_variation" => true,
            "full_title" => true,
            "product_ids" => array(10),
        ));
        $export->exec();
        $this->assertFileValues($export->getFileName(), 10, array("NAME_PRODUCT" => "NAME010"));
        $this->assertFileValues($export->getFileName(), '10_11', array("NAME_PRODUCT" => "NAME010 - Pointure - 35"));
        $this->assertFileNbLine($export->getFileName(), 8, 'with_full_title');
    }

    /**
     * Test full title option
     *
     * @test
     */
    public function withoutFullTitle()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/variation_product.yml'
        );

        $export = new LengowExport(array(
            "export_variation" => true,
            "full_title" => false,
            "product_ids" => array(10),
        ));
        $export->exec();
        //$this->assertFileValues($export->getFileName(), 10, array("NAME" => "NAME010"));
        //$this->assertFileValues($export->getFileName(), '10_11', array("NAME" => "NAME010"));
        $this->assertFileNbLine($export->getFileName(), 8, 'without_full_title');
    }

    /**
     * Test count exported products without feature
     *
     * @test
     */
    public function countExportedProductInStock()
    {
        $export = new LengowExport(array(
            "out_stock" => true,
            "export_features" => false,
            "export_lengow_selection" => false,
        ));
        $this->assertEquals(5, $export->getTotalExportProduct());
    }

    /**
     * Test export max image 10
     *
     * @test
     */
    public function max10Images()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/max_image.yml'
        );
        $export = new LengowExport(array(
            "product_ids" => array(1),
        ));
        $export->exec();

        $this->assertFileNbLine($export->getFileName(), 1, 'max_image');
        $this->assertFileColumnNotContain(
            $export->getFileName(),
            array('IMAGE_PRODUCT_11'),
            'Export contain max 10 images'
        );
        $this->assertFileColumnContain(
            $export->getFileName(),
            array('IMAGE_PRODUCT_1', 'IMAGE_PRODUCT_2', 'IMAGE_PRODUCT_3', 'IMAGE_PRODUCT_4', 'IMAGE_PRODUCT_5'),
            'Export contain max 10 images'
        );
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
