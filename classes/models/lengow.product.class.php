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
 * The Lengow Product Class
 */
class LengowProduct extends Product
{

    /**
     * array API nodes containing relevant data
     */
    public static $PRODUCT_API_NODES = array(
        'marketplace_product_id',
        'marketplace_status',
        'merchant_product_id',
        'marketplace_order_line_id',
        'quantity',
        'amount'
    );

    //current context
    protected $context;

    // product images
    protected $images;
    protected $imageCombinations;
    protected $imageSize;
    protected $cover;
    //default category
    protected $categoryDefault;
    protected $categoryDefaultName;
    //is product in sale
    protected $isSale = false;

    /**
     * Array combination of product's attributes.
     */
    protected $combinations = null;

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
     * @param object $params The context
     */
    public function __construct($id_product = null, $id_lang = null, $params = array())
    {

        $this->carrier = isset($params["carrier"]) ? $params["carrier"] : null;
        $this->imageSize = isset($params["image_size"]) ? $params["image_size"] : self::getMaxImageType();

        parent::__construct($id_product, false, $id_lang);
        $this->context = Context::getContext();
        $this->context->language = isset($params["language"]) ? $params["language"] : Context::getContext()->language;

        // The applicable tax may be BOTH the product one AND the state one (moreover this variable is some deadcode)
        $this->tax_name = 'deprecated';

        $this->manufacturer_name = Manufacturer::getNameById((int)$this->id_manufacturer);
        $this->supplier_name = Supplier::getNameById((int)$this->id_supplier);
        $address = null;
        if (is_object($this->context->cart) && $this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')} != null) {
            $address = $this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
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
        if ($this->id_category_default && $this->id_category_default > 17) {
            $this->categoryDefault = new Category((int)$this->id_category_default, $id_lang);
            $this->categoryDefaultName = $this->categoryDefault->name;
        } else {
            $categories = self::getProductCategories($this->id);
            if (!empty($categories)) {
                $this->categoryDefault = new Category($categories[0], $id_lang);
                $this->categoryDefaultName = $this->categoryDefault->name;
            }
        }
        $this->images = $this->getImages($id_lang);
        $today = date('Y-m-d H:i:s');
        if (isset($this->specificPrice) && is_array($this->specificPrice)) {
            if (array_key_exists('from', $this->specificPrice) && array_key_exists('to', $this->specificPrice)) {
                if ($this->specificPrice['from'] <= $today && $today <= $this->specificPrice['to']) {
                    $this->isSale = true;
                }
            }
        }
        $this->makeFeatures();
        $this->makeAttributes();
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
                $tmpName = $this->name;
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
                return LengowMain::cleanData($this->reference);
                break;
            case 'supplier_reference':
                if ($id_product_attribute > 1 && $this->combinations[$id_product_attribute]['supplier_reference']) {
                    return $this->combinations[$id_product_attribute]['supplier_reference'];
                }
                return LengowMain::cleanData($this->supplier_reference);
                break;
            case 'manufacturer':
                return LengowMain::cleanData($this->manufacturer_name);
                break;
            case 'category':
                return LengowMain::cleanData($this->categoryDefaultName);
                break;
            case 'breadcrumb':
                if ($this->categoryDefault) {
                    $breadcrumb = '';
                    $categories = $this->categoryDefault->getParentsCategories();
                    foreach ($categories as $category) {
                        $breadcrumb = $category['name'] . ' > ' . $breadcrumb;
                    }
                    return rtrim($breadcrumb, ' > ');
                }
                return LengowMain::cleanData($this->categoryDefaultName);
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
                if ($id_product_attribute && $this->combinations[$id_product_attribute]['ean13']) {
                    return $this->combinations[$id_product_attribute]['ean13'];
                }
                return $this->ean13;
                break;
            case 'upc':
                if ($id_product_attribute && $this->combinations[$id_product_attribute]['upc']) {
                    return $this->combinations[$id_product_attribute]['upc'];
                }
                return $this->upc;
                break;
            case 'ecotax':
                if ($id_product_attribute && $this->combinations[$id_product_attribute]['ecotax']) {
                    return LengowMain::formatNumber($this->combinations[$id_product_attribute]['ecotax']);
                }
                if (isset($this->ecotaxinfos)) {
                    return LengowMain::formatNumber(($this->ecotaxinfos > 0) ? $this->ecotaxinfos : $this->ecotax);
                } else {
                    return $this->ecotax;
                }
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
                        return $this->context->link->getProductLink(
                            $this,
                            null,
                            null,
                            null,
                            null,
                            null,
                            $id_product_attribute,
                            true
                        );
                    }
                    return $this->context->link->getProductLink(
                        $this,
                        null,
                        null,
                        null,
                        null,
                        null,
                        $id_product_attribute
                    );
                }
                return $this->context->link->getProductLink($this);
                break;
            case 'price_shipping':
                if ($id_product_attribute && $id_product_attribute != null) {
                    $price = $this->getData('price_sale', $id_product_attribute);
                    $weight = $this->getData('weight', $id_product_attribute);
                } else {
                    $price = $this->getData('price_sale');
                    $weight = $this->getData('weight');
                }
                $id_zone =  $this->context->country->id_zone;
                $id_currency =  $this->context->cart->id_currency;
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
                return $this->carrier->delay[$this->context->language->id];
                break;
            case 'sale_from':
                return $this->isSale ? $this->specificPrice['from'] : '';
                break;
            case 'sale_to':
                return $this->isSale ? $this->specificPrice['to'] : '';
                break;
            case 'meta_keywords':
                return LengowMain::cleanData($this->meta_keywords);
                break;
            case 'meta_description':
                return LengowMain::cleanData($this->meta_description);
                break;
            case 'url_rewrite':
                if (version_compare(_PS_VERSION_, '1.4', '>')) {
                    return $this->context->link->getProductLink(
                        $this,
                        $this->link_rewrite,
                        null,
                        null,
                        null,
                        null,
                        $id_product_attribute
                    );
                }
                return $this->context->link->getProductLink($this, $this->link_rewrite);
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
            //speed up export
            case 'image_1':
            case 'image_2':
            case 'image_3':
            case 'image_4':
            case 'image_5':
            case 'image_6':
            case 'image_7':
            case 'image_8':
            case 'image_9':
            case 'image_10':
                //speed up export
                switch ($name) {
                    case 'image_1':
                        $id_image = 0;
                        break;
                    case 'image_2':
                        $id_image = 1;
                        break;
                    case 'image_3':
                        $id_image = 2;
                        break;
                    case 'image_4':
                        $id_image = 3;
                        break;
                    case 'image_5':
                        $id_image = 4;
                        break;
                    case 'image_6':
                        $id_image = 5;
                        break;
                    case 'image_7':
                        $id_image = 6;
                        break;
                    case 'image_8':
                        $id_image = 7;
                        break;
                    case 'image_9':
                        $id_image = 8;
                        break;
                    case 'image_10':
                        $id_image = 9;
                        break;
                }
                if ($id_product_attribute) {
                    if (isset($this->combinations[$id_product_attribute]['images'][$id_image])) {
                        return $this->combinations[$id_product_attribute]['images'][$id_image];
                    }
                    return '';
                }
                return isset($this->images[$id_image]) ? $this->context->link->getImageLink(
                    $this->link_rewrite,
                    $this->id . '-' . $this->images[$id_image]['id_image'],
                    $this->imageSize
                ) : '';
                break;
            default:
                if (isset($this->features[$name])) {
                    return $this->features[$name]['value'];
                } elseif (!is_null($id_product_attribute) &&
                    isset($this->combinations[$id_product_attribute]['attributes'][$name][1])) {
                    return $this->combinations[$id_product_attribute]['attributes'][$name][1];
                } else {
                    return '';
                }
                break;
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
     * Make the feature of current product
     *
     */
    public function makeFeatures()
    {
        $features = $this->getFrontFeatures($this->context->language->id);
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
     */
    public function makeAttributes()
    {
        $color_by_default = '#BDE5F8';
        $combinations = $this->getAttributesGroups($this->context->language->id);
        $groups = array();
        $comb_array = array();

        if (is_array($combinations)) {
            $cImages = $this->getImageUrlCombination();
            foreach ($combinations as $k => $c) {
                $attributeId = $c['id_product_attribute'];
                $price_to_convert = Tools::convertPrice($c['price'], $this->context->currency);
                $price = Tools::displayPrice($price_to_convert, $this->context->currency);
                $comb_array[$attributeId] = array(
                    'id_product_attribute' => $attributeId,
                    'attributes' => array($c['group_name'] => array(
                        $c['group_name'],
                        $c['attribute_name'],
                        $c['id_attribute']
                    )),
                    'wholesale_price' => isset($c['wholesale_price']) ? $c['wholesale_price'] : '',
                    'price' => $price,
                    'ecotax' =>  isset($c['ecotax']) ? $c['ecotax'] : '',
                    'weight' => $c['weight'] . Configuration::get('PS_WEIGHT_UNIT'),
                    'unit_impact' => $c['unit_price_impact'],
                    'reference' => $c['reference'],
                    'ean13' => isset($c['ean13']) ? $c['ean13'] : '',
                    'upc' => isset($c['upc']) ? $c['upc'] : '',
                    'supplier_reference' => isset($c['supplier_reference']) ? $c['supplier_reference'] : '',
                    'images' => isset($cImages[$attributeId]) ? $cImages[$attributeId] : array(),
                    'default_on' => $c['default_on'],
                );
                if (LengowMain::compareVersion()) {
                    $comb_array[$attributeId]['available_date'] = strftime($c['available_date']);
                }
                if ($c['is_color_group']) {
                    $groups[$attributeId] = $c['group_name'];
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
     * v3-test
     * Publish or Un-publish to Lengow.
     *
     * @param integer $productId the id product
     * @param integer $value 1 : publish, 0 : unpublish
     * @param integer $shopId the id shop
     *
     * @return boolean.
     */
    public static function publish($productId, $value, $shopId)
    {
        if (!$value) {
            $sql = 'DELETE FROM '._DB_PREFIX_.'lengow_product
             WHERE id_product = '.(int)$productId.' AND id_shop = '.$shopId;
            Db::getInstance()->Execute($sql);
        } else {
            $sql = 'SELECT id_product FROM '._DB_PREFIX_.'lengow_product
            WHERE id_product = '.(int)$productId.' AND id_shop = '.$shopId;
            $results = Db::getInstance()->ExecuteS($sql);
            if (count($results) == 0) {
                if (_PS_VERSION_ < '1.5') {
                    Db::getInstance()->autoExecute(_DB_PREFIX_.'lengow_product', array(
                        'id_product' => $productId,
                        'id_shop' => $shopId
                    ), 'INSERT');
                } else {
                    Db::getInstance()->Insert('lengow_product', array(
                        'id_product' => $productId,
                        'id_shop' => $shopId
                    ));
                }
            }
        }
        return true;
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
    ) {
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
     * @param mixed $api
     *
     * @return array
     */
    public static function extractProductDataFromAPI($api)
    {
        $temp = array();
        foreach (LengowProduct::$PRODUCT_API_NODES as $node) {
            $temp[$node] = $api->{$node};
        }
        $temp['price_unit'] = (float)$temp['amount'] / (float)$temp['quantity'];
        return $temp;
    }

    /**
     * Retrieves the product sku
     *
     * @param string $attribute_name
     * @param string $attribute_value
     * @param array  $api_data
     *
     * @return mixed
     */
    public static function matchProduct($attribute_name, $attribute_value, $id_shop, $api_data = array())
    {
        if (empty($attribute_value) || empty($attribute_name)) {
            return false;
        }

        switch (Tools::strtolower($attribute_name)) {
            case 'reference':
                return LengowProduct::findProduct('reference', $attribute_value, $id_shop);
                break;
            case 'ean':
                return LengowProduct::findProduct('ean13', $attribute_value, $id_shop);
                break;
            case 'upc':
                return LengowProduct::findProduct('upc', $attribute_value, $id_shop);
                break;
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
                    $id_att_bool = LengowProduct::checkProductAttributeId(
                        new LengowProduct($product_ids['id_product']),
                        $product_ids['id_product_attribute']
                    );
                }

                if ($id_bool && $id_att_bool) {
                    return $product_ids;
                }
                return false;
                break;
        }
    }

    /**
     * Check if product id found is correct
     *
     * @param integer   $product_id product id to be checked
     * @param array     $api_ids    product ids from the API
     *
     * @return boolean
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
     *
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
     * @param string    $key
     * @param string    $value
     * @param integer   $id_shop
     *
     * @return integer
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
     * @param type $attribute_value
     * @param type $id_shop
     * @param type $api_data
     *
     * @return array
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
     * @param array     $product    product
     * @param int       $id_address address id used to get tax rate
     * @param Context   $context    order context
     *
     * @return float
     */
    public static function calculatePriceWithoutTax($product, $id_address, $context)
    {
        $tax_address = new LengowAddress((int)$id_address);
        if (_PS_VERSION_ >= '1.5') {
            $tax_manager = TaxManagerFactory::getManager(
                $tax_address,
                Product::getIdTaxRulesGroupByIdProduct((int)$product['id_product'], $context)
            );
            $tax_calculator = $tax_manager->getTaxCalculator();
            return $tax_calculator->removeTaxes($product['price_wt']);
        } else {
            $rate = Tax::getProductTaxRate((int)$product['id_product'], (int)$id_address);
            return $product['price_wt'] / (1 + $rate / 100);
        }
    }


    /**
     * v3-test
     * get image url of product variations
     *
     * @return mixed false or attribute image collection
     */
    public function getImageUrlCombination()
    {
        $cImages = array();
        $psImages = $this->getCombinationImages($this->id_lang);
        $maxImage = 10;
        if ($psImages) {
            foreach ($psImages as $productAttributeId => $images) {
                foreach ($images as $image) {
                    if (!isset($cImages[$productAttributeId]) || count($cImages[$productAttributeId]) < $maxImage) {
                        $cImages[$productAttributeId][] =
                            $this->context->link->getImageLink(
                                $this->link_rewrite,
                                $this->id . '-' . $image['id_image'],
                                $this->imageSize
                            );
                    }
                }
            }
            return $cImages;
        }
        return false;
    }

    /**
     * v3-test
     * Get Max Image Type
     *
     * @throws LengowExportException
     * @return string
     */
    public static function getMaxImageType()
    {
        $sql = 'SELECT name FROM '._DB_PREFIX_.'image_type WHERE products = 1 ORDER BY width DESC';
        $result = Db::getInstance()->executeS($sql);
        if ($result) {
            return $result[0]['name'];
        } else {
            throw new LengowExportException('Cant find Image type size, check your table ps_image_type');
        }
    }
}
