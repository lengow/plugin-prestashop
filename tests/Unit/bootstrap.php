<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

// php7.4 -d date.timezone=Europe/Paris ./vendor/phpunit/phpunit/phpunit -c modules/lengow/tests/Unit/phpunit.xml modules/lengow/tests/Unit
define('_PS_IN_TEST_', true);
define('_PS_ROOT_DIR_', __DIR__ . '/../../../..');
define('_PS_MODULE_DIR_', _PS_ROOT_DIR_ . '/modules/');
require_once __DIR__ . '/../../../../config/defines.inc.php';
require_once _PS_CONFIG_DIR_ . 'autoload.php';
require_once _PS_CONFIG_DIR_ . 'config.inc.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/./Fixture.php';

if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
    define('PHPUNIT_COMPOSER_INSTALL', __DIR__ . '/../../vendor/autoload.php');
}

if (!defined('_NEW_COOKIE_KEY_')) {
    define('_NEW_COOKIE_KEY_', PhpEncryption::createNewRandomKey());
}

if (!defined('__PS_BASE_URI__')) {
    define('__PS_BASE_URI__', '');
}


