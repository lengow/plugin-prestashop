<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use Cache;
use Db;
use Module;
use Configuration;

class InstallTest extends ModuleTestCase
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/multi_shop.yml');

        Configuration::updatevalue('PS_MULTISHOP_FEATURE_ACTIVE', true);
    }

    /**
     * Test uninstall lengow module
     *
     * @before
     * @test
     * @covers Lengow::uninstall
     * @covers LengowInstall::uninstall
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
     * @covers Lengow::install
     * @covers LengowInstall::install
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
            Cache::clean('Module::isInstalledlengow');
        }
        $this->assertTrue($module->install());
        $this->assertEquals($module->version, Configuration::get('LENGOW_VERSION'));
    }
}
