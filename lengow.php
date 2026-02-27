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
     * Lengow Install Class
     */
    /** @var LengowInstall */
    private LengowInstall $installClass;

    /**
     * Lengow Hook Class
     */
    /** @var LengowHook */
    private LengowHook $hookClass;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->name = 'lengow';
        $this->tab = 'export';
        $this->version = '3.9.4'; // x-release-please-version
        $this->author = 'Lengow';
        $this->module_key = '__LENGOW_PRESTASHOP_PRODUCT_KEY__';
        $this->ps_versions_compliancy = [
            'min' => '1.7.8',
            'max' => '9.99.99',
        ];

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Lengow');
        $this->description = $this->l('Lengow allows you to easily export your product catalogue from your PrestaShop store and sell on Amazon, Cdiscount, Google Shopping, Criteo, LeGuide.com, Ebay, Rakuten, Priceminister. Choose from our 1,800 available marketing channels!');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the Lengow module?');

        $this->installClass = new LengowInstall($this);
        $this->hookClass = new LengowHook($this);

        if (self::isInstalled($this->name)) {
            $oldVersion = LengowConfiguration::getGlobalValue(
                LengowConfiguration::PLUGIN_VERSION
            );
            if ($oldVersion !== $this->version) {
                $this->installClass->clearCaches();
                LengowConfiguration::updateGlobalValue(
                    LengowConfiguration::PLUGIN_VERSION,
                    $this->version
                );
                $this->installClass->update($oldVersion);
                $this->installClass->clearCaches();
            }
        }

        $this->context = Context::getContext();
        $this->context->smarty->assign('lengow_link', new LengowLink());
    }

    /**
     * Configure Link
     * Redirect on lengow configure page
     * @return void
     */
    public function getContent(): void
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
    public function install(): bool
    {
        $this->installClass->clearCaches();
        if (!parent::install()) {
            return false;
        }
        $isInstalled = $this->installClass->install();
        $this->installClass->clearCaches();

        return $isInstalled;
    }

    /**
     * Uninstall process
     *
     * @return bool
     */
    public function uninstall(): bool
    {
        $this->installClass->clearCaches();
        if (!parent::uninstall()) {
            return false;
        }
        $isUninstalled = $this->installClass->uninstall();
        $this->installClass->clearCaches();

        return $isUninstalled;
    }

    /**
     * Reset process
     *
     * @return bool
     */
    public function reset(): bool
    {
        $this->installClass->clearCaches();
        $isReset = $this->installClass->reset();
        $this->installClass->clearCaches();

        return $isReset;
    }

    /**
     * Hook to display the icon
     * @return void
     */
    public function hookDisplayBackOfficeHeader(): void
    {
        $this->hookClass->hookDisplayBackOfficeHeader();
    }

    /**
     * Hook on Home page
     *
     * @depercated Use hookDisplayHome instead
     * @return void
     */
    public function hookHome(): void
    {
        $this->hookClass->hookDisplayHome();
    }

    /**
     * Hook on Home page
     * @return void
     */
    public function hookDisplayHome(): void
    {
        $this->hookClass->hookDisplayHome();
    }

    /**
     * Hook on Payment page
     * @return void
     */
    public function hookDisplayPaymentTop(): void
    {
        $this->hookClass->hookPaymentTop();
    }

    /**
     * Hook for generate tracker on front footer page
     *
     * @return mixed
     */
    public function hookDisplayFooter(): mixed
    {
        return $this->hookClass->hookFooter();
    }

    /**
     * Hook on order confirmation page to init order's product list
     *
     * @param array<string, mixed> $args Arguments of hook
     * @return void
     */
    public function hookDisplayOrderConfirmation(array $args): void
    {
        $this->hookClass->hookOrderConfirmation($args);
    }

    /**
     * Order status update
     * Event This hook launches modules when the status of an order changes
     * @return void
     * @param array<string, mixed> $args
     */
    public function hookActionOrderStatusUpdate(array $args): void
    {
        $this->hookClass->hookUpdateOrderStatus($args);
    }

    /**
     * Order status post update
     *
     * @param array<string, mixed> $args Arguments of hook
     * @return void
     */
    public function hookActionOrderStatusPostUpdate(array $args): void
    {
        $this->hookClass->hookActionOrderStatusPostUpdate($args);
    }

    /**
     * Hook for update order if isset tracking number
     *
     * @param array<string, mixed> $args Arguments of hook
     * @return void
     */
    public function hookActionObjectUpdateAfter(array $args): void
    {
        $this->hookClass->hookActionObjectUpdateAfter($args);
    }

    /**
     * Hook on admin page's order
     *
     * @param array<string, mixed> $args Arguments of hook
     *
     * @return mixed
     */
    public function hookDisplayAdminOrder(array $args): mixed
    {
        return $this->hookClass->hookAdminOrder($args);
    }

    /**
     * Hook on admin page's order side
     *
     * @param array<string, mixed> $args Arguments of hook
     *
     * @return mixed
     */
    public function hookDisplayAdminOrderSide(array $args): mixed
    {
        return $this->hookClass->hookAdminOrderSide($args);
    }

    /**
     * Hook when a product line is refunded
     * @return void
     * @param array<string, mixed> $args
     */
    public function hookActionProductCancel(array $args): void
    {
        $this->hookClass->hookActionProductCancel($args);
    }
}
