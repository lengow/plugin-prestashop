<?php

define('CURRENT_DOMAIN', 'prestashop.unit.test');

$rootDirectory = "/var/www/test/presta_1_6/";
require_once($rootDirectory . 'config/defines.inc.php');
require_once(_PS_CONFIG_DIR_.'autoload.php');
require_once 'TestCase/ModuleTestCase.php';
require_once 'TestCase/ControllerTestCase.php';

require_once(_PS_ROOT_DIR_.'/vendor/autoload.php');
