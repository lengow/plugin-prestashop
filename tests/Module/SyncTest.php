<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use Currency;
use Context;
use LengowConfiguration;
use Db;
use Module;
use Assert;
use Feature;
use Cache;
use Shop;
use Tools;

class SyncTest extends ModuleTestCase
{

    public function setUp()
    {
        parent::setUp();
        //load module
        Module::getInstanceByName('lengow');

        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/multi_shop.yml');
        LengowConfiguration::updatevalue('PS_MULTISHOP_FEATURE_ACTIVE', true);
        LengowConfiguration::updateValue('LENGOW_SHOP_TOKEN', '1f65ze4f5e6z4fze654fe', false, null, 1);
        LengowConfiguration::updateValue('LENGOW_SHOP_TOKEN', 'fg56ze4fgze654fze65fe', false, null, 2);
        Shop::setContext(Shop::CONTEXT_ALL);
    }

    /**
     * Test getSyncData
     *
     * @test
     * @covers LengowSync::getSyncData
     */
    public function getSyncData()
    {
        $sync = new \LengowSync();
        $data = $sync->getSyncData();

        $this->assertKeysExistInArray(array(
            'domain_name',
            'token',
            'email',
            'shops',
        ), $data);

        foreach ($data['shops'] as $shop) {
            switch ($shop['token']) {
                case '1f65ze4f5e6z4fze654fe':
                    $this->assertEquals($shop['token'], '1f65ze4f5e6z4fze654fe');
                    $this->assertEquals($shop['name'], 'prestashop.unit.test');
                    $this->assertEquals($shop['domain'], 'prestashop.unit.test');
                    break;
                case 'fg56ze4fgze654fze65fe':
                    $this->assertEquals($shop['token'], 'fg56ze4fgze654fze65fe');
                    $this->assertEquals($shop['name'], 'prestashop-two.unit.test');
                    $this->assertEquals($shop['domain'], 'prestashop-two.unit.test');
                    break;
            }
        }
    }

    /**
     * Test sync
     *
     * @test
     * @covers LengowSync::sync
     */
    public function sync()
    {

        //set correct data
        LengowConfiguration::updateValue('LENGOW_ACCOUNT_ID', '', false, null, 1);
        LengowConfiguration::updateValue('LENGOW_ACCESS_TOKEN', '', false, null, 1);
        LengowConfiguration::updateValue('LENGOW_SECRET_TOKEN', '', false, null, 1);
        LengowConfiguration::updateValue('LENGOW_SHOP_ACTIVE', '', false, null, 1);
        LengowConfiguration::updateValue('LENGOW_SHOP_TOKEN', '1f65ze4f5e6z4fze654fe', false, null, 1);

        $data = array(
            '1f65ze4f5e6z4fze654fe' => array(
                'account_id' => '160',
                'access_token' => 'fzg16re54g65er4g1er65',
                'secret_token' => 'ger65g4165er4g1r65e4g',
            )
        );

        $sync = new \LengowSync();
        $sync->sync($data);

        $this->assertEquals('160', LengowConfiguration::get('LENGOW_ACCOUNT_ID', false, null, 1));
        $this->assertEquals('fzg16re54g65er4g1er65', LengowConfiguration::get('LENGOW_ACCESS_TOKEN', false, null, 1));
        $this->assertEquals('ger65g4165er4g1r65e4g', LengowConfiguration::get('LENGOW_SECRET_TOKEN', false, null, 1));
        $this->assertEquals(true, LengowConfiguration::get('LENGOW_SHOP_ACTIVE', false, null, 1));


        //key empty
        LengowConfiguration::updateValue('LENGOW_ACCOUNT_ID', '', false, null, 1);
        LengowConfiguration::updateValue('LENGOW_ACCESS_TOKEN', '', false, null, 1);
        LengowConfiguration::updateValue('LENGOW_SECRET_TOKEN', '', false, null, 1);
        LengowConfiguration::updateValue('LENGOW_SHOP_ACTIVE', '', false, null, 1);
        LengowConfiguration::updateValue('LENGOW_SHOP_TOKEN', '1f65ze4f5e6z4fze654fe', false, null, 1);

        $data = array(
            '1f65ze4f5e6z4fze654fe' => array(
                'account_id' => '160',
                'access_token' => 'fzg16re54g65er4g1er65',
                'secret_token' => '',
            )
        );

        $sync = new \LengowSync();
        $sync->sync($data);

        $this->assertEquals('160', LengowConfiguration::get('LENGOW_ACCOUNT_ID', false, null, 1));
        $this->assertEquals('fzg16re54g65er4g1er65', LengowConfiguration::get('LENGOW_ACCESS_TOKEN', false, null, 1));
        $this->assertEquals('', LengowConfiguration::get('LENGOW_SECRET_TOKEN', false, null, 1));
        $this->assertEquals(false, LengowConfiguration::get('LENGOW_SHOP_ACTIVE', false, null, 1));

        //missing key
        LengowConfiguration::updateValue('LENGOW_ACCOUNT_ID', '', false, null, 1);
        LengowConfiguration::updateValue('LENGOW_ACCESS_TOKEN', '', false, null, 1);
        LengowConfiguration::updateValue('LENGOW_SECRET_TOKEN', '', false, null, 1);
        LengowConfiguration::updateValue('LENGOW_SHOP_ACTIVE', '', false, null, 1);
        LengowConfiguration::updateValue('LENGOW_SHOP_TOKEN', '1f65ze4f5e6z4fze654fe', false, null, 1);

        $data = array(
            '1f65ze4f5e6z4fze654fe' => array(
                'account_id' => '160',
                'access_token' => 'fzg16re54g65er4g1er65',
            )
        );

        $sync = new \LengowSync();
        $sync->sync($data);

        $this->assertEquals('160', LengowConfiguration::get('LENGOW_ACCOUNT_ID', false, null, 1));
        $this->assertEquals('fzg16re54g65er4g1er65', LengowConfiguration::get('LENGOW_ACCESS_TOKEN', false, null, 1));
        $this->assertEquals('', LengowConfiguration::get('LENGOW_SECRET_TOKEN', false, null, 1));
        $this->assertEquals(false, LengowConfiguration::get('LENGOW_SHOP_ACTIVE', false, null, 1));

    }
}
