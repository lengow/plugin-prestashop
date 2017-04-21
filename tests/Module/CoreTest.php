<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use GuzzleHttp\Client;
use Currency;
use Context;
use Db;
use Module;
use Configuration;
use LengowMain;
use LengowConfiguration;
use LengowException;
use Assert;

class CoreTest extends ModuleTestCase
{
    static protected $client;
    protected $module;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$client = new Client(
            array(
                'base_uri' => 'http://' . CURRENT_DOMAIN,
                'allow_redirects' => false,
                'headers' => array('PHPUNIT_LENGOW_TEST' => 'toto')
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
        $this->module = Module::getInstanceByName('lengow');
        $this->assertTrue((boolean)$this->module, 'Lengow Module is loaded');
        $this->assertEquals($this->module->name, 'lengow', 'Lengow Module is name "lengow"');
    }

    /**
     * Test getToken Values
     *
     * @test
     */
    public function getToken()
    {
        $shopId = 1;

        //set empty token
        Configuration::updateValue('LENGOW_SHOP_TOKEN', '', null, null, $shopId);
        $token = Configuration::get('LENGOW_SHOP_TOKEN', null, null, $shopId);
        $this->assertTrue(strlen($token) == '', 'token is empty');

        LengowMain::getToken($shopId);
        $token = Configuration::get('LENGOW_SHOP_TOKEN', null, null, $shopId);
        $this->assertTrue(strlen($token) > 0, 'token is set with non empty value');
        $this->assertTrue(strlen($token) == 32, 'token is equal to 32');

        LengowMain::getToken($shopId);
        $this->assertEquals($token, LengowMain::getToken($shopId), 'token is not update when already set');
    }

    /**
     * Test checkExportAccess
     *
     * @test
     */
    public function checkExportAccess()
    {
        $shopId = 1;

        Configuration::updatevalue('LENGOW_EXPORT_FORMAT', 'csv');

        LengowConfiguration::updateGlobalValue('LENGOW_AUTHORIZED_IP', '');
        $exportUrl = LengowMain::getExportUrl($shopId);
        $response = self::$client->get($exportUrl);
        $body = $response->getBody()->getContents();
        $this->assertRegExp(
            '/\[Export\] ##/',
            substr($body, 0, 100),
            'Access Authorized'
        );

        $exportUrl = preg_replace('/token=[a-z0-9]*/', '', $exportUrl);
        $response = self::$client->get($exportUrl);
        $body = $response->getBody()->getContents();
        $this->assertTrue(
            (bool)preg_match('/^Unauthorized\ access\ for IP/', substr($body, 0, 120)),
            'Access Unauthorized'
        );

        LengowConfiguration::updateGlobalValue('LENGOW_AUTHORIZED_IP', '127.0.0.1');
        $response = self::$client->get($exportUrl);
        $body = $response->getBody()->getContents();
        $this->assertTrue(
            (bool)preg_match('/\[Export\] ##/', substr($body, 0, 50)),
            'Access Authorized'
        );
    }
}
