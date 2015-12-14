<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use PHPUnit_Framework_TestCase;
use Db;

class ModuleTestCase extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        require_once(_PS_CONFIG_DIR_ . '/config.inc.php');

    }

    public static function loadFixture($file)
    {
        $yaml = \yaml_parse_file($file);
        foreach ($yaml as $table_name => $row) {
            foreach ($row as $values) {
                Db::getInstance()->execute('TRUNCATE '._DB_PREFIX_.$table_name);
                switch ($table_name) {
                    case "product":
                        if (isset($values["id_product"])) {
                            Db::getInstance()->delete($table_name, ' id_product = '.$values["id_product"]);
                        }
                        break;
                    case "product_shop":
                        if (isset($values["id_product"]) && isset($values["id_shop"])) {
                            Db::getInstance()->delete(
                                $table_name,
                                'id_product = '.$values["id_product"].' AND id_shop ='.$values["id_shop"]
                            );
                        }
                        break;
                    case "product_lang":
                        if (isset($values["id_product"]) && isset($values["id_shop"]) && isset($values["id_lang"])) {
                            Db::getInstance()->delete(
                                $table_name,
                                'id_product = '.$values["id_product"].' AND id_shop ='.$values["id_shop"]
                                .' AND id_lang ='.$values["id_lang"]
                            );
                        }
                        break;
                }
                Db::getInstance()->insert($table_name, $values);
            }
        }
    }
}
