<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

class HomeControllerTest extends ControllerTestCase
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    /**
     * Test Index Action
     * @test
     */

    public function index()
    {
        $url = '/admin-dev/' . self::$context->link->getAdminLink('AdminLengowHome');
        $response = self::$client->get($url);
        $this->assertEquals(200, $response->getStatusCode());
        //todo test more things
    }
}
