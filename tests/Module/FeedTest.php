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
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/features.yml');
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
     * @test
     *
     */
    public function exportLimit()
    {
        error_reporting(E_ALL);
        ini_set("display_errors", 1);
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
        $this->assertFileNbLine($export->getFileName(), 7, 'inactive_product');
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
        $export = new LengowExport(array(
            "show_inactive_product" => true,
            "out_stock" => true,
            "show_product_combination" => true
        ));
        $export->exec();
        $this->assertFileNbLine($export->getFileName(), 15, 'all');
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
            "show_inactive_product" => true,
            "out_stock" => true,
            "show_product_combination" => true,
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
            "show_inactive_product" => true,
            "out_stock" => true,
            "show_product_combination" => true,
            "full_title" => false,
            "product_ids" => array(10),
        ));
        $export->exec();
        //$this->assertFileValues($export->getFileName(), 10, array("NAME" => "NAME010"));
        //$this->assertFileValues($export->getFileName(), '10_11', array("NAME" => "NAME010"));
        $this->assertFileNbLine($export->getFileName(), 8, 'without_full_title');
    }

    /**
     * Test export no field selected
     *
     * @test
     */
    public function noFieldSelected()
    {

        Configuration::set('lengow_export_fields', '');

        $fixture = new Fixture();
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/simple_product.yml'
        );

        $export = new LengowExport(array(
            "show_inactive_product" => true,
            "out_stock" => true,
            "product_ids" => array(1),
        ));
        $export->exec();

        $columns = array();
        foreach (LengowExport::$DEFAULT_FIELDS as $key => $value) {
            $columns[] = strtoupper($key);
        }

        $this->assertFileColumnEqual($export->getFileName(), $columns);
        $this->assertFileNbLine($export->getFileName(), 1, 'no_field_select');
    }


    /**
     * Test export options
     *
     * @test
     */
    public function exportFeature()
    {

        Configuration::set('LENGOW_EXPORT_FIELDS', '');
        Configuration::set('LENGOW_EXPORT_SELECT_FEATURES', '["1","2","3"]');

        $fixture = new Fixture();
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/simple_product.yml'
        );

        $export = new LengowExport(array(
            "show_inactive_product" => true,
            "out_stock" => true,
            "export_features" => true,
            "product_ids" => array(1),
        ));
        $export->exec();

        $columns = array();
        foreach (LengowExport::$DEFAULT_FIELDS as $key => $value) {
            $columns[] = strtoupper($key);
        }

        $columns = array_merge($columns, array('HAUTEUR', 'LARGEUR', 'PROFONDEUR'));


        $this->assertFileColumnEqual($export->getFileName(), $columns);
        $this->assertFileNbLine($export->getFileName(), 1, 'feature');
    }


    /**
     * Test export options
     *
     * @test
     */
    public function selectField()
    {
        Configuration::set('LENGOW_EXPORT_FIELDS', '["supplier_reference","manufacturer"]');

        $fixture = new Fixture();
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/simple_product.yml'
        );

        $export = new LengowExport(array(
            "show_inactive_product" => true,
            "out_stock" => true,
            "product_ids" => array(1),
        ));
        $export->exec();

        $this->assertFileColumnEqual($export->getFileName(), array('SUPPLIER_REFERENCE', 'MANUFACTURER'));
        $this->assertFileNbLine($export->getFileName(), 1, 'select_fields');
    }


    /**
     * Test count all products
     *
     * @test
     */
    public function countTotalProduct()
    {
        $export = new LengowExport(array(
            "export_features" => false,
        ));
        $this->assertEquals(8, $export->getTotalProduct());
    }

    /**
     * Test count all products with feature
     *
     * @test
     */
    public function countTotalProductWithFeature()
    {

        $export = new LengowExport(array(
            "export_features" => true,
        ));
        $this->assertEquals(14, $export->getTotalProduct());
    }

    /**
     * Test count exported products with feature
     *
     * @test
     */
    public function countExportedProductWithFeature()
    {
        $export = new LengowExport(array(
            "export_features" => true,
        ));
        $this->assertEquals(11, $export->getTotalExportProduct());
    }


    /**
     * Test count exported products without feature
     *
     * @test
     */
    public function countExportedProductWithoutFeature()
    {
        $export = new LengowExport(array(
            "export_features" => false,
        ));
        $this->assertEquals(5, $export->getTotalExportProduct());
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
        ));
        $this->assertEquals(5, $export->getTotalExportProduct());
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
