<?php


namespace PrestaShop\PrestaShop\Tests\Unit;

use PHPUnit_Framework_TestCase;
use Db;

class DummyTest extends PHPUnit_Framework_TestCase
{
    public function test_Dummy()
    {

        //$test = Db::getInstance()->executeS('SELECT * FROM ps_configuration');


        $this->assertTrue(true, "Everything works fine");
    }
}
