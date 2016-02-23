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
        Configuration::updatevalue('LENGOW_EXPORT_FILE_ENABLED', 0);
        Configuration::updatevalue('LENGOW_EXPORT_SELECTION_ENABLED', 0);

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

        $export = new LengowExport(array("product_ids" => array(1),"log_output" => false));
        $export->exec();
        $filename = $export->getFileName();
        $this->assertFileExists($filename);
        $this->assertTrue(strlen($filename) > 0);

        unlink($filename);
        $this->assertFileNotExists($filename);

        $export = new LengowExport(array("product_ids" => array(1), "log_output" => false));
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
            "export_variation" => false,
            "log_output" => false,
        ));
        $this->assertEquals(3, $export->getTotalProduct());

        $export = new LengowExport(array(
            "export_variation" => true,
            "log_output" => false,
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
            "log_output" => false,
        ));
        $this->assertEquals(2, $export->getTotalExportProduct());

        $export = new LengowExport(array(
            "export_variation" => true,
            "selection" => false,
            "log_output" => false,
        ));
        $this->assertEquals(8, $export->getTotalExportProduct());

        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Export/exported_total_product.yml');

        $export = new LengowExport(array(
            "export_variation" => false,
            "selection" => true,
            "log_output" => false,
        ));
        $this->assertEquals(1, $export->getTotalExportProduct());

        $export = new LengowExport(array(
            "export_variation" => true,
            "selection" => true,
            "log_output" => false,
        ));
        $this->assertEquals(7, $export->getTotalExportProduct());
    }

    /**
     * Test getFields
     * @test
     * @covers LengowExport::getFields
     */
    public function getFields()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Export/count_total_product.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Export/count_total_product_2.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/features.yml');

        $export = new LengowExport(array(
            "export_variation" => false,
            "selection" => false,
        ));
        $finalFields = array();
        foreach (LengowExport::$DEFAULT_FIELDS as $k => $v) {
            $finalFields[] = $k;
        }
        $finalFields = array_merge($finalFields, array(
            'Hauteur',
            'Largeur',
            'Profondeur',
            'Poids',
            'Compositions',
            'Styles',
            'Propriétés',
        ));

        $lengowGetFields = $this->invokeMethod($export, 'getFields');
        $this->assertEquals($lengowGetFields, $finalFields);
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
        $export = new LengowExport();
        $export->setFormat('mp3');
    }

//    /**
//     * Test Export Empty Carrier
//     *
//     * @test
//     * @expectedException        LengowExportException
//     * @expectedExceptionMessage You must select a carrier in Lengow Export Tab
//     * @covers LengowExport::setCarrier
//     */
//    public function setCarrier()
//    {
//        Configuration::set('LENGOW_CARRIER_DEFAULT', '');
//        $export = new LengowExport();
//        $export->setCarrier();
//    }

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
        $export = new LengowExport();
        Context::getContext()->currency = null;
        $export->checkCurrency();
    }

    /**
     * Test Export Empty Currency
     *
     * @test
     * @covers LengowExport::setAdditionalFields
     */
    public function setAdditionalFields()
    {
        require_once('Fixtures/Export/LengowExportOverride.php');

        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Export/count_total_product.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Export/count_total_product_2.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/no_features.yml');


        $export = new \LengowExportOverride(array(
            "export_variation" => false,
            "selection" => false,
        ));

        $finalFields = array();
        foreach (\LengowExportOverride::$DEFAULT_FIELDS as $k => $v) {
            $finalFields[] = $k;
        }
        $finalFields[] = 'test1';
        $finalFields[] = 'test2';
        $finalFields[] = 'test3';

        $lengowGetFields = $this->invokeMethod($export, 'getFields');
        $this->assertEquals($lengowGetFields, $finalFields);
    }
}
