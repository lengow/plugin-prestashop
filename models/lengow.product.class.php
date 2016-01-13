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
 * The Lengow Product Class.
 *
 */
class LengowProduct extends Product
{

    /**
     * Version.
     */
    const VERSION = '1.0.1';

    /**
     * API nodes containing relevant data
     */
    public static $PRODUCT_API_NODES = array(
        'idLengow',
        'idMP',
        'sku',
        'ean',
        'order_lineid',
        'quantity',
        'price',
        'price_unit',
        'shipping_price',
        'status',
    );

    /**
     * Images of produtcs
     */
    protected $images;

    /**
     * The product cover.
     */
    protected $cover;

    /**
     * Default category.
     */
    protected $category_default;

    /**
     * Name of default category.
     */
    protected $category_name;

    /**
     * If product is sale.
     */
    protected $is_sale = false;

    /**
     * Array combination of product's attributes.
     */
    protected $combinations;

    /**
     * Array of product's features.
     */
    protected $features;

    /*
     * Get Default Carrier
     */
    protected $carrier;

    /**
     * Variation.
     */
    protected $variation;

    /**
     * Load a new product.
     *
     * @param integer $id_product The ID product to load
     * @param integer $id_lang The ID lang for product's content
     * @param object $context The context
     */
    public function __construct($id_product = null, $id_lang = null, $params = array())
    {

        $this->carrier = isset($params["carrier"]) ? $params["carrier"] : null;

        parent::__construct($id_product, false, $id_lang);
        $context = Context::getContext();

        // Need to get price Product::getPriceStatic
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'employee WHERE active = 1 LIMIT 1';
        $findEmployee = Db::getInstance()->ExecuteS($sql);
        if ($findEmployee) {
            $context->employee = $findEmployee;
        } else {
            throw new LengowExportException('No Active Employee Fund');
        }



        // The applicable tax may be BOTH the product one AND the state one (moreover this variable is some deadcode)
        $this->tax_name = 'deprecated';

        $this->manufacturer_name = Manufacturer::getNameById((int)$this->id_manufacturer);
        $this->supplier_name = Supplier::getNameById((int)$this->id_supplier);
        $address = null;
        if (is_object($context->cart) && $context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')} != null) {
            $address = $context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
        }
        if (LengowMain::compareVersion()) {
            $this->tax_rate = $this->getTaxesRate(new Address($address));
        } else {
            $cart = Context::getContext()->cart;
            if (is_object($cart) && $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')} != null) {
                $this->tax_rate = Tax::getProductTaxRate($this->id, $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
            } else {
                $this->tax_rate = Tax::getProductTaxRate($this->id, null);
            }
        }
        $this->new = $this->isNew();
        $this->base_price = $this->price;
        if ($this->id) {
            $this->price = Product::getPriceStatic(
                (int)$this->id,
                false,
                null,
                2,
                null,
                false,
                true,
                1,
                false,
                null,
                null,
                null,
                $this->specificPrice
            );

            $this->unit_price = ($this->unit_price_ratio != 0 ? $this->price / $this->unit_price_ratio : 0);
        }
        if (LengowMain::compareVersion()) {
            $this->loadStockData();
        }
        if ($this->id_category_default && $this->id_category_default > 1) {
            $this->category_default = new Category((int)$this->id_category_default, $id_lang);
            $this->category_name = $this->category_default->name;
        } else {
            $categories = self::getProductCategories($this->id);
            if (!empty($categories)) {
                $this->category_default = new Category($categories[0], $id_lang);
                $this->category_name = $this->category_default->name;
            }
        }
        $images = $this->getImages($id_lang);
        $array_images = array();
        foreach ($images as $image) {
            if ($image['cover']) {
                $this->cover = $image;
            } else {
                $array_images[] = $image;
            }
        }
        $this->images = $array_images;
        $today = date('Y-m-d H:i:s');
        if (isset($this->specificPrice) && is_array($this->specificPrice)) {
            if (array_key_exists('from', $this->specificPrice) && array_key_exists('to', $this->specificPrice)) {
                if ($this->specificPrice['from'] <= $today && $today <= $this->specificPrice['to']) {
                    $this->is_sale = true;
                }
            }
        }
        $this->makeFeatures($context);
        $this->makeAttributes($context);
    }

    /**
     * Get data of current product.
     *
     * @param string $name the data name
     * @param integer $id_product_attribute the id product attribute
     * @param boolean $full_title set full title for product
     *
     * @return varchar The data.
     */
    public function getData($name, $id_product_attribute = null, $full_title = false)
    {
        switch ($name) {
            case 'id':
                if ($id_product_attribute) {
                    return $this->id . '_' . $id_product_attribute;
                }
                return $this->id;
                break;
            case 'name':
                $tmpName =$this->name;
                if ($id_product_attribute && $full_title) {
                    if ($this->combinations[$id_product_attribute]['attribute_name']) {
                        $tmpName = $this->name.' - '.$this->combinations[$id_product_attribute]['attribute_name'];
                    }
                }
                return LengowMain::cleanData($tmpName);
                break;
            case 'reference':
                if ($id_product_attribute > 1 && $this->combinations[$id_product_attribute]['reference']) {
                    return $this->combinations[$id_product_attribute]['reference'];
                }
                return $this->reference;
                break;
            case 'supplier_reference':
                if ($id_product_attribute > 1 && $this->combinations[$id_product_attribute]['supplier_reference']) {
                    return $this->combinations[$id_product_attribute]['supplier_reference'];
                }
                return $this->supplier_reference;
                break;
            case 'manufacturer':
                return LengowMain::cleanData($this->manufacturer_name);
                break;
            case 'category':
                return $this->category_name;
                break;
            case 'breadcrumb':
                if ($this->category_default) {
                    $breadcrumb = '';
                    $categories = $this->category_default->getParentsCategories();
                    foreach ($categories as $category) {
                        $breadcrumb = $category['name'] . ' > ' . $breadcrumb;
                    }
                    return rtrim($breadcrumb, ' > ');
                }
                return $this->category_name;
                break;
            case 'description':
                return LengowMain::cleanHtml(LengowMain::cleanData($this->description));
                break;
            case 'short_description':
                return LengowMain::cleanHtml(LengowMain::cleanData($this->description_short));
                break;
            case 'description_html':
                return LengowMain::cleanData($this->description);
                break;
            case 'price':
                if ($id_product_attribute) {
                    return $this->getPrice(true, $id_product_attribute, 2, null, false, false, 1);
                }
                return $this->getPrice(true, null, 2, null, false, false, 1);
                break;
            case 'wholesale_price':
                if ($id_product_attribute > 1 && $this->combinations[$id_product_attribute]['wholesale_price']) {
                    return LengowMain::formatNumber($this->combinations[$id_product_attribute]['wholesale_price']);
                }
                return LengowMain::formatNumber($this->wholesale_price, 2);
                break;
            case 'price_duty_free':
                if ($id_product_attribute) {
                    return $this->getPrice(false, $id_product_attribute, 2, null, false, false, 1);
                }
                return $this->getPrice(false, null, 2, null, false, false, 1);
                break;
            case 'price_sale':
                if ($id_product_attribute) {
                    return $this->getPrice(true, $id_product_attribute, 2, null, false, true, 1);
                }
                return $this->getPrice(true, null, 2, null, false, true, 1);
                break;
            case 'price_sale_percent':
                if ($id_product_attribute) {
                    $price = $this->getPrice(true, $id_product_attribute, 2, null, false, false, 1);
                    $price_sale = $this->getPrice(true, $id_product_attribute, 2, null, true, true, 1);
                } else {
                    $price = $this->getPrice(true, null, 2, null, false, false, 1);
                    $price_sale = $this->getPrice(true, null, 2, null, true, true, 1);
                }

                if ($price_sale && $price) {
                    return LengowMain::formatNumber(($price_sale / $price) * 100);
                }
                return 0;
                break;
            case 'quantity':
                if ($id_product_attribute) {
                    return self::getRealQuantity($this->id, $id_product_attribute);
                }
                return self::getRealQuantity($this->id);
                break;
            case 'weight':
                if ($id_product_attribute && $this->combinations[$id_product_attribute]['weight']) {
                    return $this->weight + $this->combinations[$id_product_attribute]['weight'];
                }
                return $this->weight;
                break;
            case 'ean':
                if ($id_product_attribute > 1 && $this->combinations[$id_product_attribute]['ean13']) {
                    return $this->combinations[$id_product_attribute]['ean13'];
                }
                return $this->ean13;
                break;
            case 'upc':
                if ($id_product_attribute > 1 && $this->combinations[$id_product_attribute]['upc']) {
                    return $this->combinations[$id_product_attribute]['upc'];
                }
                return $this->upc;
                break;
            case 'ecotax' :
                if ($id_product_attribute > 1 && $this->combinations[$id_product_attribute]['ecotax']) {
                    return LengowMain::formatNumber($this->combinations[$id_product_attribute]['ecotax']);
                }
                return isset($this->ecotaxinfos) && $this->ecotaxinfos > 0 ? LengowMain::formatNumber($this->ecotaxinfos) : LengowMain::formatNumber($this->ecotax);
                break;
            case 'active':
                return $this->active;
                break;
            case 'available':
                if ($id_product_attribute) {
                    $quantity = self::getRealQuantity($this->id, $id_product_attribute);
                } else {
                    $quantity = self::getRealQuantity($this->id);
                }
                if ($quantity <= 0) {
                    return $this->available_later;
                }
                return $this->available_now;
                break;
            case 'url':
                if (version_compare(_PS_VERSION_, '1.5', '>')) {
                    if (version_compare(_PS_VERSION_, '1.6.0.14', '>')) {
                        return Context::getContext()->link->getProductLink($this, null, null, null, null, null,
                            $id_product_attribute, true);
                    }
                    return Context::getContext()->link->getProductLink($this, null, null, null, null, null,
                        $id_product_attribute);
                }
                return Context::getContext()->link->getProductLink($this);
                break;
            case 'image_1':
                if ($id_product_attribute) {
                    $images = $this->getCombinationImages($this->id_lang);
                    if (is_array($images) && array_key_exists($id_product_attribute, $images)) {
                        return Context::getContext()->link->getImageLink($this->link_rewrite,
                            $this->id . '-' . $images[$id_product_attribute][0]['id_image'],
                            LengowMain::getImageFormat());
                    }
                }
                return isset($this->cover) ? Context::getContext()->link->getImageLink($this->link_rewrite,
                    $this->id . '-' . $this->cover['id_image'], LengowMain::getImageFormat()) : '';
                break;
            case 'price_shipping':
                if ($id_product_attribute && $id_product_attribute != null) {
                    $price = $this->getData('price_sale', $id_product_attribute);
                    $weight = $this->getData('weight', $id_product_attribute);
                } else {
                    $price = $this->getData('price_sale');
                    $weight = $this->getData('weight');
                }
                $context = Context::getContext();

                $id_zone = $context->country->id_zone;
                $id_currency = $context->cart->id_currency;
                $shipping_method = $this->carrier->getShippingMethod();
                $shipping_cost = 0;
                if (!defined('Carrier::SHIPPING_METHOD_FREE') || $shipping_method != Carrier::SHIPPING_METHOD_FREE) {
                    if ($shipping_method == Carrier::SHIPPING_METHOD_WEIGHT) {
                        $shipping_cost = LengowMain::formatNumber(
                            $this->carrier->getDeliveryPriceByWeight($weight, (int)$id_zone)
                        );
                    } else {
                        $shipping_cost = LengowMain::formatNumber(
                            $this->carrier->getDeliveryPriceByPrice(
                                $price,
                                (int)$id_zone,
                                (int)$id_currency
                            )
                        );
                    }
                }

                // Check if product have single shipping cost
                if ($this->additional_shipping_cost > 0) {
                    $shipping_cost += $this->additional_shipping_cost;
                }

                // Tax calcul
                $default_country = Configuration::get('PS_COUNTRY_DEFAULT');
                $taxe_rules = LengowTaxRule::getLengowTaxRulesByGroupId(
                    Configuration::get('PS_LANG_DEFAULT'),
                    $this->carrier->id_tax_rules_group
                );
                foreach ($taxe_rules as $taxe_rule) {
                    if (isset($taxe_rule['id_country']) && $taxe_rule['id_country'] == $default_country) {
                        $tr = new TaxRule($taxe_rule['id_tax_rule']);
                    }
                }

                if (isset($tr)) {
                    $t = new Tax($tr->id_tax);
                    $tax_calculator = new TaxCalculator(array($t));
                    $taxes = $tax_calculator->getTaxesAmount($shipping_cost);
                    if (!empty($taxes)) {
                        foreach ($taxes as $taxe) {
                            $shipping_cost += $taxe;
                        }
                    }
                }
                return LengowMain::formatNumber($shipping_cost);
                break;
            case 'id_parent':
                return $this->id;
                break;
            case 'delivery_time':
                return $this->carrier->delay[Context::getContext()->language->id];
                break;
            case 'image_2':
                if ($id_product_attribute) {
                    $images = $this->getCombinationImages($this->id_lang);
                    if (is_array($images) && array_key_exists($id_product_attribute, $images)) {
                        if (isset($images[$id_product_attribute][1]['id_image'])) {
                            return Context::getContext()->link->getImageLink($this->link_rewrite,
                                $this->id . '-' . $images[$id_product_attribute][1]['id_image'],
                                LengowMain::getImageFormat());
                        } else {
                            return '';
                        }
                    }
                }
                return isset($this->images[0]) ? Context::getContext()->link->getImageLink($this->link_rewrite,
                    $this->id . '-' . $this->images[0]['id_image'], LengowMain::getImageFormat()) : '';
                break;
            case 'image_3':
                if ($id_product_attribute) {
                    $images = $this->getCombinationImages($this->id_lang);
                    if (is_array($images) && array_key_exists($id_product_attribute, $images)) {
                        if (isset($images[$id_product_attribute][2]['id_image'])) {
                            return Context::getContext()->link->getImageLink($this->link_rewrite,
                                $this->id . '-' . $images[$id_product_attribute][2]['id_image'],
                                LengowMain::getImageFormat());
                        } else {
                            return '';
                        }
                    }
                }
                return isset($this->images[1]) ? Context::getContext()->link->getImageLink($this->link_rewrite,
                    $this->id . '-' . $this->images[1]['id_image'], LengowMain::getImageFormat()) : '';
                break;
            case 'sale_from':
                return $this->is_sale ? $this->specificPrice['from'] : '';
                break;
            case 'sale_to':
                return $this->is_sale ? $this->specificPrice['to'] : '';
                break;
            case 'meta_keywords':
                return LengowMain::cleanData($this->meta_keywords);
                break;
            case 'meta_description':
                return LengowMain::cleanData($this->meta_description);
                break;
            case 'url_rewrite':
                //return 'http://mydomain.com/index.php?controller=product&id_product=' . $this->id;
                return Context::getContext()->link->getProductLink(
                    $this,
                    $this->link_rewrite,
                    null,
                    null,
                    null,
                    null,
                    $id_product_attribute
                );
                return Context::getContext()->link->getProductLink($this, $this->link_rewrite);
                break;
            case 'type':
                if ($id_product_attribute) {
                    return 'child';
                } else {
                    if (empty($this->combinations)) {
                        return 'simple';
                    } else {
                        return 'parent';
                    }
                }
                break;
            case 'variation':
                return $this->variation;
                break;
            case 'currency':
                return Context::getContext()->currency->iso_code;
                break;
            case 'condition':
                return $this->condition;
                break;
            case 'supplier':
                return $this->supplier_name;
                break;
            case 'availability':
                if ($id_product_attribute) {
                    $quantity = self::getRealQuantity($this->id, $id_product_attribute);
                } else {
                    $quantity = self::getRealQuantity($this->id);
                }
                if ($quantity <= 0 && !$this->isAvailableWhenOutOfStock($this->out_of_stock)) {
                    return 0;
                }
                return 1;
                break;
            default:
                if (isset($this->features[$name])) {
                    return $this->features[$name]['value'];
                } elseif (!is_null($id_product_attribute) && isset($this->combinations[$id_product_attribute]['attributes'][$name][1])) {
                    return $this->combinations[$id_product_attribute]['attributes'][$name][1];
                } else {
                    return '';
                }
                break;

        }
        if (preg_match('`image_([0-9]+)`', $name, $out)) {
            if ($id_product_attribute) {
                $id_image = $out[1] - 1;
                $attribute_images = $this->getCombinationImages($this->id_lang);
                if (is_array($attribute_images) && array_key_exists($id_product_attribute, $attribute_images)) {
                    if (isset($attribute_images[$id_product_attribute][$id_image]['id_image'])) {
                        return Context::getContext()->link->getImageLink($this->link_rewrite,
                            $this->id . '-' . $attribute_images[$id_product_attribute][$id_image]['id_image'],
                            LengowMain::getImageFormat());
                    } else {
                        return '';
                    }
                }
            }
            return isset($this->images[$out[1] - 2]) ? Context::getContext()->link->getImageLink($this->link_rewrite,
                $this->id . '-' . $this->images[$out[1] - 2]['id_image'], LengowMain::getImageFormat()) : '';
        }
        if (isset($this->{$name})) {
            return $this->{$name};
        }
    }

    /**
     * Clear data cache.
     */
    public static function clear()
    {
        self::$_taxCalculationMethod = null;
        self::$_prices = array();
        self::$_pricesLevel2 = array();
        self::$_incat = array();
        self::$_cart_quantity = array();
        self::$_tax_rules_group = array();
        self::$_cacheFeatures = array();
        self::$_frontFeaturesCache = array();
        self::$producPropertiesCache = array();
        if (_PS_VERSION_ >= '1.5') {
            self::$cacheStock = array();
        }
    }

    /**
     * Get data attribute of current product.
     *
     * @param integer $id_product_attribute the id product atrribute
     * @param string $name the data name attribute
     *
     * @return varchar The data.
     */
    public function getDataAttribute($id_product_attribute, $name)
    {
        return isset($this->combinations[$id_product_attribute]['attributes'][$name][1]) ? $this->combinations[$id_product_attribute]['attributes'][$name][1] : '';
    }

    /**
     * Get data feature of current product.
     *
     * @param string $name the data name feature
     *
     * @return varchar The data.
     */
    public function getDataFeature($name)
    {
        return isset($this->features[$name]['value']) ? $this->features[$name]['value'] : '';
    }

    /**
     * Get the products to export.
     *
     * @param boolean $all Export selected products
     * @param boolean $showInactiveProduct Export inactive products
     * @param array $product_ids Products to export
     * @param boolean $out_of_stock Export out of stock products
     * @param integer $start Last id exported
     *
     * @return varchar IDs product.
     */
    public static function exportIds(
        $exportLengowSelection = false,
        $showInactiveProduct = false,
        $product_ids = null,
        $out_of_stock = false,
        $start = null
    ) {
        $context = Context::getContext();
        $id_lang = $context->language->id;
        $id_shop = $context->shop->id;
        $selected_products_sql = '';

        if ($exportLengowSelection == true) {
            $selected_products_sql = 'AND p.`id_product` IN ('
                . 'SELECT `id_product` FROM `' . _DB_PREFIX_ . 'lengow_product` '
                . 'WHERE `id_shop` = ' . (int)$id_shop . ' )';
        }
        if (LengowMain::compareVersion() < 0) {
            $query = 'SELECT p.`id_product` '
                . 'FROM `' . _DB_PREFIX_ . 'product` p ';
            if ($showInactiveProduct == false) {
                $query .= 'WHERE pl.`id_lang` = ' . (int)$id_lang . ' ';
            } else {
                $query .= 'WHERE pl.`id_lang` = ' . (int)$id_lang . ' AND p.`active` = 1 ';
            }
            $query .= $selected_products_sql;
        } else {
            $query = 'SELECT p.id_product '
                . 'FROM ' . _DB_PREFIX_ . 'product p ';
            if (LengowMain::compareVersion() == 1) {
                $query .= 'LEFT JOIN ' . _DB_PREFIX_ . 'product_shop ps ON p.id_product=ps.id_product ';
            }

            // Add Lengow selected products
            if (LengowMain::compareVersion() == 1 && $id_shop != '') {
                if ($showInactiveProduct == false) {
                    $query .= ' WHERE ps.id_shop = ' . (int)$id_shop . ' AND ps.active=1 ';
                } //AND psupp.id_product_attribute=0 ';
                else {
                    $query .= ' WHERE ps.id_shop = ' . (int)$id_shop . ' ';
                } //AND psupp.id_product_attribute=0 ';
            } else {
                if ($showInactiveProduct == false) {
                    $query .= ' WHERE p.active=1 ';
                } else {
                    $query .= ' WHERE 1 ';
                }
            }
            // Add Lengow selected products
            $query .= $selected_products_sql;
        }

        if ($product_ids != null) {
            $query .= ' AND p.`id_product` IN (' . implode(',', $product_ids) . ')';
        }

        if ($start != null) {
            $query .= ' AND p.`id_product` > ' . (int)$start;
        }

        if (!$out_of_stock) {
            if (_PS_VERSION_ >= '1.5') {
                $query .= ' AND p.`id_product` IN (SELECT sa.`id_product`FROM ' . _DB_PREFIX_ . 'stock_available sa WHERE `quantity` > 0)';
            } else {
                $query .= ' AND p.`quantity` > 0';
            }
        }
        return Db::getInstance()->executeS($query);
    }

    /**
     * Make the feature of current product
     *
     * @param object $context The given context
     */
    public function makeFeatures($context)
    {
        $features = $this->getFrontFeatures($context->language->id);
        if ($features) {
            foreach ($features as $feature) {
                $this->features[$feature['name']] = $feature;
            }
        }
    }

    /**
     * Get features of current product
     *
     * @return array All features.
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * Make the attributes of current product
     *
     * @param object $context The given context
     */
    public function makeAttributes($context)
    {
        $color_by_default = '#BDE5F8';
        $combinations = $this->getAttributesGroups($context->language->id);
        $groups = array();
        $comb_array = array();
        if (is_array($combinations)) {
            $combination_images = $this->getCombinationImages($context->language->id);
            foreach ($combinations as $k => $combination) {
                $k = $k;
                $price_to_convert = Tools::convertPrice($combination['price'], $context->currency);
                $price = Tools::displayPrice($price_to_convert, $context->currency);
                $comb_array[$combination['id_product_attribute']]['id_product_attribute'] = $combination['id_product_attribute'];
                $comb_array[$combination['id_product_attribute']]['attributes'][$combination['group_name']] = array(
                    $combination['group_name'],
                    $combination['attribute_name'],
                    $combination['id_attribute']
                );
                $comb_array[$combination['id_product_attribute']]['wholesale_price'] = isset($combination['wholesale_price']) ? $combination['wholesale_price'] : '';
                $comb_array[$combination['id_product_attribute']]['price'] = $price;
                $comb_array[$combination['id_product_attribute']]['ecotax'] = isset($combination['ecotax']) ? $combination['ecotax'] : '';
                $comb_array[$combination['id_product_attribute']]['weight'] = $combination['weight'] . Configuration::get('PS_WEIGHT_UNIT');
                $comb_array[$combination['id_product_attribute']]['unit_impact'] = $combination['unit_price_impact'];
                $comb_array[$combination['id_product_attribute']]['reference'] = $combination['reference'];
                $comb_array[$combination['id_product_attribute']]['ean13'] = isset($combination['ean13']) ? $combination['ean13'] : '';
                $comb_array[$combination['id_product_attribute']]['upc'] = isset($combination['upc']) ? $combination['upc'] : '';
                $comb_array[$combination['id_product_attribute']]['supplier_reference'] = isset($combination['supplier_reference']) ? $combination['supplier_reference'] : '';
                $comb_array[$combination['id_product_attribute']]['id_image'] = isset($combination_images[$combination['id_product_attribute']][0]['id_image']) ? $combination_images[$combination['id_product_attribute']][0]['id_image'] : 0;
                if (LengowMain::compareVersion()) {
                    $comb_array[$combination['id_product_attribute']]['available_date'] = strftime($combination['available_date']);
                }
                $comb_array[$combination['id_product_attribute']]['default_on'] = $combination['default_on'];
                if ($combination['is_color_group']) {
                    $groups[$combination['id_attribute_group']] = $combination['group_name'];
                }
            }
        }
        if (isset($comb_array)) {
            foreach ($comb_array as $id_product_attribute => $product_attribute) {
                $list = '';
                $name = '';
                /* In order to keep the same attributes order */
                asort($product_attribute['attributes']);
                foreach ($product_attribute['attributes'] as $attribute) {
                    $list .= $attribute[0] . ' - ' . $attribute[1] . ', ';
                    $name .= $attribute[0] . ',';
                }
                $list = rtrim($list, ', ');
                // $name = rtrim($name, ', ');
                $comb_array[$id_product_attribute]['image'] = $product_attribute['id_image'] ? new Image($product_attribute['id_image']) : false;
                if (LengowMain::compareVersion()) {
                    $comb_array[$id_product_attribute]['available_date'] = $product_attribute['available_date'] != 0 ? date('Y-m-d',
                        strtotime($product_attribute['available_date'])) : '0000-00-00';
                }
                $comb_array[$id_product_attribute]['attribute_name'] = $list;
                $comb_array[$id_product_attribute]['name'] = $name;
                if ($product_attribute['default_on']) {
                    $comb_array[$id_product_attribute]['name'] = 'is_default';
                    $comb_array[$id_product_attribute]['color'] = $color_by_default;
                }
                if (!$this->variation) {
                    $this->variation = $name;
                }
            }
        }
        $this->combinations = $comb_array;
    }

    /**
     * Get combinations of current product
     *
     * @return array All combinations.
     */
    public function getCombinations()
    {
        return $this->combinations;
    }

    /**
     * Get count images of current product
     *
     * @return integer The number of images.
     */
    public function getCountImages()
    {
        return count($this->images);
    }

    /**
     * OVERRIDE NATIVE FONCTION : add supplier_reference, ean13, upc, wholesale_price and ecotax
     * Get all available attribute groups
     *
     * @param integer $id_lang Language id
     * @return array Attribute groups
     */
    public function getAttributesGroups($id_lang)
    {
        if (LengowMain::compareVersion()) {
            if (!Combination::isFeatureActive()) {
                return array();
            }
            $sql = 'SELECT ag.`id_attribute_group`, ag.`is_color_group`, agl.`name` AS group_name, agl.`public_name` AS public_group_name,
						a.`id_attribute`, al.`name` AS attribute_name, a.`color` AS attribute_color, pa.`id_product_attribute`,
						IFNULL(stock.quantity, 0) as quantity, product_attribute_shop.`price`, product_attribute_shop.`ecotax`, pa.`weight`,
						product_attribute_shop.`default_on`, pa.`reference`, product_attribute_shop.`unit_price_impact`,
						pa.`minimal_quantity`, pa.`available_date`, ag.`group_type`, ps.`product_supplier_reference` AS `supplier_reference`, pa.`ean13`, pa.`upc`, pa.`wholesale_price`, pa.`ecotax`
					FROM `' . _DB_PREFIX_ . 'product_attribute` pa
					' . Shop::addSqlAssociation('product_attribute', 'pa') . '
					' . Product::sqlStock('pa', 'pa') . '
					LEFT JOIN `' . _DB_PREFIX_ . 'product_supplier` ps ON (ps.`id_product_attribute` = pa.`id_product_attribute` AND ps.`id_product` = ' . (int)$this->id . ')
					LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
					LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
					LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
					LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON a.`id_attribute` = al.`id_attribute`
					LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON ag.`id_attribute_group` = agl.`id_attribute_group`
					' . Shop::addSqlAssociation('attribute', 'a') . '
					WHERE pa.`id_product` = ' . (int)$this->id . '
						AND al.`id_lang` = ' . (int)$id_lang . '
						AND agl.`id_lang` = ' . (int)$id_lang . '
					GROUP BY id_attribute_group, id_product_attribute
					ORDER BY ag.`position` ASC, a.`position` ASC, agl.`name` ASC';
        } else {
            $sql = 'SELECT ag.`id_attribute_group`, ag.`is_color_group`, agl.`name` group_name, agl.`public_name` public_group_name, a.`id_attribute`, al.`name` attribute_name,
					a.`color` attribute_color, pa.*
					FROM `' . _DB_PREFIX_ . 'product_attribute` pa
					LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON (pac.`id_product_attribute` = pa.`id_product_attribute`)
					LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON (a.`id_attribute` = pac.`id_attribute`)
					LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON (ag.`id_attribute_group` = a.`id_attribute_group`)
					LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute`)
					LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group`)
					WHERE pa.`id_product` = ' . (int)$this->id . ' AND al.`id_lang` = ' . (int)$id_lang . ' AND agl.`id_lang` = ' . (int)$id_lang . '
					ORDER BY agl.`public_name`, al.`name`';
        }
        //echo "**********".$sql."**********";
        return Db::getInstance()->executeS($sql);
    }

    /**
     * Publis or unpublish to Lengow.
     *
     * @param integer $id_product the id product
     * @param integer $status 1 : publish, 0 : unpublish
     * @param integer $id_lang the id lang
     * @param integer $id_product the id shop
     *
     * @return boolean.
     */
    public static function publish($id_product, $status = 1, $id_lang = null, $id_shop = null)
    {
        $context = Context::getContext();
        if (empty($id_lang)) {
            $id_lang = $context->language->id;
        }
        if (empty($id_shop)) {
            $id_shop = $context->shop->id;
        }
        $id_shop_group = $context->shop->id_shop_group;
        if ($status == 1) {
            $select = 'SELECT COUNT(`id_product`) FROM `' . _DB_PREFIX_ . 'lengow_product` WHERE `id_product`= ' . (int)$id_product . ';';
            $count = Db::getInstance()->getValue($select);
            if ($count == 1) {
                return true;
            } else {
                return Db::getInstance()->autoExecute(_DB_PREFIX_ . 'lengow_product', array(
                    'id_product' => (int)$id_product,
                    'id_shop' => (int)$id_shop,
                    'id_shop_group' => (int)$id_shop_group,
                    'id_lang' => (int)$id_lang,
                ), 'INSERT');
            }
        } elseif ($status == 0) {
            return Db::getInstance()->delete(_DB_PREFIX_ . 'lengow_product', 'id_product = ' . (int)$id_product, 1);
        }
    }

    /**
     * For a given product, returns its real quantity
     *
     * @param int $id_product
     * @param int $id_product_attribute
     * @param int $id_warehouse
     * @param int $id_shop
     * @return int real_quantity
     */
    public static function getRealQuantity(
        $id_product,
        $id_product_attribute = 0,
        $id_warehouse = null,
        $id_shop = null
    )
    {
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            if ($id_product_attribute == 0 || $id_product_attribute == null) {
                return Product::getQuantity($id_product);
            }
            return Product::getQuantity($id_product, $id_product_attribute);
        } else {
            return parent::getRealQuantity($id_product, $id_product_attribute, $id_warehouse, $id_shop);
        }
    }

    /**
     * Get max number of images
     *
     * @return int max number of images for one product
     */
    public static function getMaxImages()
    {
        if (_PS_VERSION_ >= '1.5') {
            $sql = 'SELECT COUNT(i.`id_image`) AS `total`
					FROM `' . _DB_PREFIX_ . 'image` i
					' . Shop::addSqlAssociation('image', 'i') . '
					GROUP BY i.`id_product`
					ORDER BY `total` DESC';
            $count = Db::getInstance()->getRow($sql);
            return $count['total'];
        } else {
            $sql = 'SELECT COUNT(i.`id_image`) AS `total`
					FROM `' . _DB_PREFIX_ . 'image` i
					GROUP BY i.`id_product`
					ORDER BY `total` DESC';
            $count = Db::getInstance()->getRow($sql);
            return $count['total'];
        }
    }


    /**
     * Compares found id with API ids and checks if they match
     *
     * @return boolean if valid or not
     */
    protected static function isValidId($product, $api_ids)
    {
        $attributes = array('reference', 'ean13', 'upc', 'id');
        if (count($product->getCombinations()) > 0) {
            foreach ($product->getCombinations() as $combination) {
                foreach ($attributes as $attribute_name) {
                    foreach ($api_ids as $api_id) {
                        if (!empty($api_id)) {
                            if ($attribute_name == 'id') {
                                $id = str_replace('\_', '_', $api_id);
                                $id = str_replace('X', '_', $api_id);
                                $ids = explode('_', $id);
                                $id = $ids[0];
                                if (is_numeric($id) && $product->{$attribute_name} == $id) {
                                    return true;
                                }
                            } elseif ($combination[$attribute_name] === $api_id) {
                                return true;
                            }
                        }
                    }
                }
            }
        } else {
            foreach ($attributes as $attribute_name) {
                foreach ($api_ids as $api_id) {
                    if (!empty($api_id)) {
                        if ($attribute_name == 'id') {
                            $id = str_replace('\_', '_', $api_id);
                            $id = str_replace('X', '_', $api_id);
                            $ids = explode('_', $id);
                            $id = $ids[0];
                            if (is_numeric($id) && $product->{$attribute_name} == $id) {
                                return true;
                            }
                        }
                        if ($product->{$attribute_name} === $api_id) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Extract cart data from API
     *
     * @return array
     */
    public static function extractProductDataFromAPI($api)
    {
        $temp = array();
        foreach (LengowProduct::$PRODUCT_API_NODES as $node) {
            $temp[$node] = (string)$api->{$node};
        }
        return $temp;
    }

    /**
     * Retrieves the product sku
     *
     * @param string $attribute_name
     * @param string $attribute_value
     * @param array $api_data
     *
     * @return mixes
     */
    public static function matchProduct($attribute_name, $attribute_value, $id_shop, $api_data = array())
    {
        if (empty($attribute_value) || empty($attribute_name)) {
            return false;
        }

        switch (Tools::strtolower($attribute_name)) {
            case 'reference_product':
            case 'reference':
                return LengowProduct::findProduct('reference', $attribute_value, $id_shop);
            case 'ean':
                return LengowProduct::findProduct('ean13', $attribute_value, $id_shop);
            case 'upc':
                return LengowProduct::findProduct('upc', $attribute_value, $id_shop);
            default:
                $product_ids = array();
                $sku = str_replace('\_', '_', $attribute_value);
                $sku = str_replace('X', '_', $sku);
                $sku = explode('_', $sku);
                $product_ids['id_product'] = $sku[0];
                if (isset($sku[1])) {
                    $product_ids['id_product_attribute'] = $sku[1];
                }
                $id_bool = LengowProduct::checkProductId($product_ids['id_product'], $api_data);

                $id_att_bool = true;
                if (isset($product_ids['id_product_attribute'])) {
                    $id_att_bool = LengowProduct::checkProductAttributeId(new LengowProduct($product_ids['id_product']),
                        $product_ids['id_product_attribute']);
                }

                if ($id_bool && $id_att_bool) {
                    return $product_ids;
                }
                return false;
        }
    }

    /**
     * Check if product id found is correct
     * @param  integer $product_id product id to be checked
     * @param array $api_ids product ids from the API
     *
     * @return bool
     */
    protected static function checkProductId($product_id, $api_ids)
    {
        if (empty($product_id)) {
            return false;
        }
        $product = new LengowProduct($product_id);
        if ($product->name == '' || !self::isValidId($product, $api_ids)) {
            return false;
        }
        return true;
    }

    /**
     * Check if the product attribute exists
     * @param integer $product
     * @param integer $product_attribute_id
     *
     * @return boolean
     */
    protected static function checkProductAttributeId($product, $product_attribute_id)
    {
        if ($product_attribute_id != 0) {
            if (!array_key_exists($product_attribute_id, $product->getCombinations())) {
                return false;
            }
        }
        return true;
    }


    /**
     * Return the product and its attribute ids
     *
     * @param string $key
     * @param string $value
     *
     * @return int
     */
    protected static function findProduct($key, $value, $id_shop)
    {
        if (empty($key) || empty($value)) {
            return false;
        }
        if (_PS_VERSION_ >= '1.5') {
            $query = new DbQuery();
            $query->select('p.id_product');
            $query->from('product', 'p');
            $query->innerJoin('product_shop', 'ps', 'p.id_product = ps.id_product');
            $query->where('p.' . pSQL($key) . ' = \'' . pSQL($value) . '\'');
            $query->where('ps.`id_shop` = \'' . (int)$id_shop . '\'');
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);

            // If no result, search in attribute
            if ($result == '') {
                $query = new DbQuery();
                $query->select('pa.id_product, pa.id_product_attribute');
                $query->from('product_attribute', 'pa');
                $query->innerJoin('product_shop', 'ps', 'pa.id_product = pa.id_product');
                $query->where('pa.' . pSQL($key) . ' = \'' . pSQL($value) . '\'');
                $query->where('ps.`id_shop` = \'' . (int)$id_shop . '\'');
                $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
            }
        } else {
            $sql = 'SELECT p.`id_product`
				FROM `' . _DB_PREFIX_ . 'product` p
				INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps
				ON p.id_product = ps.id_product
				WHERE p.`' . pSQL($key) . '` = \'' . pSQL($value) . '\'
				AND ps.`id_shop` = \'' . (int)$id_shop . '\'';
            $result = Db::getInstance()->getRow($sql);

            if ($result == '') {
                $sql = 'SELECT pa.`id_product`, pa.`id_product_attribute`
					FROM `' . _DB_PREFIX_ . 'product_attribute` pa
					INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps
					WHERE pa.`' . pSQL($key) . '` = \'' . pSQL($value) . '\'
					AND ps.`id_shop` = \'' . (int)$id_shop . '\'';
                $result = Db::getInstance()->getRow($sql);
            }
        }
        return $result;
    }

    /**
     * Search a product by its reference, ean, upc and id
     *
     * @param type $args
     *
     * @return type
     */
    public static function advancedSearch($attribute_value, $id_shop, $api_data)
    {
        $attributes = array('reference', 'ean', 'upc', 'ids'); // Product class attribute to search
        $product_ids = array();
        $find = false;
        $i = 0;
        $count = count($attributes);
        while (!$find && $i < $count) {
            $product_ids = self::matchProduct($attributes[$i], $attribute_value, $id_shop, $api_data);
            if (!empty($product_ids)) {
                $find = true;
            }
            $i++;
        }
        if ($find) {
            return $product_ids;
        }
    }

    /**
     * Calculate product without taxes using TaxManager
     *
     * @param array $product product
     * @param int $id_address address id used to get tax rate
     * @param Context $context order context
     *
     * @return float
     */
    public static function calculatePriceWithoutTax($product, $id_address, $context)
    {
        $tax_address = new LengowAddress((int)$id_address);
        if (_PS_VERSION_ >= '1.5') {
            $tax_manager = TaxManagerFactory::getManager($tax_address,
                Product::getIdTaxRulesGroupByIdProduct((int)$product['id_product'], $context));
            $tax_calculator = $tax_manager->getTaxCalculator();
            return $tax_calculator->removeTaxes($product['price_wt']);
        } else {
            $rate = Tax::getProductTaxRate((int)$product['id_product'], (int)$id_address);
            return $product['price_wt'] / (1 + $rate / 100);
        }
    }

}
