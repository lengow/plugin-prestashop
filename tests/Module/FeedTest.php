<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use Db;
use Module;
use Configuration;
use LengowCore;
use LengowExport;
use LengowExportException;

class FeedTest extends ModuleTestCase
{


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        //define Transporteur
        Configuration::set('LENGOW_CARRIER_DEFAULT', 1);

    }


    /**
     * Test Module Load
     *
     * @test
     *
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
        new LengowExport();
    }

    /**
     * Test Export Format Empty
     *
     * @test
     */
    public function exportLimit()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_LENGOW_DIR_.'tests/Module/Fixtures/test.yml');
        $export = new LengowExport("csv", null, null, null, false);
        $export->exec();
    }
}
