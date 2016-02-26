<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use Cache;
use Db;
use Module;
use LengowInstall;
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
        $this->assertTrue($module->uninstall(), 'Uninstall Lengow Module');
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
        $this->assertEquals($module->name, 'lengow', 'Module name is lengow');
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
            Cache::store('Module::isInstalledlengow', false);
        }
        $this->assertTrue($module->install(), 'Module install successfully');
        $this->assertEquals($module->version, Configuration::get('LENGOW_VERSION'), 'Module name has correct version');
    }

    /**
     * Test dropTable
     * @test
     * @covers LengowInstallation::dropTable
     * @covers LengowInstallation::update
     */
    public function dropTable()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Install/lengow_table.yml');

        foreach (LengowInstall::$tables as $table) {
            $this->assertTableExist($table, 'Table '.$table.' exist');
        }
        LengowInstall::dropTable();
        foreach (LengowInstall::$tables as $table) {
            $this->assertTableNotExist($table, 'Table '.$table.' don\'t exist');
        }
        $module = Module::getInstanceByName('lengow');
        $module->isInstalled('lengow');
        $install = new LengowInstall($module);
        $install->update();
    }


    /**
     * Test Remove Files
     * @test
     * @covers LengowInstallation::removeFiles
     */
    public function removeFiles()
    {
        $filePath = _PS_MODULE_LENGOW_DIR_.'new_file.php';

        $fp = fopen($filePath, 'w');
        $this->assertTrue((bool)$fp);
        $this->assertTrue(file_exists($filePath));

        LengowInstall::removeFiles(array(
            'new_file.php',
        ));
        $this->assertFalse(file_exists($filePath));

        $directoryPath = _PS_MODULE_LENGOW_DIR_.'new_directory';

        $this->assertTrue((bool)mkdir($directoryPath, 0700));
        $fp = fopen($directoryPath.'/new_file.php', 'w');
        $this->assertTrue((bool)$fp);
        $this->assertTrue(file_exists($directoryPath));
        LengowInstall::removeFiles(array(
            'new_directory/',
        ));
    }
}
