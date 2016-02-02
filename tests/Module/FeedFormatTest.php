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
use Assert;

class FeedFormatTest extends ModuleTestCase
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

        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/attribute_product.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/before_feed.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/features.yml');
    }

    /**
     * Test multi line
     *
     * @test
     */
    public function multiLine()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/multi_line_product.yml'
        );
        $export = new LengowExport(array(
            "out_stock" => true,
            "export_variation" => true,
        ));
        $export->exec();
        $this->assertFileNbLine($export->getFileName(), 1, 'multi_line');
    }

    /**
     * Test quote character " | '
     *
     * @test
     */
    public function quote()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Export/quote_product.yml'
        );

        $export = new LengowExport(array(
            "show_inactive_product" => true,
            "out_stock" => true,
            "export_variation" => true,
        ));
        $export->exec();
        $this->assertFileValues($export->getFileName(), 101, array("NAME_PRODUCT" => "THIS ' IS ' A   Test"));
        $this->assertFileValues($export->getFileName(), 101, array("DESCRIPTION" => "THIS ' IS ' A Test"));
        $this->assertFileValues($export->getFileName(), 101, array("DESCRIPTION_SHORT" => "THIS ' IS ' A Test"));
        $this->assertFileNbLine($export->getFileName(), 1, 'quote');
    }
}
