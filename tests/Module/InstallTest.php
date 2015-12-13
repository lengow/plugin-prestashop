<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use Db;
use Module;
use Configuration;

class InstallTest extends ModuleTestCase
{

    /**
     * Test can load lengow module
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
     * Test install lengow module
     *
     * @test
     *
     */
    public function install()
    {
        $module = Module::getInstanceByName('lengow');
        $this->assertTrue((boolean)$module, 'Load Lengow Module');

        //desinstall module if install
        if ($module->isInstalled('lengow')) {
            $module = Module::getInstanceByName('lengow');
            $module->uninstall();
        }

        $this->assertTrue($module->install());
        $this->assertEquals($module->version, Configuration::get('LENGOW_VERSION'));
    }

    /**
     * Test uninstall lengow module
     *
     * @depends install
     * @test
     *
     */
    public function unInstall()
    {
        //test if version is correct
        $module = Module::getInstanceByName('lengow');

        $this->assertTrue((boolean)$module, 'Load Lengow Module');
        $this->assertTrue($module->uninstall());
    }

}
