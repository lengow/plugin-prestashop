<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use PHPUnit_Framework_TestCase;
use Employee;
use Context;
use Rijndael;

class IntegrationTestCase extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        require_once(_PS_CONFIG_DIR_ . '/config.inc.php');

    }

    /**
     *
     * @runInSeparateProcess
     *
     */
    public function loadCookie()
    {
        $employee = new Employee();
        $employee->getByEmail("pub@prestashop.com");
        $content = array(
            'id_employee' => $employee->id,
            'email' => $employee->email,
            'profile' => $employee->id_profile,
            'passwd' => $employee->passwd,
            'remote_addr' => $employee->remote_addr,
        );
        $cookieContent = '';
        foreach ($content as $key => $value) {
            $cookieContent .= $key.'|'.$value.'¤';
        }
        $cookieContent .= 'checksum|'.crc32(_COOKIE_IV_.$cookieContent);
        var_dump($cookieContent);
        $cookieName = 'PrestaShop-'.md5(_PS_VERSION_.'psAdmin'.CURRENT_DOMAIN);


        $cipherTool = new Rijndael(_RIJNDAEL_KEY_, _RIJNDAEL_IV_);
        $cookieContent = $cipherTool->encrypt($cookieContent);

        //var_dump($cookieName);
        var_dump($cookieContent);

        return array($cookieContent, $cookieName);
    }

}
