<?php
/**
 * Copyright 2021 Lengow SAS.
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
 * @copyright 2021 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */
/*
 * Lengow Product Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class LengowProduct extends Product
{
    /**
     * @var string Lengow product table name
     */
    public const TABLE_PRODUCT = 'lengow_product';

    /* Product fields */
    public const FIELD_ID = 'id';
    public const FIELD_PRODUCT_ID = 'id_product';
    public const FIELD_SHOP_ID = 'id_shop';

    /**
     * @var array API nodes containing relevant data
     */
    public static $productApiNodes = [
        'marketplace_product_id',
        'marketplace_status',
        'merchant_product_id',
        'marketplace_order_line_id',
        'quantity',
        'amount',
    ];

    /**
     * @var Context PrestaShop context instance
     */
    protected $context;

    /**
     * @var array product images
     */
    protected $images;

    /**
     * @var string image size
     */
    protected $imageSize;

    /**
     * @var Category PrestaShop category instance
     */
    protected $categoryDefault;

    /**
     * @var string name of the default category
     */
    protected $categoryDefaultName;

    /**
     * @var bool is product in sale
     */
    protected $isSale;

    /**
     * @var array|null combination of product's attributes
     */
    protected $combinations;

    /**
     * @var array product's features
     */
    protected $features;

    /**
     * @var Carrier PrestaShop carrier instance
     */
    protected $carrier;

    /**
     * @var string all product variations
     */
    protected $variation;

    /**
     * Load a new product
     *
     * @param int|null $idProduct PrestaShop product id
     * @param int|null $idLang PrestaShop lang id
     * @param array $params all export parameters
     *
     * @throws Exception|LengowException
     */
    public function __construct($idProduct = null, $idLang = null, $params = [])
    {
        parent::__construct($idProduct, false, $idLang);
        $this->isSale = false;
        $this->combinations = null;
        $this->carrier = isset($params['carrier']) ? $params['carrier'] : null;
        $this->imageSize = isset($params['image_size']) ? $params['image_size'] : self::getMaxImageType();
        $this->context = Context::getContext();
        $this->context->language = isset($params['language']) ? $params['language'] : Context::getContext()->language;
        // the applicable tax may be BOTH the product one and the state one (moreover this variable is some deadcode)
        $this->tax_name = 'deprecated';
        $this->manufacturer_name = Manufacturer::getNameById((int) $this->id_manufacturer);
        $this->supplier_name = Supplier::getNameById((int) $this->id_supplier);
        $address = null;
        if (is_object($this->context->cart)
            && $this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')} != null
        ) {
            $address = $this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
        }
        $this->tax_rate = $this->getTaxesRate(new Address($address));
        $this->new = $this->isNew();
        $this->base_price = $this->price;
        if ($this->id) {
            $this->price = Product::getPriceStatic(
                (int) $this->id,
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
        $this->loadStockData();
        if ($this->id_category_default && $this->id_category_default > 1) {
            $this->categoryDefault = new Category((int) $this->id_category_default, $idLang);
            $this->categoryDefaultName = $this->categoryDefault->name;
        } else {
            $categories = self::getProductCategories($this->id);
            if (!empty($categories)) {
                $this->categoryDefault = new Category($categories[0], $idLang);
                $this->categoryDefaultName = $this->categoryDefault->name;
            }
        }
        $this->images = $this->getImages($idLang);
        $today = date(LengowMain::DATE_FULL);
        if (isset($this->specificPrice) && is_array($this->specificPrice)
            && array_key_exists('from', $this->specificPrice)
            && array_key_exists('to', $this->specificPrice)
            && $this->specificPrice['from'] <= $today && $today <= $this->specificPrice['to']
        ) {
            $this->isSale = true;
        }
        $this->makeFeatures();
        $this->makeAttributes();
    }

    /**
     * Get data of current product
     *
     * @param string $name data name
     * @param int|null $idProductAttribute PrestaShop product attribute id
     *
     * @return string
     *
     * @throws Exception
     */
    public function getData($name, $idProductAttribute = null)
    {
        switch ($name) {
            case 'id':
                return $idProductAttribute ? $this->id . '_' . $idProductAttribute : $this->id;
            case 'sku':
                return LengowMain::cleanData($this->getProductData('reference', $idProductAttribute));
            case 'sku_supplier':
                return LengowMain::cleanData($this->getSupplierReference($idProductAttribute));
            case 'ean':
                return $this->getProductData('ean13', $idProductAttribute);
            case 'upc':
                return $this->getProductData('upc', $idProductAttribute);
            case 'isbn':
                return $this->getProductData('isbn', $idProductAttribute);
            case 'name':
                return LengowMain::cleanData($this->name);
            case 'quantity':
                return self::getRealQuantity($this->id, $idProductAttribute);
            case 'minimal_quantity':
                return (int) $this->getProductData('minimal_quantity', $idProductAttribute);
            case 'availability':
                return self::getRealQuantity($this->id, $idProductAttribute) <= 0
                    ? $this->available_later
                    : $this->available_now;
            case 'condition':
                return $this->condition;
            case 'category':
                return LengowMain::cleanData($this->getBreadcrumb());
            case 'status':
                return $this->active ? 'Enabled' : 'Disabled';
            case 'url':
                return $this->getProductUrl($idProductAttribute);
            case 'url_rewrite':
                return $this->getProductUrl($idProductAttribute, true);
            case 'price_excl_tax':
                return $this->getPrice(false, $idProductAttribute, 2, null, false, true, 1);
            case 'price_incl_tax':
                return $this->getPrice(true, $idProductAttribute, 2, null, false, true, 1);
            case 'price_before_discount_excl_tax':
                return $this->getPrice(false, $idProductAttribute, 2, null, false, false, 1);
            case 'price_before_discount_incl_tax':
                return $this->getPrice(true, $idProductAttribute, 2, null, false, false, 1);
            case 'price_wholesale':
                return LengowMain::formatNumber($this->getProductData('wholesale_price', $idProductAttribute));
            case 'discount_percent':
                $price = $this->getPrice(true, $idProductAttribute, 2, null, false, false, 1);
                $priceSale = $this->getPrice(true, $idProductAttribute, 2, null, true, true, 1);

                return ($priceSale && $price) ? LengowMain::formatNumber(($priceSale / $price) * 100) : 0;
            case 'discount_start_date':
                return $this->isSale ? $this->specificPrice['from'] : '';
            case 'discount_end_date':
                return $this->isSale ? $this->specificPrice['to'] : '';
            case 'ecotax':
                return LengowMain::formatNumber($this->getEcotaxLengow($idProductAttribute));
            case 'shipping_cost':
                return $this->getShippingCost($idProductAttribute);
            case 'shipping_delay':
                return $this->carrier->delay[$this->context->language->id];
            case 'currency':
                return Context::getContext()->currency->iso_code;
            case (bool) preg_match('`image_([0-9]+)`', $name):
                return $this->getImageLink($name, $idProductAttribute);
            case 'type':
                return $this->getProductTypeLengow($idProductAttribute);
            case 'parent_id':
                return $this->id;
            case 'variation':
                return $this->variation;
            case 'language':
                return $this->context->language->iso_code;
            case 'description':
                return LengowMain::cleanHtml(LengowMain::cleanData($this->description));
            case 'description_html':
                return LengowMain::cleanData($this->description);
            case 'short_description':
                return LengowMain::cleanHtml(LengowMain::cleanData($this->description_short));
            case 'short_description_html':
                return LengowMain::cleanData($this->description_short);
            case 'tags':
                return LengowMain::cleanData($this->getTagList());
            case 'manufacturer':
                return LengowMain::cleanData($this->manufacturer_name);
            case 'supplier':
                return LengowMain::cleanData($this->supplier_name);
            case 'weight':
                return LengowMain::formatNumber($this->getWeight($idProductAttribute));
            case 'weight_unit':
                return Configuration::get('PS_WEIGHT_UNIT');
            case 'available':
                return (self::getRealQuantity($this->id, $idProductAttribute) <= 0
                    && !$this->isAvailableWhenOutOfStock($this->out_of_stock)
                ) ? 0 : 1;
            default:
                if (isset($this->features[$name])) {
                    return LengowMain::cleanData($this->features[$name]['value']);
                }
                if ($idProductAttribute !== null
                    && isset($this->combinations[$idProductAttribute]['attributes'][$name][1])
                ) {
                    return LengowMain::cleanData($this->combinations[$idProductAttribute]['attributes'][$name][1]);
                }
                if (isset($this->{$name})) {
                    return LengowMain::cleanData($this->{$name});
                }

                return '';
        }
    }

    /**
     * Make the feature of current product
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
     * Make the attributes of current product
     */
    public function makeAttributes()
    {
        $combArray = [];
        $combinations = $this->getAttributesGroups($this->context->language->id);
        if (is_array($combinations)) {
            $cImages = $this->getImageUrlCombination();
            foreach ($combinations as $c) {
                $attributeId = $c['id_product_attribute'];
                $priceToConvert = Tools::convertPrice($c['price'], $this->context->currency);
                $price = Tools::displayPrice($priceToConvert, $this->context->currency);
                if (array_key_exists($attributeId, $combArray)) {
                    $combArray[$attributeId]['attributes'][$c['group_name']] = [
                        $c['group_name'],
                        $c['attribute_name'],
                        $c['id_attribute'],
                    ];
                } else {
                    $combArray[$attributeId] = [
                        'id_product_attribute' => $attributeId,
                        'attributes' => [
                            $c['group_name'] => [
                                $c['group_name'],
                                $c['attribute_name'],
                                $c['id_attribute'],
                            ],
                        ],
                        'wholesale_price' => isset($c['wholesale_price']) ? $c['wholesale_price'] : '',
                        'price' => $price,
                        'ecotax' => isset($c['ecotax']) ? $c['ecotax'] : '',
                        'weight' => $c['weight'],
                        'reference' => $c['reference'],
                        'ean13' => isset($c['ean13']) ? $c['ean13'] : '',
                        'upc' => isset($c['upc']) ? $c['upc'] : '',
                        'isbn' => isset($c['isbn']) ? $c['isbn'] : '',
                        'supplier_reference' => isset($c['supplier_reference']) ? $c['supplier_reference'] : '',
                        'minimal_quantity' => isset($c['minimal_quantity']) ? $c['minimal_quantity'] : '',
                        'images' => isset($cImages[$attributeId]) ? $cImages[$attributeId] : [],
                    ];
                }
                $available = new DateTime($c['available_date']);
                $combArray[$attributeId]['available_date'] = $available->format('Y-m-d');
            }
        }
        if (isset($combArray)) {
            foreach ($combArray as $idProductAttribute => $productAttribute) {
                $name = '';
                // in order to keep the same attributes order
                asort($productAttribute['attributes']);
                foreach ($productAttribute['attributes'] as $attribute) {
                    $name .= $attribute[0] . ', ';
                }
                if (!$this->variation) {
                    $this->variation = rtrim($name, ', ');
                }
                $combArray[$idProductAttribute]['available_date'] = (
                    $productAttribute['available_date'] != 0
                    ? date(LengowMain::DATE_DAY, strtotime($productAttribute['available_date']))
                    : '0000-00-00'
                );
            }
        }
        $this->combinations = $combArray;
    }

    /**
     * Get combinations of current product
     *
     * @return array
     */
    public function getCombinations()
    {
        return $this->combinations;
    }

    /**
     * Get all available attribute groups
     *
     * @param int $idLang PrestaShop lang id
     * @param int $id_product_attribute
     *
     * @return array
     */
    public function getAttributesGroups($idLang, $id_product_attribute = null)
    {
        if (!Combination::isFeatureActive()) {
            return [];
        }
        $sql = 'SELECT
            ag.`id_attribute_group`,
            ag.`is_color_group`,
            agl.`name` AS group_name,
            agl.`public_name` AS public_group_name,
            a.`id_attribute`,
            al.`name` AS attribute_name,
            a.`color` AS attribute_color,
            pa.`id_product_attribute`,
            IFNULL(stock.quantity, 0) as quantity,
            product_attribute_shop.`price`,
            product_attribute_shop.`ecotax`,
            pa.`weight`,
            product_attribute_shop.`default_on`,
            pa.`reference`,
            product_attribute_shop.`unit_price_impact`,
            pa.`minimal_quantity`,
            pa.`available_date`,
            ag.`group_type`,
            ps.`product_supplier_reference` AS `supplier_reference`,
            pa.`ean13`,
            pa.`upc`,
            ' . (version_compare(_PS_VERSION_, '1.7.0', '>=') ? 'pa.`isbn`,' : '') . '
            pa.`wholesale_price`,
            pa.`ecotax`
            FROM `' . _DB_PREFIX_ . 'product_attribute` pa
            ' . Shop::addSqlAssociation('product_attribute', 'pa') . '
            ' . Product::sqlStock('pa', 'pa') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'product_supplier` ps
            ON (ps.`id_product_attribute` = pa.`id_product_attribute` AND ps.`id_product` = ' . (int) $this->id . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac
            ON pac.`id_product_attribute` = pa.`id_product_attribute`
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON a.`id_attribute` = al.`id_attribute`
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl
            ON ag.`id_attribute_group` = agl.`id_attribute_group`
            ' . Shop::addSqlAssociation('attribute', 'a') . '
            WHERE pa.`id_product` = ' . (int) $this->id . '
                AND al.`id_lang` = ' . (int) $idLang . '
                AND agl.`id_lang` = ' . (int) $idLang . '
            GROUP BY id_attribute_group, id_product_attribute
            ORDER BY ag.`position` ASC, a.`position` ASC, agl.`name` ASC';
        try {
            return Db::getInstance()->executeS($sql);
        } catch (PrestaShopDatabaseException $e) {
            return [];
        }
    }

    /**
     * Get supplier reference
     *
     * @param int $idProductAttribute PrestaShop product attribute id
     *
     * @return string
     */
    protected function getSupplierReference($idProductAttribute)
    {
        if ($idProductAttribute && $this->combinations[$idProductAttribute]['supplier_reference']) {
            $supplierReference = $this->combinations[$idProductAttribute]['supplier_reference'];
        } elseif ($this->supplier_reference !== '') {
            $supplierReference = $this->supplier_reference;
        } else {
            $sql = 'SELECT `product_supplier_reference`
                FROM `' . _DB_PREFIX_ . 'product_supplier`
                WHERE `id_product` = \'' . (int) $this->id . '\'
                AND `id_product_attribute` = 0';
            $result = Db::getInstance()->getRow($sql);
            $supplierReference = $result['product_supplier_reference'] ?? '';
        }

        return $supplierReference;
    }

    /**
     * Get product breadcrumb from default category
     *
     * @return string
     */
    protected function getBreadcrumb()
    {
        if ($this->categoryDefault) {
            $breadcrumb = '';
            $categories = $this->categoryDefault->getParentsCategories();
            foreach ($categories as $category) {
                $breadcrumb = $category['name'] . ' > ' . $breadcrumb;
            }
            $breadcrumb = rtrim($breadcrumb, ' > ');
        } else {
            $breadcrumb = $this->categoryDefaultName;
        }

        return $breadcrumb;
    }

    /**
     * Get product url for all different version of PrestaShop
     *
     * @param int|null $idProductAttribute PrestaShop product attribute id
     * @param bool $rewrite rewrite product url or not
     *
     * @return string
     */
    protected function getProductUrl($idProductAttribute = null, $rewrite = false)
    {
        try {
            if (version_compare(_PS_VERSION_, '1.6.1.1', '<')) {
                $productUrl = $this->context->link->getProductLink(
                    $this,
                    $rewrite ? $this->link_rewrite : null,
                    null,
                    null,
                    null,
                    null,
                    $idProductAttribute,
                    _PS_VERSION_ === '1.6.1.0' && !$rewrite
                );
            } else {
                if (version_compare(_PS_VERSION_, '1.7.1', '>=') && $idProductAttribute === null) {
                    $idProductAttribute = $this->getDefaultAttribute($this->id);
                }
                $productUrl = $this->context->link->getProductLink(
                    $this,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $idProductAttribute,
                    !(version_compare(_PS_VERSION_, '1.7.1', '<') && $rewrite),
                    false,
                    true
                );
            }
        } catch (Exception $e) {
            $productUrl = '';
        }

        return $productUrl;
    }

    /**
     * Get ecotax
     *
     * @param int|null $idProductAttribute PrestaShop product attribute id
     *
     * @return float
     */
    protected function getEcotaxLengow($idProductAttribute = null)
    {
        $ecotax = 0;
        if ($idProductAttribute && $this->combinations[$idProductAttribute]['ecotax']) {
            $ecotax = $this->combinations[$idProductAttribute]['ecotax'];
        }
        if ($ecotax == 0) {
            $ecotax = (isset($this->ecotaxinfos) && $this->ecotaxinfos > 0) ? $this->ecotaxinfos : $this->ecotax;
        }

        return (float) $ecotax;
    }

    /**
     * Get shipping cost
     *
     * @param int|null $idProductAttribute PrestaShop product attribute id
     *
     * @return float
     *
     * @throws Exception
     */
    protected function getShippingCost($idProductAttribute = null)
    {
        if ($idProductAttribute) {
            $price = $this->getData('price_incl_tax', $idProductAttribute);
            $weight = $this->getData('weight', $idProductAttribute);
        } else {
            $price = $this->getData('price_incl_tax');
            $weight = $this->getData('weight');
        }
        $idZone = (int) $this->context->country->id_zone;
        $idCurrency = (int) $this->context->cart->id_currency;
        if (!$this->carrier) {
            return LengowMain::formatNumber(0);
        }
        $shippingMethod = (int) $this->carrier->getShippingMethod();
        $shippingCost = 0;
        if (!defined('Carrier::SHIPPING_METHOD_FREE') || $shippingMethod !== Carrier::SHIPPING_METHOD_FREE) {
            if ($shippingMethod === Carrier::SHIPPING_METHOD_WEIGHT) {
                $shippingCost = LengowMain::formatNumber(
                    $this->carrier->getDeliveryPriceByWeight($weight, $idZone)
                );
            } else {
                $shippingCost = LengowMain::formatNumber(
                    $this->carrier->getDeliveryPriceByPrice($price, $idZone, $idCurrency)
                );
            }
        }
        // check if product have single shipping cost
        if ($this->additional_shipping_cost > 0) {
            $shippingCost += $this->additional_shipping_cost;
        }
        // tax calculation
        $defaultCountry = (int) Configuration::get('PS_COUNTRY_DEFAULT');
        $idTaxRulesGroup = $this->carrier->getIdTaxRulesGroup();
        $taxRules = TaxRule::getTaxRulesByGroupId(Configuration::get('PS_LANG_DEFAULT'), $idTaxRulesGroup);
        foreach ($taxRules as $taxRule) {
            if (isset($taxRule['id_country']) && (int) $taxRule['id_country'] === $defaultCountry) {
                $tr = new TaxRule($taxRule['id_tax_rule']);
            }
        }
        if (isset($tr)) {
            $t = new Tax($tr->id_tax);
            $taxCalculator = new TaxCalculator([$t]);
            $taxes = $taxCalculator->getTaxesAmount($shippingCost);
            if (!empty($taxes)) {
                foreach ($taxes as $tax) {
                    $shippingCost += $tax;
                }
            }
        }

        return LengowMain::formatNumber($shippingCost);
    }

    /**
     * Get image link
     *
     * @param string $name name of product attribute
     * @param int|null $idProductAttribute PrestaShop product attribute id
     *
     * @return string
     */
    protected function getImageLink($name, $idProductAttribute = null)
    {
        $index = explode('_', $name);
        $idImage = (isset($index[1])) ? $index[1] - 1 : null;
        if (is_null($idImage)) {
            return '';
        }
        if ($idProductAttribute) {
            $attributeImages = $this->combinations[$idProductAttribute]['images'];
            if (!empty($attributeImages)) {
                if (isset($attributeImages[$idImage])) {
                    return $attributeImages[$idImage];
                }

                return '';
            }
        }

        return isset($this->images[$idImage]) ? $this->context->link->getImageLink(
            $this->link_rewrite,
            $this->id . '-' . $this->images[$idImage]['id_image'],
            $this->imageSize
        ) : '';
    }

    /**
     * Get product type (simple, parent or child)
     *
     * @param int|null $idProductAttribute PrestaShop product attribute id
     *
     * @return string
     */
    protected function getProductTypeLengow($idProductAttribute = null)
    {
        if ($idProductAttribute) {
            $type = 'child';
        } elseif (empty($this->combinations)) {
            $type = 'simple';
        } else {
            $type = 'parent';
        }

        return $type;
    }

    /**
     * Returns all tags to string
     *
     * @return string
     */
    protected function getTagList()
    {
        return $this->getTags($this->context->language->id);
    }

    /**
     * Get product weight without unit
     *
     * @param int|null $idProductAttribute PrestaShop product attribute id
     *
     * @return string
     */
    protected function getWeight($idProductAttribute = null)
    {
        if ($idProductAttribute && $this->combinations[$idProductAttribute]['weight']) {
            $weight = (float) $this->weight + (float) $this->combinations[$idProductAttribute]['weight'];
        } else {
            $weight = (float) $this->weight;
        }

        return $weight;
    }

    /**
     * Get a specific attribute from a parent or a combination
     *
     * @param string $name name of product attribute
     * @param int|null $idProductAttribute PrestaShop product attribute id
     *
     * @return string
     */
    protected function getProductData($name, $idProductAttribute = null)
    {
        $value = false;
        if ($idProductAttribute && $this->combinations[$idProductAttribute][$name]) {
            $value = $this->combinations[$idProductAttribute][$name];
        }
        // if the value of the combination is not given, we take that of the parent
        if (!$value || $value === 0 || $value === '0' || $value === '') {
            $value = isset($this->{$name}) ? $this->{$name} : '';
        }

        return $value;
    }

    /**
     * Publish or Un-publish to Lengow
     *
     * @param int $productId PrestaShop product id
     * @param int $value publish value (1 : publish, 0 : unpublish)
     * @param int $shopId PrestaShop shop id
     *
     * @return bool
     */
    public static function publish($productId, $value, $shopId)
    {
        if (!$value) {
            $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'lengow_product
             WHERE id_product = ' . (int) $productId . ' AND id_shop = ' . (int) $shopId;
            Db::getInstance()->Execute($sql);
        } else {
            try {
                $sql = 'SELECT id_product FROM ' . _DB_PREFIX_ . 'lengow_product
                    WHERE id_product = ' . (int) $productId . ' AND id_shop = ' . (int) $shopId;
                $results = Db::getInstance()->ExecuteS($sql);
                if (empty($results)) {
                    return Db::getInstance()->insert(
                        self::TABLE_PRODUCT,
                        [
                            self::FIELD_PRODUCT_ID => (int) $productId,
                            self::FIELD_SHOP_ID => (int) $shopId,
                        ]
                    );
                }
            } catch (PrestaShopDatabaseException $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Compares found id with API ids and checks if they match
     *
     * @param LengowProduct $product Lengow product instance
     * @param array $apiDatas product ids from the API
     *
     * @return bool if valid or not
     */
    protected static function isValidId($product, $apiDatas)
    {
        $attributes = ['reference', 'ean13', 'upc', 'id'];
        $combinations = $product->getCombinations();
        if (!empty($combinations)) {
            foreach ($combinations as $combination) {
                foreach ($attributes as $attributeName) {
                    foreach ($apiDatas as $idApi) {
                        if (!empty($idApi)) {
                            if ($attributeName === 'id') {
                                // compatibility with old plugins
                                $id = str_replace('\_', '_', $idApi);
                                $id = str_replace('X', '_', $id);
                                $ids = explode('_', $id);
                                $id = $ids[0];
                                if (is_numeric($id) && $product->{$attributeName} == $id) {
                                    return true;
                                }
                            } elseif ($combination[$attributeName] === $idApi) {
                                return true;
                            }
                        }
                    }
                }
            }
        } else {
            foreach ($attributes as $attributeName) {
                foreach ($apiDatas as $idApi) {
                    if (!empty($idApi)) {
                        if ($attributeName === 'id') {
                            // compatibility with old plugins
                            $id = str_replace('\_', '_', $idApi);
                            $id = str_replace('X', '_', $id);
                            $ids = explode('_', $id);
                            $id = $ids[0];
                            if (is_numeric($id) && $product->{$attributeName} == $id) {
                                return true;
                            }
                        }
                        if ($product->{$attributeName} === $idApi) {
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
     * @param mixed $api product datas
     *
     * @return array
     */
    public static function extractProductDataFromAPI($api)
    {
        $temp = [];
        foreach (self::$productApiNodes as $node) {
            $temp[$node] = $api->{$node};
        }
        $qty = (float) $temp['quantity'];
        if ($qty <= 0) {
            $temp['price_unit'] = 0;
        } else {
            $temp['price_unit'] = (float) $temp['amount'] / $qty;
        }

        return $temp;
    }

    /**
     * Retrieves the product sku
     *
     * @param string $attributeName attribute name
     * @param string $attributeValue attribute value
     * @param int $idShop PrestaShop shop id
     * @param array $apiDatas product ids from the API
     *
     * @return array|false
     *
     * @throws LengowException
     */
    public static function matchProduct($attributeName, $attributeValue, $idShop, $apiDatas = [])
    {
        if (empty($attributeValue) || empty($attributeName)) {
            return false;
        }
        switch (Tools::strtolower($attributeName)) {
            case 'reference':
                return self::findProduct('reference', $attributeValue, $idShop);
            case 'ean':
                return self::findProduct('ean13', $attributeValue, $idShop);
            case 'upc':
                return self::findProduct('upc', $attributeValue, $idShop);
            case 'isbn':
                return self::findProduct('isbn', $attributeValue, $idShop);
            default:
                $idsProduct = [];
                // compatibility with old plugins
                $sku = str_replace(['\_', 'X'], '_', $attributeValue);
                $sku = explode('_', $sku);
                if (isset($sku[0]) && preg_match('/^[0-9]*$/', $sku[0]) && count($sku) < 3) {
                    $idsProduct['id_product'] = (int) $sku[0];
                    if (isset($sku[1])) {
                        if (preg_match('/^[0-9]*$/', $sku[1]) && count($sku) === 2) {
                            // compatibility with old plugins -> XXX_0 product without variation
                            if ($sku[1] != 0) {
                                $idsProduct['id_product_attribute'] = (int) $sku[1];
                            }
                        } else {
                            return false;
                        }
                    }
                    $idBool = self::checkProductId($idsProduct['id_product'], $apiDatas);
                    $idAttBool = true;
                    if (isset($idsProduct['id_product_attribute'])) {
                        $idAttBool = self::checkProductAttributeId(
                            new LengowProduct($idsProduct['id_product']),
                            $idsProduct['id_product_attribute']
                        );
                    }
                    if ($idBool && $idAttBool) {
                        return $idsProduct;
                    }
                }

                return false;
        }
    }

    /**
     * Check if product id found is correct
     *
     * @param int $idProduct PrestaShop product id
     * @param array $apiDatas product ids from the API
     *
     * @return bool
     *
     * @throws LengowException
     */
    protected static function checkProductId($idProduct, $apiDatas)
    {
        if (empty($idProduct)) {
            return false;
        }
        $product = new LengowProduct($idProduct);

        return !($product->name === '' || !self::isValidId($product, $apiDatas));
    }

    /**
     * Check if the product attribute exists
     *
     * @param LengowProduct $product Lengow product instance
     * @param int $idProductAttribute PrestaShop product attribute id
     *
     * @return bool
     */
    protected static function checkProductAttributeId($product, $idProductAttribute)
    {
        return !($idProductAttribute === 0 || !array_key_exists($idProductAttribute, $product->getCombinations()));
    }

    /**
     * Return the product and its attribute ids
     *
     * @param string $key attribute key
     * @param string $value attribute value
     * @param int $idShop PrestaShop shop id
     *
     * @return int|false
     */
    protected static function findProduct($key, $value, $idShop)
    {
        if (empty($key) || empty($value) || ($key === 'isbn' && version_compare(_PS_VERSION_, '1.7.0', '<'))) {
            return false;
        }
        $query = new DbQuery();
        $query->select('p.id_product');
        $query->from('product', 'p');
        $query->innerJoin('product_shop', 'ps', 'p.id_product = ps.id_product');
        $query->where('p.' . pSQL(Db::getInstance()->_escape($key)) . ' = \'' . pSQL(Db::getInstance()->_escape($value)) . '\'');
        $query->where('ps.`id_shop` = \'' . (int) Db::getInstance()->_escape($idShop) . '\'');
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
        // if no result, search in attribute
        if ($result == '') {
            $query = new DbQuery();
            $query->select('pa.id_product, pa.id_product_attribute');
            $query->from('product_attribute', 'pa');
            $query->innerJoin('product_shop', 'ps', 'pa.id_product = ps.id_product');
            $query->where('pa.' . pSQL(Db::getInstance()->_escape($key)) . ' = \'' . pSQL(Db::getInstance()->_escape($value)) . '\'');
            $query->where('ps.`id_shop` = \'' . (int) Db::getInstance()->_escape($idShop) . '\'');
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
        }

        return $result;
    }

    /**
     * Search a product by its reference, ean, upc and id
     *
     * @param string $attributeValue attribute value
     * @param int $idShop PrestaShop shop id
     * @param array $apiDatas product ids from the API
     *
     * @return array|false
     *
     * @throws LengowException
     */
    public static function advancedSearch($attributeValue, $idShop, $apiDatas)
    {
        // product class attribute to search
        $attributes = ['reference', 'ean', 'upc', 'isbn', 'ids'];
        $idsProduct = [];
        $find = false;
        $i = 0;
        $count = count($attributes);
        while (!$find && $i < $count) {
            $idsProduct = self::matchProduct($attributes[$i], $attributeValue, $idShop, $apiDatas);
            if (!empty($idsProduct)) {
                $find = true;
            }
            ++$i;
        }
        if ($find) {
            return $idsProduct;
        }

        return false;
    }

    /**
     * Calculate product without taxes using TaxManager
     *
     * @param array $product product
     * @param int $idAddress PrestaShop address id used to get tax rate
     * @param Context $context PrestaShop context instance
     *
     * @return float
     */
    public static function calculatePriceWithoutTax($product, $idAddress, $context)
    {
        $taxAddress = new LengowAddress((int) $idAddress);
        $taxManager = TaxManagerFactory::getManager(
            $taxAddress,
            Product::getIdTaxRulesGroupByIdProduct((int) $product['id_product'], $context)
        );
        $taxCalculator = $taxManager->getTaxCalculator();

        return $taxCalculator->removeTaxes($product['price_wt']);
    }

    /**
     * get image url of product variations
     *
     * @return array|false
     */
    public function getImageUrlCombination()
    {
        $cImages = [];
        $psImages = $this->getCombinationImages($this->id_lang);
        $maxImage = 10;
        if ($psImages) {
            foreach ($psImages as $productAttributeId => $images) {
                foreach ($images as $image) {
                    if (!isset($cImages[$productAttributeId]) || count($cImages[$productAttributeId]) < $maxImage) {
                        $cImages[$productAttributeId][] = $this->context->link->getImageLink(
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
     * Get Max Image Type
     *
     * @return string
     *
     * @throws LengowException cant find image size
     */
    public static function getMaxImageType()
    {
        $sql = 'SELECT name FROM ' . _DB_PREFIX_ . 'image_type WHERE products = 1 ORDER BY width DESC';
        try {
            $result = Db::getInstance()->executeS($sql);
        } catch (PrestaShopDatabaseException $e) {
            $result = false;
        }
        if ($result) {
            return $result[0]['name'];
        }
        throw new LengowException(LengowMain::setLogMessage('log.export.error_cant_find_image_size'));
    }
}
