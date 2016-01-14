<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use Currency;
use Context;
use Db;
use Module;
use Configuration;
use LengowExport;
use Assert;
use Feature;

class ExportTest extends ModuleTestCase
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
    }

    /**
     * Test getFileName
     *
     * @test
     * @covers LengowExport::getFileName
     */
    public function getFileName()
    {

        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/simple_product.yml');

        $export = new LengowExport(array("product_ids" => array(1)));
        $export->exec();
        $filename = $export->getFileName();
        $this->assertFileExists($filename);
        $this->assertTrue(strlen($filename) > 0);

        unlink($filename);
        $this->assertFileNotExists($filename);

        $export = new LengowExport(array("product_ids" => array(1)));
        $export->exec();
        $filename = $export->getFileName();
        $this->assertFileExists($filename);
    }


    /**
     * Test count all products
     *
     * @test
     * @covers LengowExport::getTotalProduct
     * @covers LengowExport::buildTotalQuery
     */
    public function getTotalProduct()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Export/count_total_product.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Export/count_total_product_2.yml');
        $export = new LengowExport(array(
            "export_variation" => false
        ));
        $this->assertEquals(3, $export->getTotalProduct());

        $export = new LengowExport(array(
            "export_variation" => true
        ));
        $this->assertEquals(10, $export->getTotalProduct());
    }


    /**
     * Test count exported products with variation
     *
     * @test
     * @covers LengowExport::getTotalExportProduct
     * @covers LengowExport::buildTotalQuery
     */
    public function getTotalExportProduct()
    {

        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Export/count_total_product.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Export/count_total_product_2.yml');

        $export = new LengowExport(array(
            "export_variation" => false,
            "selection" => false,
        ));
        $this->assertEquals(3, $export->getTotalExportProduct());

        $export = new LengowExport(array(
            "export_variation" => true,
            "selection" => false,
        ));
        $this->assertEquals(10, $export->getTotalExportProduct());

        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Export/exported_total_product.yml');

        $export = new LengowExport(array(
            "export_variation" => false,
            "selection" => true,
        ));
        $this->assertEquals(2, $export->getTotalExportProduct());

        $export = new LengowExport(array(
            "export_variation" => true,
            "selection" => true,
        ));
        $this->assertEquals(9, $export->getTotalExportProduct());
    }
}
