<?php

define('CURRENT_DOMAIN', 'prestashop.unit.test');

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_ADDR'] = '99.99.99.99';

$rootDirectory = "/var/www/test/presta_1_6/";
require_once($rootDirectory . 'config/defines.inc.php');


require_once(_PS_CONFIG_DIR_ . 'autoload.php');
require_once 'TestCase/Fixture.php';
require_once 'TestCase/ModuleTestCase.php';
require_once 'TestCase/ControllerTestCase.php';

require_once(_PS_ROOT_DIR_ . '/vendor/autoload.php');
