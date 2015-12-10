<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use PHPUnit_Framework_TestCase;
use Module;

class LogTest extends PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        require_once _PS_CONFIG_DIR_ . 'config.inc.php';
    }

    /**
     * Test write lengow log
     *
     * @test
     *
     */
    public function write()
    {
        $lengow = Module::getInstanceByName('lengow');


        $lengowLog = new \LengowLog();
        $lengowLog->write('toto');

        $this->assertTrue(true);
    }
}

