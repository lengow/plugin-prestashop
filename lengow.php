<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

$sep = DIRECTORY_SEPARATOR;
require_once _PS_MODULE_DIR_ . 'lengow' . $sep . 'loader.php';

class Lengow extends Module
{

    private $installClass;

    private $hookClass;

    public function __construct()
    {

        $this->name = 'lengow';
        $this->tab = 'export';
        $this->version = '3.0.0';
        $this->author = 'Lengow';
        $this->module_key = '92f99f52f2bc04ed999f02e7038f031c';
        $this->ps_versions_compliancy = array('min' => '1.4', 'max' => '1.7');

        parent::__construct();

        if (_PS_VERSION_ < '1.5') {
            $sep = DIRECTORY_SEPARATOR;
            require_once _PS_MODULE_DIR_.$this->name.$sep.'backward_compatibility'.$sep.'backward.php';
            $this->context = Context::getContext();
            $this->smarty = $this->context->smarty;
        }

        $this->displayName = $this->l('Lengow');
        $this->description = $this->l('Lengow allows you to easily export your product catalogue from your Prestashop
        store and sell on Amazon, Cdiscount, Google Shopping, Criteo, LeGuide.com, Ebay, Rakuten, Priceminister..
        Choose from our 1,800 available marketing channels!');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the Lengow module ?');

        $this->installClass = new LengowInstall($this);
        $this->hookClass = new LengowHook($this);

        if (self::isInstalled($this->name)) {
            if (Configuration::get('LENGOW_VERSION') != $this->version) {
                $this->installClass->update();
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

    public function install()
    {
        if (!parent::install()) {
            return false;
        }
        return $this->installClass->install();
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }
        return $this->installClass->uninstall();
    }


    /**
     * Hook Definition in LengowHook
     */
    public function hookHome()
    {
        return $this->hookClass->hookHome();
    }

    public function hookFooter()
    {
        return $this->hookClass->hookFooter();
    }

    public function hookUpdateOrderStatus($args)
    {
        return $this->hookClass->hookUpdateOrderStatus($args);
    }

    public function hookPostUpdateOrderStatus($args)
    {
        return $this->hookClass->hookPostUpdateOrderStatus($args);
    }

    public function hookActionObjectUpdateAfter($args)
    {
        return $this->hookClass->hookActionObjectUpdateAfter($args);
    }

    public function hookOrderConfirmation($args)
    {
        return $this->hookClass->hookOrderConfirmation($args);
    }

    public function hookPaymentTop($args)
    {
        return $this->hookClass->hookPaymentTop($args);
    }

    public function hookActionAdminControllerSetMedia($args)
    {
        return $this->hookClass->hookActionAdminControllerSetMedia($args);
    }

    public function hookAdminOrder($args)
    {
        return $this->hookClass->hookAdminOrder($args);
    }
}
