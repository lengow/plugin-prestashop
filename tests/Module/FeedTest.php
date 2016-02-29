<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use Currency;
use Context;
use Db;
use Module;
use Configuration;
use LengowMain;
use LengowExport;
use LengowException;
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
        Configuration::updatevalue('LENGOW_EXPORT_FORMAT', 'csv');
        Configuration::updatevalue('LENGOW_EXPORT_FILE_ENABLED', 0);
        Configuration::updatevalue('LENGOW_EXPORT_SELECTION_ENABLED', 0);

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
     * @expectedException        LengowException
     * @expectedExceptionMessage log.export.error_illegal_export_format
     * @covers LengowExport::setFormat
     */
    public function setFormat()
    {
        new LengowExport(array("format" => "mp3"));
    }

    /**
     * Test Export Limit
     * @test
     *
     */
    public function exportLimit()
    {
        $export = new LengowExport(array(
            "export_variation" => false,
            "limit" => 4,
            "log_output" => false
        ));
        $export->exec();
        $this->assertFileNbLine($export->getFileName(), 4, 'limit_4');
    }

    /**
     * Test Export Offset
     * @test
     *
     */
    public function exportOffset()
    {
        $export = new LengowExport(array(
            "export_variation" => true,
            "offset" => 2,
            "limit" => 4,
            "log_output" => false
        ));
        $export->exec();
        $this->assertFileValues($export->getFileName(), 10, array("NAME_PRODUCT" => "NAME010"));
        $this->assertFileValues($export->getFileName(), '10_11', array("NAME_PRODUCT" => "NAME010 - Pointure - 35"));
        $this->assertFileValues($export->getFileName(), '10_12', array("NAME_PRODUCT" => "NAME010 - Pointure - 36"));
        $this->assertFileValues($export->getFileName(), '10_13', array("NAME_PRODUCT" => "NAME010 - Pointure - 37"));
        $this->assertFileNbLine($export->getFileName(), 4, 'offset_1_limit_2');
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
            "log_output" => false
        ));
        $export->exec();
        $this->assertFileNbLine($export->getFileName(), 6, 'show_combination');
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
            "show_inactive_product" => true,
            "log_output" => false
        ));
        $export->exec();
        $this->assertFileNbLine($export->getFileName(), 6, 'inactive_product');
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
            "log_output" => false,
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
        $export = new LengowExport(array("log_output" => false));
        $export->exec();
        $this->assertFileNbLine($export->getFileName(), 4, 'all');
    }

    /**
     * Test full title option
     *
     * @test
     */
    public function fullTitle()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/variation_product.yml'
        );

        $export = new LengowExport(array(
            "export_variation" => true,
            "product_ids" => array(10),
            "log_output" => false
        ));
        $export->exec();
        $this->assertFileValues($export->getFileName(), 10, array("NAME_PRODUCT" => "NAME010"));
        $this->assertFileValues($export->getFileName(), '10_11', array("NAME_PRODUCT" => "NAME010 - Pointure - 35"));
        $this->assertFileNbLine($export->getFileName(), 6, 'with_full_title');
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
            "log_output" => false
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
            "log_output" => false
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
