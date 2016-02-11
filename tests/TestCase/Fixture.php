<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use Db;

class Fixture
{

    private $alreadyTruncate = array();

    private $defaultValues = array(
        "product" => array(
            "id_supplier" => 1,
            "id_manufacturer" => 1,
            "id_category_default" => 1,
            "price" => 1.8,
            "active" => 1,
            "redirect_type" => "404",
            "condition" => "new",
        ),
        "product_shop" => array(
            "active" => 1
        ),
        "image_lang" => array(
            "id_lang" => 1
        ),
        "lengow_product" => array(
            "id_shop" => 1,
            "id_shop_group" => 1,
            "id_lang" => 1,
        ),
        "lengow_orders" => array(
            "id_shop" => 1,
            "id_shop_group" => 1,
            "id_lang" => 1,
        )
    );
    private $dateValues = array(
        "product",
        "product_shop"
    );


    public function loadFixture($file, $params = array())
    {

        $truncate = isset($params["force_truncate"]) ? $params["force_truncate"] : false;

        $yml = \yaml_parse_file($file);
        foreach ($yml as $tableName => $row) {
            //don't re-truncate tables
            if ($truncate || !isset($this->alreadyTruncate[$tableName])) {
                Db::getInstance()->execute('TRUNCATE ' . _DB_PREFIX_ . $tableName);
                $this->alreadyTruncate[$tableName] = true;
            }
            if ($row) {
                foreach ($row as $values) {
                    $this->loadData($tableName, $values);
                }
            }
        }
    }

    public function loadData($table_name, $values)
    {
        if (isset($this->defaultValues[$table_name])) {
            foreach ($this->defaultValues[$table_name] as $key => $value) {
                if (!isset($values[$key])) {
                    $values[$key] = $value;
                }
            }
        }
        foreach ($values as $key => &$value) {
            $value = addslashes($value);
        }
        if (in_array($table_name, $this->dateValues)) {
            if (!isset($values["date_add"])) {
                $values["date_add"] = date('Y-m-d H:m:i');
            }
            if (!isset($values["date_upd"])) {
                $values["date_upd"] = date('Y-m-d H:m:i');
            }
        }
        Db::getInstance()->insert($table_name, $values);
    }

    public function truncate($tableName)
    {
        Db::getInstance()->execute('TRUNCATE ' . _DB_PREFIX_ . $tableName);
    }
}
