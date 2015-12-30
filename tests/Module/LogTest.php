<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use Module;
use LengowLog;

class LogTest extends ModuleTestCase
{
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
     * Test write log
     *
     * @test
     */
    public function write()
    {
        $log = new LengowLog();
        $log->write('this is a test');

        $lastLine = $this::readLastLine($log->getFileName());
        $date = substr($lastLine, 0, 26);
        $message = substr($lastLine, 30, strlen($lastLine)-30);
        $this->assertValidDatetime($date, 'Y-m-d:H:i:s.u');
        $this->assertEquals($message, 'this is a test');
    }
}
