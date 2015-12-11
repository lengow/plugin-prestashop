<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;


class HomeControllerTest extends IntegrationTestCase
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

    }

    protected function setUp()
    {
        list($cookieContent, $cookieName) = parent::loadCookie();

        $cookie = new SetCookie();
        $cookie->setName($cookieName);
        $cookie->setValue($cookieContent);
        $cookie->setDomain(CURRENT_DOMAIN);
        $cookie->setPath('/');
        $cookie->setMaxAge('541654654');
        $this->assertTrue($cookie->validate());

        $this->adminCookie = new CookieJar();  // new jar instance
        $this->adminCookie->setCookie($cookie);

    }

    public function test()
    {
        $client = new Client();
        $response = $client->request('GET', 'http://'.CURRENT_DOMAIN.'/admin-dev/', [
            'cookies' => $this->adminCookie
        ]);

        //$this->assertEquals(200, $response->getStatusCode());

        echo $response->getBody();


    }


}
