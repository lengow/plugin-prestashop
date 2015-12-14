<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use Db;

class Fixture
{

    private $defaultValues = array(
        "product" => array(
            "id_supplier" => 1,
            "id_manufacturer" => 1,
            "id_category_default" => 1,
            "price" => 1.8,
            "active" => 1,
        )
    );

    public function loadFixture($file)
    {
        $yml = \yaml_parse_file($file);
        foreach ($yml as $table_name => $row) {
            foreach ($row as $values) {
                Db::getInstance()->execute('TRUNCATE ' . _DB_PREFIX_ . $table_name);
                $this->loadData($table_name, $values);
            }
        }
    }

    public function loadData($table_name, $values)
    {
        if (isset($this->defaultValues[$table_name])) {
            foreach ($this->defaultValues[$table_name] as $key => $value) {
                if (isset($values[$key])) {
                    $values[$key] = $value;
                }
            }
        }
        Db::getInstance()->insert($table_name, $values);
    }
}
