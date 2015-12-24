<?php
/**
 * Copyright 2016 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2016 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

@set_time_limit(0);
$sep = DIRECTORY_SEPARATOR;
require_once dirname(__FILE__).$sep.'..'.$sep.'..'.$sep.'..'.$sep.'config'.$sep.'config.inc.php';
require_once dirname(__FILE__).$sep.'..'.$sep.'..'.$sep.'..'.$sep.'init.php';
require_once dirname(__FILE__).$sep.'..'.$sep.'lengow.php';
require_once dirname(__FILE__).$sep.'..'.$sep.'loader.php';

try
{
	loadFile('core');
	loadFile('import');
} catch(Exception $e)
{
	try
	{
		loadFile('core');
		LengowCore::log($e->getMessage(), null, 1);
	} catch (Exception $ex)
	{
		echo date('Y-m-d : H:i:s ').$e->getMessage().'<br />';
	}
}
/* check if Lengow is installed and enabled */
$lengow = new Lengow();
if (!Module::isInstalled($lengow->name))
{
	if (_PS_VERSION_ >= 1.5 && !Module::isEnabled($lengow->name))
		die('Lengow module is not active');

	die('Lengow module is not installed');
}

if (LengowCore::checkIP())
{
	$force_price = Configuration::get('LENGOW_FORCE_PRICE');
	if (Tools::getIsset('forcePrice'))
		$force_price = (bool)Tools::getValue('forcePrice');

	$force_product = Configuration::get('LENGOW_IMPORT_FORCE_PRODUCT');
	if (Tools::getIsset('forceProduct'))
		$force_product = Tools::getValue('forceProduct');

	/* check if debug is active in module config */
	$debug = Configuration::get('LENGOW_DEBUG');

	/* check if debug param is passed in URL */
	if (Tools::getIsset('lengow_debug'))
		$debug = (bool)Tools::getValue('lengow_debug');

	/* get start and end dates of import */
	$days = (int)Configuration::get('LENGOW_IMPORT_DAYS');
	if (Tools::getIsset('days') && is_numeric(Tools::getValue('days')))
		$days = (int)Tools::getValue('days');
	$date_from = date('Y-m-d', strtotime(date('Y-m-d').' -'.$days.'days'));
	$date_to = date('Y-m-d');

	$limit = 0;
	if (Configuration::get('LENGOW_IMPORT_SINGLE'))
		$limit = 1;
	elseif (Tools::getIsset('limit'))
		$limit = (int)Tools::getValue('limit');

	if (Tools::getIsset('idOrder') && Tools::getIsset('idFlux') && is_numeric(Tools::getValue('idFlux')))
	{
		$import = new LengowImport(
								Tools::getValue('idOrder'),
								(int)Tools::getValue('idFlux'),
								true,
								$debug
							);
	}
	else
		$import = new LengowImport(
								null,
								null,
								$force_product,
								$debug,
								$date_from,
								$date_to,
								$limit,
								true
							);
	$import->exec();
}
else
	die('Unauthorized access for IP : '.$_SERVER['REMOTE_ADDR']);