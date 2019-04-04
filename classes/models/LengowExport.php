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

/**
 * Lengow Export class
 */
class LengowExport
{
    /**
     * @var array default fields for export
     */
    public static $defaultFields;

    /**
     * @var array all available params for export
     */
    public static $exportParams = array(
        'mode',
        'format',
        'stream',
        'offset',
        'limit',
        'selection',
        'out_of_stock',
        'product_ids',
        'variation',
        'inactive',
        'shop',
        'currency',
        'language',
        'legacy_fields',
        'log_output',
        'update_export_date',
        'get_params'
    );

    /**
     * @var array new fields for v3
     */
    protected $newFields = array(
        'id' => 'id',
        'sku' => 'reference',
        'sku_supplier' => 'supplier_reference',
        'ean' => 'ean',
        'upc' => 'upc',
        'name' => 'name',
        'quantity' => 'quantity',
        'minimal_quantity' => 'minimal_quantity',
        'availability' => 'available',
        'is_virtual' => 'is_virtual',
        'condition' => 'condition',
        'category' => 'breadcrumb',
        'status' => 'active',
        'url' => 'url',
        'url_rewrite' => 'url_rewrite',
        'price_excl_tax' => 'price_sale_duty_free',
        'price_incl_tax' => 'price_sale',
        'price_before_discount_excl_tax' => 'price_duty_free',
        'price_before_discount_incl_tax' => 'price',
        'discount_percent' => 'price_sale_percent',
        'discount_start_date' => 'sale_from',
        'discount_end_date' => 'sale_to',
        'ecotax' => 'ecotax',
        'shipping_cost' => 'price_shipping',
        'shipping_delay' => 'delivery_time',
        'currency' => 'currency',
        'image_url_1' => 'image_1',
        'image_url_2' => 'image_2',
        'image_url_3' => 'image_3',
        'image_url_4' => 'image_4',
        'image_url_5' => 'image_5',
        'image_url_6' => 'image_6',
        'image_url_7' => 'image_7',
        'image_url_8' => 'image_8',
        'image_url_9' => 'image_9',
        'image_url_10' => 'image_10',
        'type' => 'type',
        'parent_id' => 'id_parent',
        'variation' => 'variation',
        'language' => 'language',
        'description' => 'description',
        'description_html' => 'description_html',
        'description_short' => 'short_description',
        'description_short_html' => 'short_description_html',
        'tags' => 'tags',
        'meta_title' => 'meta_title',
        'meta_keyword' => 'meta_keywords',
        'meta_description' => 'meta_description',
        'manufacturer' => 'manufacturer',
        'supplier' => 'supplier',
        'weight' => 'weight',
    );

    /**
     * @var array legacy fields for export
     */
    protected $legacyFields = array(
        'id_product' => 'id',
        'name_product' => 'name',
        'reference_product' => 'reference',
        'supplier_reference' => 'supplier_reference',
        'manufacturer' => 'manufacturer',
        'category' => 'breadcrumb',
        'description' => 'description',
        'description_short' => 'short_description',
        'price_product' => 'price',
        'wholesale_price' => 'wholesale_price',
        'price_ht' => 'price_duty_free',
        'price_reduction' => 'price_sale',
        'pourcentage_reduction' => 'price_sale_percent',
        'quantity' => 'quantity',
        'weight' => 'weight',
        'ean' => 'ean',
        'upc' => 'upc',
        'ecotax' => 'ecotax',
        'active' => 'active',
        'available_product' => 'available',
        'url_product' => 'url',
        'fdp' => 'price_shipping',
        'id_mere' => 'id_parent',
        'delais_livraison' => 'delivery_time',
        'image_product' => 'image_1',
        'image_product_2' => 'image_2',
        'image_product_3' => 'image_3',
        'image_4' => 'image_4',
        'image_5' => 'image_5',
        'image_6' => 'image_6',
        'image_7' => 'image_7',
        'image_8' => 'image_8',
        'image_9' => 'image_9',
        'image_10' => 'image_10',
        'reduction_from' => 'sale_from',
        'reduction_to' => 'sale_to',
        'meta_keywords' => 'meta_keywords',
        'meta_description' => 'meta_description',
        'url_rewrite' => 'url_rewrite',
        'product_type' => 'type',
        'product_variation' => 'variation',
        'currency' => 'currency',
        'condition' => 'condition',
        'supplier' => 'supplier',
        'minimal_quantity' => 'minimal_quantity',
        'is_virtual' => 'is_virtual',
        'available_for_order' => 'available_for_order',
        'available_date' => 'available_date',
        'show_price' => 'show_price',
        'visibility' => 'visibility',
        'available_now' => 'available_now',
        'available_later' => 'available_later',
        'stock_availables' => 'stock_availables',
        'description_html' => 'description_html',
        'availability' => 'availability',
    );

    /**
     * @var string format to return
     */
    protected $format;

    /**
     * @var Carrier Prestashop Carrier instance
     */
    protected $carrier;

    /**
     * @var LengowFeed Feed
     */
    protected $feed;

    /**
     * @var integer Prestashop shop id
     */
    protected $idShop;

    /**
     * @var boolean stream return
     */
    protected $stream = true;

    /**
     * @var boolean export Lengow selection
     */
    protected $selection = false;

    /**
     * @var boolean export out of stock product
     */
    protected $outOfStock = false;

    /**
     * @var boolean export product variations
     */
    protected $variation = true;

    /**
     * @var boolean include active products
     */
    protected $inactive = false;

    /**
     * @var boolean see log or not
     */
    protected $logOutput;

    /**
     * @var boolean use legacy fields
     */
    protected $legacy = false;

    /**
     * @var integer amount of products to export
     */
    protected $limit = 0;

    /**
     * @var integer offset of total product
     */
    protected $offset = 0;

    /**
     * @var array product ids to be exported
     */
    protected $productIds = array();

    /**
     * @var boolean update export date.
     */
    protected $updateExportDate;

    /**
     * @var array cache combination
     */
    protected $cacheCombination;

    /**
     * @var array excluded products for export
     */
    protected $excludedProducts = array();

    /**
     * Construct new Lengow export.
     *
     * @param array $params optional options
     * integer limit              The number of product to be exported
     * integer offset             From what product export
     * integer shop_id            Shop id for export
     * integer language_id        language for export
     * string  product_ids        Ids product to export
     * string  format             Export Format (csv|yaml|xml|json)
     * boolean stream             Display file when call script (1) | Save File (0)
     * boolean out_of_stock       Export product in stock and out stock (1) | Export Only in stock product (0)
     * boolean selection          Export selected product (1) | Export all products (0)
     * boolean inactive           Export active and inactive product (1) | Export Only active product (0)
     * boolean variation          Export product variation (1) | Export Only simple product (0)
     * boolean legacy_fields      Export with legacy fields (1) | Export with new fields (0)
     * boolean update_export_date Update 'LENGOW_LAST_EXPORT' when launching export process (1)
     *                                | Do not update 'LENGOW_LAST_EXPORT' when exporting from toolbox (0)
     */
    public function __construct($params = array())
    {
        $this->setFormat(isset($params["format"]) ? $params["format"] : 'csv');
        $this->offset = isset($params["offset"]) ? $params["offset"] : false;
        $this->productIds = isset($params["product_ids"]) ? $params["product_ids"] : false;
        $this->stream = isset($params["stream"]) ? $params["stream"] : false;
        $this->limit = isset($params["limit"]) ? (int)$params["limit"] : false;
        $this->idShop = (int)isset($params["shop_id"]) ? (int)$params["shop_id"] : Context::getContext()->shop->id;
        $this->language = isset($params["language_id"])
            ? new Language($params["language_id"])
            : new Language(Configuration::get('PS_LANG_DEFAULT', null, null, $this->idShop));
        // Get specific params in database
        $selection = LengowConfiguration::get('LENGOW_EXPORT_SELECTION_ENABLED', null, null, $this->idShop);
        $outOfStock = LengowConfiguration::get('LENGOW_EXPORT_OUT_STOCK', null, null, $this->idShop);
        $variation = LengowConfiguration::get('LENGOW_EXPORT_VARIATION_ENABLED', null, null, $this->idShop);
        $inactive = LengowConfiguration::get('LENGOW_EXPORT_INACTIVE', null, null, $this->idShop);
        // Set default value for new shop
        if (is_null($selection)) {
            LengowConfiguration::updateValue('LENGOW_EXPORT_SELECTION_ENABLED', 0, null, null, $this->idShop);
            $selection = false;
        } else {
            $selection = (bool)$selection;
        }
        if (is_null($outOfStock)) {
            LengowConfiguration::updateValue('LENGOW_EXPORT_OUT_STOCK', 1, null, null, $this->idShop);
            $outOfStock = true;
        } else {
            $outOfStock = (bool)$outOfStock;
        }
        if (is_null($variation)) {
            LengowConfiguration::updateValue('LENGOW_EXPORT_VARIATION_ENABLED', 1, null, null, $this->idShop);
            $variation = true;
        } else {
            $variation = (bool)$variation;
        }
        if (is_null($inactive)) {
            LengowConfiguration::updateValue('LENGOW_EXPORT_INACTIVE', 0, null, null, $this->idShop);
            $inactive = false;
        } else {
            $inactive = (bool)$inactive;
        }
        $this->selection = isset($params['selection']) ? (bool)$params['selection'] : $selection;
        $this->outOfStock = isset($params['out_of_stock']) ? (bool)$params['out_of_stock'] : $outOfStock;
        $this->variation = isset($params['variation']) ? (bool)$params['variation'] : $variation;
        $this->inactive = isset($params['inactive']) ? (bool)$params['inactive'] : $inactive;
        if ($this->stream) {
            $this->logOutput = false;
        } else {
            $this->logOutput = isset($params['log_output']) ? (bool)$params['log_output'] : true;
        }
        $this->updateExportDate = isset($params['update_export_date']) ? (bool)$params['update_export_date'] : true;
        if (!Context::getContext()->currency) {
            Context::getContext()->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }
        $this->legacy = isset($params['legacy_fields']) ? (bool)$params['legacy_fields'] : null;
    }

    /**
     * Set format to export
     *
     * @param string $format The export format
     */
    public function setFormat($format)
    {
        $this->format = in_array($format, LengowFeed::$availableFormats) ? $format : 'csv';
    }

    /**
     * Execute export process
     *
     */
    public function exec()
    {
        try {
            // Clean logs
            LengowMain::cleanLog();
            LengowMain::log('Export', LengowMain::setLogMessage('log.export.start'), $this->logOutput);
            $shop = new LengowShop($this->idShop);
            LengowMain::log(
                'Export',
                LengowMain::setLogMessage(
                    'log.export.start_for_shop',
                    array(
                        'name_shop' => $shop->name,
                        'id_shop' => $shop->id
                    )
                ),
                $this->logOutput
            );
            // Check currency for export
            $this->checkCurrency();
            // Set carrier for the calculation of the shipping cost
            $this->setCarrier();
            // Set legacy fields option
            $this->setLegacyFields();
            // Get fields to export
            $exportFields = $this->getFields();
            // Get products to be exported
            $products = $this->exportIds();
            LengowMain::log(
                'Export',
                LengowMain::setLogMessage('log.export.nb_product_found', array("nb_product" => count($products))),
                $this->logOutput
            );
            $this->export($products, $exportFields, $shop);
            if ($this->updateExportDate) {
                LengowConfiguration::updateValue('LENGOW_LAST_EXPORT', date('Y-m-d H:i:s'), false, null, $this->idShop);
            }
            LengowMain::log(
                'Export',
                LengowMain::setLogMessage('log.export.end'),
                $this->logOutput
            );
        } catch (LengowException $e) {
            $errorMessage = $e->getMessage();
        } catch (Exception $e) {
            $errorMessage = '[Prestashop error] "' . $e->getMessage() . '" ' . $e->getFile() . ' | ' . $e->getLine();
        }
        if (isset($errorMessage)) {
            $decodedMessage = LengowMain::decodeLogMessage($errorMessage, 'en');
            LengowMain::log(
                'Export',
                LengowMain::setLogMessage(
                    'log.export.export_failed',
                    array('decoded_message' => $decodedMessage)
                ),
                $this->logOutput
            );
        }
    }

    /**
     * Check currency to export
     *
     * @throws LengowException illegal currency
     *
     * @return boolean
     */
    public function checkCurrency()
    {
        if (!Context::getContext()->currency) {
            throw new LengowException(LengowMain::setLogMessage('log.export.error_illegal_currency'));
        }
        return true;
    }

    /**
     * Set Carrier to export
     *
     * @throws LengowException no default carrier selected
     *
     * @return boolean
     */
    public function setCarrier()
    {
        $carrier = LengowCarrier::getDefaultExportCarrier();
        if (!$carrier) {
            throw new LengowException(LengowMain::setLogMessage('log.export.error_no_carrier_selected'));
        }
        $this->carrier = $carrier;
        return true;
    }

    /**
     * Set or not legacy fields to export
     */
    public function setLegacyFields()
    {
        if (is_null($this->legacy)) {
            $result = LengowConnector::queryApi('get', '/v3.0/plans');
            if (isset($result->accountVersion)) {
                $this->legacy = $result->accountVersion === 'v2' ? true : false;
            } else {
                $this->legacy = false;
            }
        }
        self::$defaultFields = $this->legacy ? $this->legacyFields : $this->newFields;
    }

    /**
     * Export products
     *
     * @param array $products list of products to be exported
     * @param array $fields list of fields
     * @param Shop $shop Prestashop shop being exported
     *
     * @throws LengowException folder not writable
     */
    public function export($products, $fields, $shop)
    {
        $productCount = 0;
        $this->feed = new LengowFeed(
            $this->stream,
            $this->format,
            $this->legacy,
            isset($shop->name) ? $shop->name : 'default'
        );
        $this->feed->write('header', $fields);
        $isFirst = true;
        // Get the maximum of character for yaml format
        $maxCharacter = 0;
        foreach ($fields as $field) {
            if (Tools::strlen($field) > $maxCharacter) {
                $maxCharacter = Tools::strlen($field);
            }
        }
        foreach ($products as $p) {
            // Ignore products with faulty combinations
            if (in_array($p['id_product'], $this->excludedProducts)) {
                continue;
            }
            $productDatas = array();
            $product = new LengowProduct(
                $p['id_product'],
                $this->language->id,
                array(
                    'carrier' => $this->carrier,
                    'image_size' => LengowProduct::getMaxImageType(),
                    'language' => $this->language
                )
            );
            if ($p['id_product'] && $p['id_product_attribute'] == 0) {
                foreach ($fields as $field) {
                    if (isset(self::$defaultFields[$field])) {
                        $productDatas[$field] = $product->getData(
                            self::$defaultFields[$field],
                            null
                        );
                    } else {
                        $productDatas[$field] = $product->getData($field, null);
                    }
                }
                // Get additional data
                $productDatas = $this->setAdditionalFieldsValues($product, null, $productDatas);
                // Write parent product
                $this->feed->write('body', $productDatas, $isFirst, $maxCharacter);
                $productCount++;
            }
            if ($p['id_product'] && $p['id_product_attribute'] > 0) {
                if (!$this->loadCacheCombinations($product, $fields)) {
                    LengowMain::log(
                        'Export',
                        LengowMain::setLogMessage(
                            'log.export.error_no_product_combination',
                            array('product_id' => $product->id)
                        ),
                        $this->logOutput
                    );
                    // Indicates that a product has failed combinations
                    $this->excludedProducts[] = $product->id;
                    continue;
                }
                if (isset($this->cacheCombination[$p['id_product']][$p['id_product_attribute']])) {
                    // Get additional data
                    $combinationDatas = $this->setAdditionalFieldsValues(
                        $product,
                        $p['id_product_attribute'],
                        $this->cacheCombination[$p['id_product']][$p['id_product_attribute']]
                    );
                    $this->feed->write('body', $combinationDatas, $isFirst, $maxCharacter);
                    $productCount++;
                }
            }
            if ($productCount > 0 && $productCount % 50 == 0) {
                LengowMain::log(
                    'Export',
                    LengowMain::setLogMessage(
                        'log.export.count_product',
                        array('product_count' => $productCount)
                    ),
                    $this->logOutput
                );
            }
            if ($this->limit > 0 && $productCount >= $this->limit) {
                break;
            }
            // Clean data for next product
            unset($productDatas, $product);
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
            $isFirst = false;
        }
        $success = $this->feed->end();
        if (!$success) {
            throw new LengowException(
                LengowMain::setLogMessage('log.export.error_folder_not_writable')
            );
        }
        if (!$this->stream) {
            $feedUrl = $this->feed->getUrl();
            if ($feedUrl && php_sapi_name() != "cli") {
                LengowMain::log(
                    'Export',
                    LengowMain::setLogMessage('log.export.your_feed_available_here', array('feed_url' => $feedUrl)),
                    $this->logOutput
                );
            }
        }
    }

    /**
     * Load cache combinations
     *
     * @param LengowProduct $product Lengow product instance
     * @param array $fields list of fields
     *
     * @return boolean
     */
    public function loadCacheCombinations($product, $fields)
    {
        if (!isset($this->cacheCombination[$product->id])) {
            $this->cacheCombination = array();
            $combinations = $product->getCombinations();
            if (empty($combinations)) {
                return false;
            }
            foreach ($combinations as $combination) {
                $paId = $combination['id_product_attribute'];
                foreach ($fields as $field) {
                    if (isset(self::$defaultFields[$field])) {
                        $this->cacheCombination[$product->id][$paId][$field] = $product->getData(
                            self::$defaultFields[$field],
                            $paId
                        );
                    } else {
                        $this->cacheCombination[$product->id][$paId][$field] = $product->getData($field, $paId);
                    }
                }
            }
        }
        return true;
    }

    /**
     * Get Total product (Active/Inactive, In Stock/ Out Stock)
     *
     * @return integer
     */
    public function getTotalProduct()
    {
        if (_PS_VERSION_ >= '1.5') {
            $join = ' INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps ON
            (ps.id_product = p.id_product AND ps.id_shop = ' . (int)$this->idShop . ') ';
        } else {
            $join = '';
        }
        if (_PS_VERSION_ < '1.5') {
            $where = ' WHERE p.active = 1 ';
        } else {
            $where = ' WHERE ps.active = 1 ';
        }
        $query = ' SELECT SUM(total) as total FROM (';
        $query .= ' ( SELECT COUNT(*) as total';
        $query .= ' FROM ' . _DB_PREFIX_ . 'product p ' . $join . ' ' . $where . ')';
        $query .= ' UNION ';
        $query .= ' ( SELECT COUNT(*) as total';
        $query .= ' FROM ' . _DB_PREFIX_ . 'product p';
        $query .= ' INNER JOIN ' . _DB_PREFIX_ . 'product_attribute pa ON (pa.id_product = p.id_product)';
        $query .= ' ' . $join . ' ' . $where . ' ) ';
        $query .= '  ) as tmp ';
        try {
            $collection = Db::getInstance()->executeS($query);
            return (int)$collection[0]['total'];
        } catch (PrestaShopDatabaseException $e) {
            return 0;
        }
    }

    /**
     * Get Count export product
     *
     * @return integer
     */
    public function getTotalExportProduct()
    {
        if ($this->variation) {
            $query = ' SELECT SUM(total) as total FROM ( ( ';
            $query .= 'SELECT COUNT(*) as total ' . $this->buildTotalQuery();
            $query .= ' ) UNION ( ';
            $query .= 'SELECT COUNT(*) as total ' . $this->buildTotalQuery(true);
            $query .= ' ) ) as tmp';
        } else {
            $query = 'SELECT COUNT(*) as total ' . $this->buildTotalQuery();
        }
        try {
            $collection = Db::getInstance()->executeS($query);
            return (int)$collection[0]['total'];
        } catch (PrestaShopDatabaseException $e) {
            return 0;
        }
    }

    /**
     * Get Count export product
     *
     * @param boolean $variation count variation product
     *
     * @return string
     */
    public function buildTotalQuery($variation = false)
    {
        $where = array();
        $query = ' FROM ' . _DB_PREFIX_ . 'product p';
        if ($this->selection) {
            $query .= ' INNER JOIN ' . _DB_PREFIX_ . 'lengow_product lp ON (lp.id_product = p.id_product AND
            lp.id_shop = ' . (int)$this->idShop . ')';
        }
        if ($variation) {
            $query .= ' INNER JOIN ' . _DB_PREFIX_ . 'product_attribute pa ON (pa.id_product = p.id_product) ';
        }
        if (_PS_VERSION_ >= '1.5') {
            $query .= ' INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps ON
            (ps.id_product = p.id_product AND ps.id_shop = ' . (int)$this->idShop . ') ';
        }
        if (!$this->inactive) {
            if (_PS_VERSION_ < '1.5') {
                $where[] = ' p.active = 1 ';
            } else {
                $where[] = ' ps.active = 1 ';
            }
        }
        if (!(_PS_VERSION_ < '1.5')) {
            $where[] = ' ps.id_shop = ' . (int)$this->idShop;
        }
        if (!$this->outOfStock) {
            if (_PS_VERSION_ >= '1.5') {
                if ($variation) {
                    $query .= ' INNER JOIN ' . _DB_PREFIX_ . 'stock_available sa ON
                    (sa.id_product=p.id_product
                    AND pa.id_product_attribute = sa.id_product_attribute
                    AND sa.id_shop = ' . (int)$this->idShop . '
                    AND sa.quantity > 0)';
                } else {
                    $query .= ' INNER JOIN ' . _DB_PREFIX_ . 'stock_available sa ON
                    (sa.id_product=p.id_product AND id_product_attribute = 0 AND sa.quantity > 0
                    AND sa.id_shop = ' . (int)$this->idShop . ' )';
                }
            } else {
                $where[] = ' p.`quantity` > 0';
            }
        }
        if ($this->productIds != null) {
            $where[] = ' p.`id_product` IN (' . implode(',', $this->productIds) . ')';
        }
        if (count($where) > 0) {
            $query .= ' WHERE ' . join(' AND ', $where);
        }
        return $query;
    }

    /**
     * Get the products to export
     *
     * @return array
     */
    public function exportIds()
    {
        if ($this->variation) {
            $query = ' SELECT * FROM ( ( ';
            $query .= 'SELECT p.id_product, \'0\' as id_product_attribute ' . $this->buildTotalQuery();
            $query .= ' ) UNION ( ';
            $query .= 'SELECT p.id_product, pa.id_product_attribute ' . $this->buildTotalQuery(true);
            $query .= ' ) ) as tmp ORDER BY id_product, id_product_attribute';
        } else {
            $query = 'SELECT p.id_product, \'0\' as id_product_attribute ' . $this->buildTotalQuery();
        }
        if ($this->limit > 0) {
            if ($this->offset > 0) {
                $query .= ' LIMIT ' . ((int)$this->offset) . ', ' . (int)$this->limit . ' ';
            } else {
                $query .= ' LIMIT 0,' . (int)$this->limit . ' ';
            }
        }
        try {
            return Db::getInstance()->executeS($query);
        } catch (PrestaShopDatabaseException $e) {
            return array();
        }
    }

    /**
     * Get fields to export
     *
     * @return array
     */
    protected function getFields()
    {
        $fields = array();
        // Check field name to lower to avoid duplicates
        $fieldsToLower = array();
        foreach (self::$defaultFields as $key => $value) {
            // This line is useless, but Prestashop validator require it
            $value = $value;
            $fields[] = $key;
            $fieldsToLower[] = Tools::strtolower($key);
        }
        // Get product Features
        $features = Feature::getFeatures($this->language->id);
        foreach ($features as $feature) {
            if (!in_array(Tools::strtolower($feature['name']), $fieldsToLower)) {
                $fields[] = $feature['name'];
                $fieldsToLower[] = Tools::strtolower($feature['name']);
            } else {
                if ($this->legacy) {
                    $fields[] = $feature['name'] . '_1';
                }
            }
        }
        // If export product variations -> get variations attributes
        if ($this->variation) {
            $attributes = AttributeGroup::getAttributesGroups($this->language->id);
            foreach ($attributes as $attribute) {
                // don't export empty attributes
                if ($attribute['name'] == '') {
                    continue;
                }
                if (!in_array(Tools::strtolower($attribute['name']), $fieldsToLower)) {
                    $fields[] = $attribute['name'];
                    $fieldsToLower[] = Tools::strtolower($attribute['name']);
                } else {
                    if ($this->legacy) {
                        $fields[] = $attribute['name'] . '_2';
                    }
                }
            }
        }
        // Allow to add extra fields
        return static::setAdditionalFields($fields);
    }

    /**
     * Get filename of generated feeds
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->feed->getFilename();
    }

    /**
     * Get all export available parameters
     *
     * @return string
     */
    public function getExportParams()
    {
        $params = array();
        foreach (self::$exportParams as $param) {
            switch ($param) {
                case 'mode':
                    $authorizedValue = array('size', 'total');
                    $type = 'string';
                    $example = 'size';
                    break;
                case 'format':
                    $authorizedValue = LengowFeed::$availableFormats;
                    $type = 'string';
                    $example = 'csv';
                    break;
                case 'shop':
                    $availableShops = array();
                    $shops = LengowShop::findAll(true);
                    foreach ($shops as $shop) {
                        $availableShops[] = $shop['id_shop'];
                    }
                    $authorizedValue = $availableShops;
                    $type = 'integer';
                    $example = 1;
                    break;
                case 'currency':
                    $availableCurrencies = array();
                    $currencies = Currency::getCurrencies();
                    foreach ($currencies as $currency) {
                        $availableCurrencies[] = $currency['iso_code'];
                    }
                    $authorizedValue = $availableCurrencies;
                    $type = 'string';
                    $example = 'EUR';
                    break;
                case 'language':
                    $availableLanguages = array();
                    $languages = Language::getLanguages();
                    foreach ($languages as $language) {
                        $availableLanguages[] = $language['iso_code'];
                    }
                    $authorizedValue = $availableLanguages;
                    $type = 'string';
                    $example = 'fr';
                    break;
                case 'offset':
                case 'limit':
                    $authorizedValue = 'all integers';
                    $type = 'integer';
                    $example = 100;
                    break;
                case 'product_ids':
                    $authorizedValue = 'all integers';
                    $type = 'string';
                    $example = '101,108,215';
                    break;
                default:
                    $authorizedValue = array(0, 1);
                    $type = 'integer';
                    $example = 1;
                    break;
            }
            $params[$param] = array(
                'authorized_values' => $authorizedValue,
                'type' => $type,
                'example' => $example
            );
        }

        return Tools::jsonEncode($params);
    }

    /**
     * Override this function in override/lengow.export.class.php to add header
     *
     * @param array $fields fields to export
     *
     * @return array
     */
    public static function setAdditionalFields($fields)
    {
        /**
         * Write here your process
         *
         * ex : fields[] = 'my_header_value';
         */
        return $fields;
    }

    /**
     * Override this function to assign data for additional fields
     *
     * @param LengowProduct $product Lengow product instance
     * @param integer $idProductAttribute Prestashop product attribute id
     * @param array $arrayProduct product data
     *
     * @return array
     */
    public static function setAdditionalFieldsValues($product, $idProductAttribute = null, $arrayProduct = null)
    {
        /**
         * Write here your process
         * $arrayProduct['my_header_value'] = 'your value';
         */
        // This two lines are useless, but Prestashop validator require it
        $product = $product;
        $idProductAttribute = $idProductAttribute;
        return $arrayProduct;
    }
}
