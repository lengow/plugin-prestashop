<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use PHPUnit_Framework_TestCase;
use Employee;
use Blowfish;
use Rijndael;
use Configuration;
use Module;
use Context;
use Link;

class ControllerTestCase extends PHPUnit_Framework_TestCase
{
    static private $cookie;
    static protected $client;
    static protected $context;
    static protected $employee;

    public static function setUpBeforeClass()
    {
        require_once(_PS_CONFIG_DIR_ . '/config.inc.php');

        self::loadCookie();
        self::loadModule();

        self::$context = Context::getContext();
        self::$context->employee = self::$employee;
        self::$context->link = new Link();

    }

    /**
     * Load Cookie and guzzle client to handle admin connection
     *
     */
    public static function loadCookie()
    {
        self::$employee = new Employee();
        self::$employee->getByEmail("pub@prestashop.com");
        $content = array(
            'id_employee' => self::$employee->id,
            'email' => self::$employee->email,
            'profile' => self::$employee->id_profile,
            'passwd' => self::$employee->passwd,
            'remote_addr' => ip2long('127.0.0.1'),
        );
        $cookieContent = '';
        foreach ($content as $key => $value) {
            $cookieContent .= $key . '|' . $value . '¤';
        }
        $cookieContent .= 'checksum|' . crc32(_COOKIE_IV_ . $cookieContent);
        $cookieName = 'PrestaShop-' . md5(_PS_VERSION_ . 'psAdmin' . CURRENT_DOMAIN);
        if (!Configuration::get('PS_CIPHER_ALGORITHM') || !defined('_RIJNDAEL_KEY_')) {
            $cipherTool = new Blowfish(_COOKIE_KEY_, _COOKIE_IV_);
        } else {
            $cipherTool = new Rijndael(_RIJNDAEL_KEY_, _RIJNDAEL_IV_);
        }
        $cookieContent = rawurlencode($cipherTool->encrypt($cookieContent));

        $cookie = new SetCookie();
        $cookie->setName($cookieName);
        $cookie->setValue($cookieContent);
        $cookie->setDomain(CURRENT_DOMAIN);
        $cookie->setPath('/');
        $cookie->setExpires(time() + 1 * 3600);
        $cookie->setSecure(false);

        self::$cookie = new CookieJar();  // new jar instance
        self::$cookie->setCookie($cookie);
        self::$client = new Client(
            array(
                'base_uri' => 'http://' . CURRENT_DOMAIN,
                'cookies' => self::$cookie,
                'allow_redirects' => false,
            )
        );
    }

    public static function loadModule()
    {
        if (!Module::isInstalled('lengow')) {
            $module = Module::getInstanceByName('lengow');
            $module->install();
        }
    }
}
