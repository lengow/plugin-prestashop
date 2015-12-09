<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

if (!defined('_PS_VERSION_')) {
    exit;
}

$sep = DIRECTORY_SEPARATOR;
require('models/lengow.install.class.php');
require_once _PS_MODULE_DIR_ . 'lengow' . $sep . 'loader.php';

class Lengow extends Module
{

    var $installClass;

    public function __construct()
    {

        $this->name = 'lengow';
        $this->tab = 'lengow_tab';
        $this->version = '3.0.0';
        $this->author = 'Lengow';
        $this->module_key = '92f99f52f2bc04ed999f02e7038f031c';
        $this->ps_versions_compliancy = array('min' => '1.4', 'max' => '1.7');

        parent::__construct();

        $this->displayName = $this->l('Lengow');
        $this->description = $this->l('Lengow allows you to easily export your product catalogue from your Prestashop store and sell on Amazon, Cdiscount, Google Shopping, Criteo, LeGuide.com, Ebay, Rakuten, Priceminister…  Choose from our 1,800 available marketing channels!');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the Lengow module ?');

        $this->installClass = new LengowInstall($this);
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


}