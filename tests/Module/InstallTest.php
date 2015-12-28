<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use Cache;
use Db;
use Module;
use Configuration;

class InstallTest extends ModuleTestCase
{

    /**
     *
     * Install module
     *
     * @after
     *
     */
    public function installAfter()
    {
        $module = Module::getInstanceByName('lengow');
        $this->assertTrue((boolean)$module, 'Load Lengow Module');

        //install module if uninstall
        if (!$module->isInstalled('lengow')) {
            $module = Module::getInstanceByName('lengow');
            $module->install();
        }
    }

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
     * Test updage process
     *
     * @test
     *
     */
    public function upgrade()
    {
        $module = Module::getInstanceByName('lengow');
        $this->assertTrue($module->update());
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
     * @test
     *
     */
    public function unInstall()
    {
        //test if version is correct
        $module = Module::getInstanceByName('lengow');

        //install module if uninstall
        if (!$module->isInstalled('lengow')) {
            $module = Module::getInstanceByName('lengow');
            $module->install();
        }

        $this->assertTrue((boolean)$module, 'Load Lengow Module');
        $this->assertTrue($module->uninstall());
    }
}
