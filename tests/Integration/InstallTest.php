<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;
use PHPUnit_Framework_TestCase;
use Module;
use Configuration;

class InstallTest extends PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        require_once(_PS_CONFIG_DIR_ . '/config.inc.php');
    }

    /**
     *
     * @test
     *
     */
    public function Install()
    {
        //test if version is correct
        $module = Module::getInstanceByName('lengow');

        $this->assertTrue($module->install());
        $this->assertEquals($module->version, Configuration::get('LENGOW_VERSION'));
    }

    /**
     *
     * @test
     *
     */
    public function unInstall()
    {
        //test if version is correct
        $module = Module::getInstanceByName('lengow');
        $this->assertTrue($module->uninstall());
        $module->install();

        //$this->assertEquals($module->version, Configuration::get('LENGOW_VERSION'));
    }

}
