<?php
/**
 * Copyright 2017 Lengow SAS.
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
 * @copyright 2017 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */
require_once _PS_MODULE_DIR_ . 'lengow' . DIRECTORY_SEPARATOR . 'loader.php';

if (!defined('_PS_VERSION_')) {
    exit;
}
/**
 * Lengow
 */
class Lengow extends Module
{
    /**
     * @const string LENGOW MODULE NAME
     */
    public const MODULE_NAME = 'lengow';
    /**
     * @const string LENGOW MODULE TAB
     */
    public const MODULE_TAB = 'export';
    /**
     * @const string LENGOW MODULE VERSION
     */
    public const MODULE_VERSION = '3.5.2';
    /**
     * @const string LENGOW MODULE AUTHOR
     */
    public const MODULE_AUTHOR = 'Lengow';
    /**
     * @const string LENGOW MODULE KEY
     */
    public const MODULE_KEY = '__LENGOW_PRESTASHOP_PRODUCT_KEY__';
    /**
     * @const array LENGOW MODULE COMPATIBILITY
     */
    public const MODULE_COMPATIBILITY = [
        'min' => '1.7.7.0',
        'max' => '8.99.99',
    ];    
    /**
     * Lengow Install Class
     */
    private $installClass;

    /**
     * Lengow Hook Class
     */
    private $hookClass;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->name = self::MODULE_NAME;
        $this->tab = self::MODULE_TAB;
        $this->version = self::MODULE_VERSION;
        $this->author = self::MODULE_AUTHOR;
        $this->module_key = self::MODULE_KEY;
        $this->ps_versions_compliancy = self::MODULE_COMPATIBILITY
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Lengow');
        $this->description = $this->l('Lengow allows you to easily export your product catalogue from your PrestaShop store and sell on Amazon, Cdiscount, Google Shopping, Criteo, LeGuide.com, Ebay, Rakuten, Priceminister. Choose from our 1,800 available marketing channels!');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the Lengow module?');

        $this->installClass = new LengowInstall($this);
        $this->hookClass = new LengowHook($this);

        if (self::isInstalled($this->name)) {
            $oldVersion = LengowConfiguration::getGlobalValue(LengowConfiguration::PLUGIN_VERSION);
            if ($oldVersion !== $this->version) {
                LengowConfiguration::updateGlobalValue(LengowConfiguration::PLUGIN_VERSION, $this->version);
                $this->installClass->update($oldVersion);
            }
        }

        $this->context = Context::getContext();
        $this->context->smarty->assign('lengow_link', new LengowLink());
    }

    /**
     * Configure Link
     * Redirect on lengow configure page
     */
    public function getContent()
    {
        $link = new LengowLink();
        $configLink = $link->getAbsoluteAdminLink('AdminLengowHome');
        Tools::redirect($configLink, '');
    }

    /**
     * Install process
     *
     * @return bool
     */
    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        return $this->installClass->install();
    }

    /**
     * Uninstall process
     *
     * @return bool
     */
    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        return $this->installClass->uninstall();
    }

    /**
     * Reset process
     *
     * @return bool
     */
    public function reset()
    {
        return $this->installClass->reset();
    }

    /**
     * Hook to display the icon
     */
    public function hookDisplayBackOfficeHeader()
    {
        $this->hookClass->hookDisplayBackOfficeHeader();
    }

    /**
     * Hook on Home page
     */
    public function hookHome()
    {
        $this->hookClass->hookHome();
    }

    /**
     * Hook on Payment page
     */
    public function hookPaymentTop()
    {
        $this->hookClass->hookPaymentTop();
    }

    /**
     * Hook for generate tracker on front footer page
     *
     * @return mixed
     */
    public function hookFooter()
    {
        return $this->hookClass->hookFooter();
    }

    /**
     * Hook on order confirmation page to init order's product list
     *
     * @param array $args Arguments of hook
     */
    public function hookOrderConfirmation($args)
    {
        $this->hookClass->hookOrderConfirmation($args);
    }

    /**
     * Hook before an status update to synchronize status with lengow
     *
     * @param array $args Arguments of hook
     */
    public function hookUpdateOrderStatus($args)
    {
        $this->hookClass->hookUpdateOrderStatus($args);
    }

    /**
     * Hook after an status update to synchronize status with lengow
     *
     * @param array $args Arguments of hook
     */
    public function hookPostUpdateOrderStatus($args)
    {
        $this->hookClass->hookPostUpdateOrderStatus($args);
    }

    /**
     * Hook for update order if isset tracking number
     *
     * @param array $args Arguments of hook
     */
    public function hookActionObjectUpdateAfter($args)
    {
        $this->hookClass->hookActionObjectUpdateAfter($args);
    }

    /**
     * Hook on admin page's order
     *
     * @param array $args Arguments of hook
     *
     * @return mixed
     */
    public function hookAdminOrder($args)
    {
        return $this->hookClass->hookAdminOrder($args);
    }
}
