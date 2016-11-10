<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use Currency;
use Context;
use Db;
use Module;
use LengowConfiguration;
use Assert;
use Feature;
use Cache;
use LengowTool;
use Tools;

class ToolTest extends ModuleTestCase
{
    protected $accountId = 155;

    protected $secretToken = '456465465146514651465';

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        parent::setUp();
        //load module
        Module::getInstanceByName('lengow');
    }

    /**
     * Test processLogin
     *
     * @test
     * @covers LengowTool::processLogin
     */
    public function processLogin()
    {
        LengowConfiguration::updatevalue('LENGOW_ACCOUNT_ID', $this->accountId, false, null, 1);
        LengowConfiguration::updatevalue('LENGOW_SECRET_TOKEN', $this->secretToken, false, null, 1);

        LengowConfiguration::updateGlobalValue('LENGOW_ACCESS_BLOCK_IP_1', '');
        LengowConfiguration::updateGlobalValue('LENGOW_ACCESS_BLOCK_IP_2', '');
        LengowConfiguration::updateGlobalValue('LENGOW_ACCESS_BLOCK_IP_3', '');

        $tool = new LengowTool();

        $this->assertTrue($tool->processLogin($this->accountId, $this->secretToken));
        $this->assertFalse($tool->processLogin('1564', $this->secretToken));
        $this->assertFalse($tool->processLogin($this->accountId, '1564165465'));
    }

    /**
     * Test checkIp
     *
     * @test
     * @covers LengowTool::checkIp
     */
    public function checkIp()
    {
        LengowConfiguration::updatevalue('LENGOW_ACCOUNT_ID', $this->accountId);
        LengowConfiguration::updatevalue('LENGOW_SECRET_TOKEN', $this->secretToken);

        LengowConfiguration::updateGlobalValue('LENGOW_ACCESS_BLOCK_IP_1', '["127.0.0.1"]');
        LengowConfiguration::updateGlobalValue('LENGOW_ACCESS_BLOCK_IP_2', '');
        LengowConfiguration::updateGlobalValue('LENGOW_ACCESS_BLOCK_IP_3', '');

        $tool = new LengowTool();
        $this->assertFalse($tool->processLogin('1564', $this->secretToken));
        $this->assertEquals('["127.0.0.1"]', LengowConfiguration::getGlobalValue('LENGOW_ACCESS_BLOCK_IP_2'));
        $this->assertEquals('', LengowConfiguration::getGlobalValue('LENGOW_ACCESS_BLOCK_IP_3'));

        $this->assertFalse($tool->processLogin('1564', $this->secretToken));
        $this->assertEquals('["127.0.0.1"]', LengowConfiguration::getGlobalValue('LENGOW_ACCESS_BLOCK_IP_2'));
        $this->assertEquals('["127.0.0.1"]', LengowConfiguration::getGlobalValue('LENGOW_ACCESS_BLOCK_IP_3'));


        LengowConfiguration::updateGlobalValue('LENGOW_ACCESS_BLOCK_IP_1', '');
        LengowConfiguration::updateGlobalValue('LENGOW_ACCESS_BLOCK_IP_2', '');
        LengowConfiguration::updateGlobalValue('LENGOW_ACCESS_BLOCK_IP_3', '');
        $this->assertFalse($tool->processLogin('1564', $this->secretToken));
        $this->assertEquals('["127.0.0.1"]', LengowConfiguration::getGlobalValue('LENGOW_ACCESS_BLOCK_IP_1'));
        $this->assertEquals('', LengowConfiguration::getGlobalValue('LENGOW_ACCESS_BLOCK_IP_2'));
        $this->assertEquals('', LengowConfiguration::getGlobalValue('LENGOW_ACCESS_BLOCK_IP_3'));
    }

    /**
     * Test checkBlockedIp
     *
     * @test
     * @covers LengowTool::checkBlockedIp
     */
    public function checkBlockedIp()
    {
        LengowConfiguration::updateGlobalValue('LENGOW_ACCESS_BLOCK_IP_1', json_encode(array('127.0.0.1')));
        LengowConfiguration::updateGlobalValue('LENGOW_ACCESS_BLOCK_IP_2', json_encode(array('127.0.0.1')));
        LengowConfiguration::updateGlobalValue('LENGOW_ACCESS_BLOCK_IP_3', json_encode(array('127.0.0.1')));
        $tool = new LengowTool();
        $this->assertTrue($tool->checkBlockedIp());
    }

    /**
     * Test unblockIp
     *
     * @test
     * @covers LengowTool::unblockIp
     */
    public function unblockIp()
    {
        LengowConfiguration::updatevalue('LENGOW_ACCOUNT_ID', $this->accountId);
        LengowConfiguration::updatevalue('LENGOW_SECRET_TOKEN', $this->secretToken);

        LengowConfiguration::updateGlobalValue('LENGOW_ACCESS_BLOCK_IP_1', json_encode(array('127.0.0.1')));
        LengowConfiguration::updateGlobalValue('LENGOW_ACCESS_BLOCK_IP_2', json_encode(array('127.0.0.1')));
        LengowConfiguration::updateGlobalValue('LENGOW_ACCESS_BLOCK_IP_3', '');

        $tool = new LengowTool();
        $this->assertTrue($tool->processLogin($this->accountId, $this->secretToken));
        $this->assertEquals('', LengowConfiguration::getGlobalValue('LENGOW_ACCESS_BLOCK_IP_1'));
        $this->assertEquals('', LengowConfiguration::getGlobalValue('LENGOW_ACCESS_BLOCK_IP_2'));
        $this->assertEquals('', LengowConfiguration::getGlobalValue('LENGOW_ACCESS_BLOCK_IP_3'));


        LengowConfiguration::updateGlobalValue(
            'LENGOW_ACCESS_BLOCK_IP_1',
            json_encode(array('127.0.0.1', '128.0.0.1'))
        );
        LengowConfiguration::updateGlobalValue(
            'LENGOW_ACCESS_BLOCK_IP_2',
            json_encode(array('127.0.0.1', '128.0.0.1'))
        );
        LengowConfiguration::updateGlobalValue('LENGOW_ACCESS_BLOCK_IP_3', '');

        $tool = new LengowTool();
        $this->assertTrue($tool->processLogin($this->accountId, $this->secretToken));
        $this->assertEquals(
            array('128.0.0.1'),
            (array)json_decode(LengowConfiguration::getGlobalValue('LENGOW_ACCESS_BLOCK_IP_1'))
        );
        $this->assertEquals(
            array('128.0.0.1'),
            (array)json_decode(LengowConfiguration::getGlobalValue('LENGOW_ACCESS_BLOCK_IP_2'))
        );
        $this->assertEquals('', LengowConfiguration::getGlobalValue('LENGOW_ACCESS_BLOCK_IP_3'));

    }
}
