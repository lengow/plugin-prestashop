<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use GuzzleHttp\Client;
use Module;
use LengowLog;

class LogTest extends ModuleTestCase
{
    static protected $client;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$client = new Client(
            array(
                'base_uri' => 'http://'.CURRENT_DOMAIN,
                'allow_redirects' => false,
                'headers' => array('PHPUNIT_LENGOW_TEST' => 'toto'),
                'exceptions' => false,
            )
        );
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

    /**
     * Test log file is unauthorized
     *
     * @test
     */
    public function isLogUnauthorized()
    {
        $log = new LengowLog();
        $log->write('this is a test');

        $this->assertTrue(file_exists($log->getFileName()));

        $response =self::$client->get('modules/lengow/logs/logs-'.date('Y-m-d').'.txt');
        $this->assertEquals('403', $response->getStatusCode());
    }
}
