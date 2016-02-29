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

/**
 * The Lengow File class
 *
 */
class LengowFlat
{

    protected $context;

    public static $AADEFAULT_FIELDS = array(
        'category' => 'breadcrumb',
        'price_product' => 'price',
        'wholesale_price' => 'wholesale_price',
        'price_ht' => 'price_duty_free',
        'price_reduction' => 'price_sale',
        'pourcentage_reduction' => 'price_sale_percent',
        'available_product' => 'available',
        'image_product' => 'image_1',
        'delais_livraison' => 'delivery_time',
        'image_product_2' => 'image_2',
        'image_product_3' => 'image_3',
        'reduction_from' => 'sale_from',
        'reduction_to' => 'sale_to',
        'product_variation' => 'variation',
        'condition' => 'condition',
        'supplier' => 'supplier',
        'available_for_order' => 'available_for_order',
        'available_date' => 'available_date',
        'available_now' => 'available_now',
        'available_later' => 'available_later',
        'stock_availables' => 'stock_availables',
    );

    const TABLE_NAME = 'lengow_product_flat';

    /**
     * Default fields.
     */
    public static $DEFAULT_FIELDS = array(
        'id_product' =>         array('type' => 'varchar', 'size' => '50'),
        'id_parent' =>          array('type' => 'int', 'size' => '11'),
        'reference' =>          array('type' => 'varchar', 'size' => '32'),
        'supplier_reference' => array('type' => 'varchar', 'size' => '32'),
        'active' =>             array('type' => 'tinyint', 'size' => '1'),
        'availability' =>       array('type' => 'tinyint', 'size' => '1'),
        'is_virtual' =>         array('type' => 'tinyint', 'size' => '1'),
        'product_type' =>       array('type' => 'varchar', 'size' => '6'),
        'quantity' =>           array('type' => 'int', 'size' => '10'),
        'minimal_quantity' =>   array('type' => 'int', 'size' => '10'),
        'visibility' =>         array('type' => 'varchar', 'size' => '7'),
        'weight' =>             array('type' => 'decimal', 'size' => '20,6'),
        'ean' =>                array('type' => 'varchar', 'size' => '13'),
        'upc' =>                array('type' => 'varchar', 'size' => '12'),
        'ecotax' =>             array('type' => 'decimal', 'size' => '17,6'),
        'name_product' =>       array('type' => 'varchar', 'size' => '250'),
        'description' =>        array('type' => 'text', 'size' => '65535'),
        'description_html' =>   array('type' => 'text', 'size' => '65535'),
        'description_short' =>  array('type' => 'text', 'size' => '65535'),
        'meta_keywords' =>      array('type' => 'varchar', 'size' => '255'),
        'meta_description' =>   array('type' => 'varchar', 'size' => '255'),
        'manufacturer_name' =>  array('type' => 'varchar', 'size' => '64'),
        'supplier' =>           array('type' => 'varchar', 'size' => '64'),
        'show_price' =>         array('type' => 'tinyint', 'size' => '1'),
        'price_shipping' =>     array('type' => 'decimal', 'size' => '10,4'),
        'currency' =>           array('type' => 'varchar', 'size' => '3'),
        'url_rewrite' =>        array('type' => 'varchar', 'size' => '128'),
        'url_product' =>        array('type' => 'varchar', 'size' => '256'),
    );

    public function buildTable()
    {

        $this->context = Context::getContext();

        $this->setCarrier();
        $this->checkCurrency();

        if (count(Db::getInstance()->ExecuteS("SHOW TABLES LIKE '"._DB_PREFIX_.self::TABLE_NAME."' "))==0) {
            $sql = array();
            $sql[]= ' `id` INT(11) NOT NULL AUTO_INCREMENT ';
            foreach (self::$DEFAULT_FIELDS as $key => $value) {
                $sql[]= ' `'.$key.'` '.$value['type'].'('.$value['size'].') NULL';
            }
            $sql[]= ' `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ';
            $sql[]= ' `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ';
            $sql[]= ' PRIMARY KEY (id) ';
            $sql = 'CREATE TABLE `'._DB_PREFIX_.self::TABLE_NAME.'` ('.join(',', $sql).')
             ENGINE=InnoDB DEFAULT CHARSET=latin1;';
            Db::getInstance()->Execute($sql);

            $sql = 'CREATE INDEX id_product_idx ON `'._DB_PREFIX_.self::TABLE_NAME.'` (id_product)';
            Db::getInstance()->Execute($sql);
        } else {
            foreach (self::$DEFAULT_FIELDS as $field => $values) {
                $result = Db::getInstance()->ExecuteS(
                    'SHOW COLUMNS FROM `'._DB_PREFIX_.self::TABLE_NAME.'` LIKE "'.$field.'"'
                );
                if (count($result)==0) {
                    Db::getInstance()->Execute('DROP TABLE '._DB_PREFIX_.self::TABLE_NAME);
                    $this->buildTable();
                    return true;
                }
            }
        }

    }

    public function populateTable()
    {
        $langId = $this->context->language->id;
        $shopId = $this->context->shop->id;

        $select = array();
        $selectSql = array();
        $select["p"] = array(
            'id_product'  => array('like' => 'id_product'),
            'reference'  => array('like' => 'reference'),
            'is_virtual'  => array('like' => 'is_virtual'),
            'quantity' => array('like' => 'p_quantity'),
            'weight' => array('like' => 'p_weight'),
            'ean13' => array('like' => 'p_ean'),
            'upc' => array('like' => 'p_upc'),
            'ecotax' => array('like' => 'p_ecotax'),
            'active'  => array('like' => 'p_active'),
            'minimal_quantity'  => array('like' => 'p_minimal_quantity'),
            'visibility'  => array('like' => 'p_visibility'),
            'show_price'  => array('like' => 'p_show_price'),
        );

        $select["pl"] = array(
            'name',
            'description',
            'description_short',
            'meta_description',
            'meta_keywords',
            'meta_title',
            'meta_title',
            'link_rewrite',
        );
        $select["ps"] = array(
            'product_supplier_reference' => array('like' => 'supplier_reference'),
        );
        $select["m"] = array(
            'name' => array('like' => 'manufacturer_name'),
        );
        $select["s"] = array(
            'name' => array('like' => 'supplier_name'),
        );
        $select["pshop"] = array(
            'price'  => array('like' => 'price'),
            'active'  => array('like' => 'pshop_active'),
            'ecotax' => array('like' => 'pshop_ecotax'),
            'minimal_quantity'  => array('like' => 'pshop_minimal_quantity'),
            'visibility'  => array('like' => 'pshop_visibility'),
            'show_price'  => array('like' => 'pshop_show_price'),
        );
        $select["pa"] = array(
            'id_product_attribute' => array('like' => 'id_product_attribute'),
            'quantity' => array('like' => 'pa_quantity'),
            'reference' => array('like' => 'pa_reference'),
            'supplier_reference' => array('like' => 'pa_supplier_reference'),
            'weight' => array('like' => 'pa_weight'),
            'ean13' => array('like' => 'pa_ean'),
            'upc' => array('like' => 'pa_upc'),
            'ecotax' => array('like' => 'pa_ecotax'),
            'minimal_quantity'  => array('like' => 'pa_minimal_quantity'),
        );
        $select["pas"] = array(
            'ecotax' => array('like' => 'pas_ecotax'),
            'minimal_quantity'  => array('like' => 'pas_minimal_quantity'),
        );
        $select["sa"] = array(
            'quantity' => array('like' => 'sa_quantity'),
        );

        foreach ($select as $table => $values) {
            $isIndexed = array_values($values) === $values;
            if ($isIndexed) {
                foreach ($values as $value) {
                    $selectSql[] = $table.'.'.$value;
                }
            } else {
                foreach ($values as $value => $properties) {
                    $selectSql[] = $table.'.'.$value.' as '.$properties['like'];
                }
            }
        }

        $query = ' SELECT '.join(', ', $selectSql);
        $query.= ' FROM '._DB_PREFIX_.'product p';
        $query.= ' INNER JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = p.id_product AND
        pl.id_shop =1 AND pl.id_lang=1) ';
        $query.= ' INNER JOIN '._DB_PREFIX_.'product_attribute pa ON (pa.id_product = p.id_product) ';
        $query.= ' LEFT JOIN '._DB_PREFIX_.'product_supplier ps ON (ps.id_product = p.id_product AND
        ps.id_supplier = p.id_supplier) ';
        $query.= ' LEFT JOIN '._DB_PREFIX_.'supplier s ON (s.id_supplier = p.id_supplier) ';
        $query.= ' LEFT JOIN '._DB_PREFIX_.'stock_available sa ON (sa.id_product = p.id_product AND
        sa.id_product_attribute = pa.id_product_attribute) ';
        $query.= ' LEFT JOIN '._DB_PREFIX_.'manufacturer m ON (p.id_manufacturer = m.id_manufacturer) ';
        $query.= ' LEFT JOIN '._DB_PREFIX_.'product_shop pshop ON (p.id_product = pshop.id_product
        AND pshop.id_shop =1 ) ';
        $query.= ' LEFT JOIN '._DB_PREFIX_.'product_attribute_shop pas ON (p.id_product = pas.id_product
        AND pas.id_shop =1 ) ';
        $productCollection = Db::getInstance()->executeS($query);

        $productTaxes = $this->getProductTaxes();

        foreach ($productCollection as $productInstance) {
            $product = new LengowFlatProduct(
                $productInstance,
                array(
                    "context" => $this->context,
                    "productTaxes" => $productTaxes,
                    "carrier" => $this->carrier,
                    "currency" => $this->context->currency->iso_code
                )
            );
            $this->populateProduct($product);
        }

    }

    public function populateProduct($product)
    {
        $data = array();
        foreach (self::$DEFAULT_FIELDS as $key => $value) {
            switch ($value["type"]) {
                case "int":
                case "tinyint":
                case "decimal":
                    $data[$key] = $product->$key;
                    break;
                case "varchar":
                case "text":
                    $data[$key] = pSQL($product->$key);
                    break;
            }
        }
        $query = Db::getInstance()->ExecuteS('SELECT id FROM `' . _DB_PREFIX_.self::TABLE_NAME.'`
        WHERE id_product="'.$product->id_product.'"');
        if (count($query)>0) {
            Db::getInstance()->update(self::TABLE_NAME, $data, 'id_product = "'.$product->id_product.'"');
        } else {
            Db::getInstance()->insert(self::TABLE_NAME, $data);
        }
    }

    public function getProductTaxes()
    {
        // Tax calcul
        $defaultCountry = Configuration::get('PS_COUNTRY_DEFAULT');
        $taxeRules = LengowTaxRule::getLengowTaxRulesByGroupId(
            Configuration::get('PS_LANG_DEFAULT'),
            $this->carrier->id_tax_rules_group
        );

        //todo check tax on shipping price ?
        if (count($taxeRules)==0) {
            return null;
        }

        foreach ($taxeRules as $taxe_rule) {
            if (isset($taxe_rule['id_country']) && $taxe_rule['id_country'] == $defaultCountry) {
                $tr = new TaxRule($taxe_rule['id_tax_rule']);
            }
        }
        return new Tax($tr->id_tax);
    }


    /**
     * Set Carrier to export.
     *
     * @throws LengowException
     *
     * @return boolean.
     */
    public function setCarrier()
    {
        throw new LengowException('todo : set carrier');
        return true;
    }

    /**
     * Check currency to export.
     *
     * @throws LengowException
     *
     * @return boolean.
     */
    public function checkCurrency()
    {
        if (!$this->context->currency) {
            throw new LengowException('Illegal Currency');
        }
        return true;
    }
}
