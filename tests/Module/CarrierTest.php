<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use GuzzleHttp\Client;
use Currency;
use Context;
use Db;
use Module;
use Configuration;
use LengowMain;
use LengowExport;
use LengowException;
use LengowCarrier;
use LengowConnector;
use Assert;

class CarrierTest extends ModuleTestCase
{
    public function setUp()
    {
        parent::setUp();
        //load module
        Module::getInstanceByName('lengow');
    }
}
