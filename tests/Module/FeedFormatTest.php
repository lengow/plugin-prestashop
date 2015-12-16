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

class FeedFormatTest extends ModuleTestCase
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
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/attribute_product.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/before_feed.yml');

        $this->assertEquals(1, Configuration::get('LENGOW_CARRIER_DEFAULT'));
        $this->assertTrue((boolean)Context::getContext()->currency);
    }


    /**
     * Test Module Load
     *
     * @before
     */
    public function load()
    {
        $module = Module::getInstanceByName('lengow');
        $this->assertTrue((boolean)$module, 'Load Lengow Module');
        $this->assertEquals($module->name, 'lengow');
    }

    /**
     * Test multiligne
     *
     * @test
     */
    public function multiLine()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/multi_line_product.yml');

        $export = new LengowExport(array(
            "show_inactive_product" => true,
            "out_stock" => true,
            "show_product_combination" => true
        ));
        $export->exec();
        $this->assertFileNbLine($export->getFileName(), 1, 'format_feed');
    }

    /**
     * Test quote character " | '
     *
     * @test
     */
    public function quote()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/quote_product.yml');

        $export = new LengowExport(array(
            "show_inactive_product" => true,
            "out_stock" => true,
            "show_product_combination" => true
        ));
        $export->exec();
        $this->assertFileValues($export->getFileName(), 1, array("NAME" => "THIS IS ' A   Test"));
        $this->assertFileValues($export->getFileName(), 1, array("DESCRIPTION" => "THIS IS ' A   Test"));
        $this->assertFileValues($export->getFileName(), 1, array("SHORT_DESCRIPTION" => "THIS IS ' A   Test"));
        $this->assertFileNbLine($export->getFileName(), 1, 'quote');
    }
}
