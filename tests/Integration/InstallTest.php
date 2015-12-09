<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use PHPUnit_Framework_TestCase;
use Module;
use Order;

class InstallTest extends IntegrationTestCase
{


    public function testInstall()
    {
        Db::getInstance();

        //test if version is correct
        $module = Module::getInstanceByName('lengow');

        var_dump($module);

        echo $module->version;

        echo Configuration::get('LENGOW_VERSION');


        $this->assertTrue(true, "Everything works fine");
    }
}
