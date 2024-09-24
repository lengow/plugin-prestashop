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
 * Lengow Export class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class LengowExport
{
    /* Export GET params */
    public const PARAM_TOKEN = 'token';
    public const PARAM_MODE = 'mode';
    public const PARAM_FORMAT = 'format';
    public const PARAM_STREAM = 'stream';
    public const PARAM_OFFSET = 'offset';
    public const PARAM_LIMIT = 'limit';
    public const PARAM_SELECTION = 'selection';
    public const PARAM_OUT_OF_STOCK = 'out_of_stock';
    public const PARAM_PRODUCT_IDS = 'product_ids';
    public const PARAM_VARIATION = 'variation';
    public const PARAM_INACTIVE = 'inactive';
    public const PARAM_SHOP = 'shop';
    public const PARAM_SHOP_ID = 'shop_id';
    public const PARAM_CURRENCY = 'currency';
    public const PARAM_LANGUAGE = 'language';
    public const PARAM_LANGUAGE_ID = 'language_id';
    public const PARAM_LEGACY_FIELDS = 'legacy_fields';
    public const PARAM_LOG_OUTPUT = 'log_output';
    public const PARAM_UPDATE_EXPORT_DATE = 'update_export_date';
    public const PARAM_GET_PARAMS = 'get_params';

    /* Legacy export GET params for old versions */
    public const PARAM_LEGACY_SELECTION = 'all';
    public const PARAM_LEGACY_OUT_OF_STOCK = 'out_stock';
    public const PARAM_LEGACY_PRODUCT_IDS = 'ids';
    public const PARAM_LEGACY_VARIATION = 'mode';
    public const PARAM_LEGACY_INACTIVE = 'active';
    public const PARAM_LEGACY_CURRENCY = 'lang';
    public const PARAM_LEGACY_LANGUAGE = 'lang';

    /**
     * @var array default fields for export
     */
    public static $defaultFields;

    /**
     * @var array all available params for export
     */
    public static $exportParams = [
        self::PARAM_MODE,
        self::PARAM_FORMAT,
        self::PARAM_STREAM,
        self::PARAM_OFFSET,
        self::PARAM_LIMIT,
        self::PARAM_SELECTION,
        self::PARAM_OUT_OF_STOCK,
        self::PARAM_PRODUCT_IDS,
        self::PARAM_VARIATION,
        self::PARAM_INACTIVE,
        self::PARAM_SHOP,
        self::PARAM_CURRENCY,
        self::PARAM_LANGUAGE,
        self::PARAM_LEGACY_FIELDS,
        self::PARAM_LOG_OUTPUT,
        self::PARAM_UPDATE_EXPORT_DATE,
        self::PARAM_GET_PARAMS,
    ];

    /**
     * @var array legacy fields for export
     */
    protected $legacyFields = [
        'id_product' => 'id',
        'name_product' => 'name',
        'reference_product' => 'sku',
        'supplier_reference' => 'sku_supplier',
        'manufacturer' => 'manufacturer',
        'category' => 'category',
        'description' => 'description',
        'description_short' => 'short_description',
        'price_product' => 'price_before_discount_incl_tax',
        'wholesale_price' => 'price_wholesale',
        'price_ht' => 'price_before_discount_excl_tax',
        'price_reduction' => 'price_incl_tax',
        'pourcentage_reduction' => 'discount_percent',
        'quantity' => 'quantity',
        'weight' => 'weight',
        'ean' => 'ean',
        'upc' => 'upc',
        'ecotax' => 'ecotax',
        'active' => 'status',
        'available_product' => 'availability',
        'url_product' => 'url',
        'fdp' => 'shipping_cost',
        'id_mere' => 'parent_id',
        'delais_livraison' => 'shipping_delay',
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
        'reduction_from' => 'discount_start_date',
        'reduction_to' => 'discount_end_date',
        'meta_title' => 'meta_title',
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
        'availability' => 'available',
    ];

    /**
     * @var string format to return
     */
    protected $format;

    /**
     * @var Carrier PrestaShop Carrier instance
     */
    protected $carrier;

    /**
     * @var LengowFeed Feed
     */
    protected $feed;

    /**
     * @var int PrestaShop shop id
     */
    protected $idShop;

    /**
     * @var bool stream return
     */
    protected $stream = true;

    /**
     * @var bool export Lengow selection
     */
    protected $selection = false;

    /**
     * @var bool export out of stock product
     */
    protected $outOfStock = false;

    /**
     * @var string export language
     */
    protected $language;

    /**
     * @var bool export product variations
     */
    protected $variation = true;

    /**
     * @var bool include active products
     */
    protected $inactive = false;

    /**
     * @var bool see log or not
     */
    protected $logOutput;

    /**
     * @var bool use legacy fields
     */
    protected $legacy = false;

    /**
     * @var int amount of products to export
     */
    protected $limit = 0;

    /**
     * @var int offset of total product
     */
    protected $offset = 0;

    /**
     * @var array product ids to be exported
     */
    protected $productIds = [];

    /**
     * @var bool update export date
     */
    protected $updateExportDate;

    /**
     * @var array cache combination
     */
    public $cacheCombination;

    /**
     * @var array excluded products for export
     */
    protected $excludedProducts = [];

    /**
     * Construct new Lengow export
     *
     * @param array $params optional options
     *                      integer limit              The number of product to be exported
     *                      integer offset             From what product export
     *                      integer shop_id            Shop id for export
     *                      integer language_id        language for export
     *                      string  product_ids        Ids product to export
     *                      string  format             Export Format (csv|yaml|xml|json)
     *                      boolean stream             Display file when call script (1) | Save File (0)
     *                      boolean out_of_stock       Export product in stock and out stock (1) | Export Only in stock product (0)
     *                      boolean selection          Export selected product (1) | Export all products (0)
     *                      boolean inactive           Export active and inactive product (1) | Export Only active product (0)
     *                      boolean variation          Export product variation (1) | Export Only simple product (0)
     *                      boolean legacy_fields      Export with legacy fields (1) | Export with new fields (0)
     *                      boolean update_export_date Update 'LENGOW_LAST_EXPORT' when launching export process (1) or not
     */
    public function __construct($params = [])
    {
        $this->setFormat(isset($params[self::PARAM_FORMAT]) ? $params[self::PARAM_FORMAT] : LengowFeed::FORMAT_CSV);
        $this->offset = isset($params[self::PARAM_OFFSET]) ? (int) $params[self::PARAM_OFFSET] : false;
        $this->productIds = isset($params[self::PARAM_PRODUCT_IDS]) ? $params[self::PARAM_PRODUCT_IDS] : [];
        $this->stream = isset($params[self::PARAM_STREAM]) ? $params[self::PARAM_STREAM] : false;
        $this->limit = isset($params[self::PARAM_LIMIT]) ? (int) $params[self::PARAM_LIMIT] : false;
        $this->idShop = (int) (
        isset($params[self::PARAM_SHOP_ID])
            ? $params[self::PARAM_SHOP_ID]
            : Context::getContext()->shop->id
        );
        $this->language = isset($params[self::PARAM_LANGUAGE_ID])
            ? new Language($params[self::PARAM_LANGUAGE_ID])
            : new Language(Configuration::get('PS_LANG_DEFAULT', null, null, $this->idShop));
        // get specific params in database
        $selection = LengowConfiguration::get(LengowConfiguration::SELECTION_ENABLED, null, null, $this->idShop);
        $outOfStock = LengowConfiguration::get(LengowConfiguration::OUT_OF_STOCK_ENABLED, null, null, $this->idShop);
        $variation = LengowConfiguration::get(LengowConfiguration::VARIATION_ENABLED, null, null, $this->idShop);
        $inactive = LengowConfiguration::get(LengowConfiguration::INACTIVE_ENABLED, null, null, $this->idShop);
        // set default value for new shop
        if ($selection === null) {
            LengowConfiguration::updateValue(LengowConfiguration::SELECTION_ENABLED, 0, null, null, $this->idShop);
            $selection = false;
        } else {
            $selection = (bool) $selection;
        }
        if ($outOfStock === null) {
            LengowConfiguration::updateValue(LengowConfiguration::OUT_OF_STOCK_ENABLED, 1, null, null, $this->idShop);
            $outOfStock = true;
        } else {
            $outOfStock = (bool) $outOfStock;
        }
        if ($variation === null) {
            LengowConfiguration::updateValue(LengowConfiguration::VARIATION_ENABLED, 1, null, null, $this->idShop);
            $variation = true;
        } else {
            $variation = (bool) $variation;
        }
        if ($inactive === null) {
            LengowConfiguration::updateValue(LengowConfiguration::INACTIVE_ENABLED, 0, null, null, $this->idShop);
            $inactive = false;
        } else {
            $inactive = (bool) $inactive;
        }
        $this->selection = isset($params[self::PARAM_SELECTION]) ? (bool) $params[self::PARAM_SELECTION] : $selection;
        $this->outOfStock = isset($params[self::PARAM_OUT_OF_STOCK])
            ? (bool) $params[self::PARAM_OUT_OF_STOCK]
            : $outOfStock;
        $this->variation = isset($params[self::PARAM_VARIATION]) ? (bool) $params[self::PARAM_VARIATION] : $variation;
        $this->inactive = isset($params[self::PARAM_INACTIVE]) ? (bool) $params[self::PARAM_INACTIVE] : $inactive;
        if ($this->stream) {
            $this->logOutput = false;
        } else {
            $this->logOutput = !isset($params[self::PARAM_LOG_OUTPUT]) || $params[self::PARAM_LOG_OUTPUT];
        }
        $this->updateExportDate = !isset($params[self::PARAM_UPDATE_EXPORT_DATE])
            || $params[self::PARAM_UPDATE_EXPORT_DATE];
        if (!Context::getContext()->currency) {
            Context::getContext()->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }
        $this->legacy = isset($params[self::PARAM_LEGACY_FIELDS]) ? (bool) $params[self::PARAM_LEGACY_FIELDS] : null;
    }

    /**
     * Set format to export
     *
     * @param string $format The export format
     */
    public function setFormat($format)
    {
        $this->format = in_array($format, LengowFeed::$availableFormats, true) ? $format : LengowFeed::FORMAT_CSV;
    }

    /**
     * Execute export process
     */
    public function exec()
    {
        try {
            // clean logs
            LengowMain::cleanLog();
            LengowMain::log(LengowLog::CODE_EXPORT, LengowMain::setLogMessage('log.export.start'), $this->logOutput);
            $shop = new LengowShop($this->idShop);
            LengowMain::log(
                LengowLog::CODE_EXPORT,
                LengowMain::setLogMessage(
                    'log.export.start_for_shop',
                    [
                        'name_shop' => $shop->name,
                        'id_shop' => $shop->id,
                    ]
                ),
                $this->logOutput
            );
            // check currency for export
            $this->checkCurrency();
            // set carrier for the calculation of the shipping cost
            $this->setCarrier();
            // set legacy fields option
            $this->setLegacyFields();
            // get fields to export
            $exportFields = $this->getFields();
            // get products to be exported
            $products = $this->exportIds();
            LengowMain::log(
                LengowLog::CODE_EXPORT,
                LengowMain::setLogMessage('log.export.nb_product_found', ['nb_product' => count($products)]),
                $this->logOutput
            );
            $this->export($products, $exportFields, $shop);
            if ($this->updateExportDate) {
                LengowConfiguration::updateValue(
                    LengowConfiguration::LAST_UPDATE_EXPORT,
                    time(),
                    false,
                    null,
                    $this->idShop
                );
            }
            LengowMain::log(
                LengowLog::CODE_EXPORT,
                LengowMain::setLogMessage('log.export.end'),
                $this->logOutput
            );
        } catch (LengowException $e) {
            $errorMessage = $e->getMessage();
        } catch (Exception $e) {
            $errorMessage = '[PrestaShop error]: "' . $e->getMessage()
                . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
        }
        if (isset($errorMessage)) {
            $decodedMessage = LengowMain::decodeLogMessage($errorMessage, LengowTranslation::DEFAULT_ISO_CODE);
            LengowMain::log(
                LengowLog::CODE_EXPORT,
                LengowMain::setLogMessage(
                    'log.export.export_failed',
                    ['decoded_message' => $decodedMessage]
                ),
                $this->logOutput
            );
        }
    }

    /**
     * Check currency to export
     *
     * @return bool
     *
     * @throws LengowException illegal currency
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
     * @return bool
     *
     * @throws LengowException no default carrier selected
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
        if ($this->legacy === null) {
            $merchantStatus = LengowSync::getStatusAccount();
            if ($merchantStatus && isset($merchantStatus['legacy'])) {
                $this->legacy = $merchantStatus['legacy'];
            } else {
                $this->legacy = false;
            }
        }
        self::$defaultFields = $this->legacy ? $this->legacyFields : $this->getNewFields();
    }

    /**
     * Retrieves new fields from the lengow_exported_fields table
     *
     * @return array Array of fields and valuies
     */
    public function getNewFields()
    {
        $sql = 'SELECT prestashop_value, lengow_field, exported FROM ' . _DB_PREFIX_ . 'lengow_exported_fields';
        $result = Db::getInstance()->executeS($sql);


        $newFields = [];
        if ($result) {
            foreach ($result as $row) {
                if ($row['exported'] === '1') {
                    $newFields[$row['lengow_field']] = $row['prestashop_value'];
                }
            }
        }

        return $newFields;
    }

    /**
     * Retrieves fields config from the lengow_exported_fields table
     *
     * @return array Array of fields with their values
     */
    public function getConfigFields()
    {
        $sql = 'SELECT default_key, prestashop_value, lengow_field, exported FROM ' . _DB_PREFIX_ . 'lengow_exported_fields';
        $result = Db::getInstance()->executeS($sql);

        $newFields = [];
        if ($result) {
            foreach ($result as $row) {
                $newFields[$row['default_key']] = [
                    'prestashop_value' => $row['prestashop_value'],
                    'lengow_field' => $row['lengow_field'],
                    'exported' => $row['exported'],
                ];
            }
        }

        return $newFields;
    }


    /**
     * Export products
     *
     * @param array $products list of products to be exported
     * @param array $fields list of fields
     * @param Shop $shop PrestaShop shop being exported
     *
     * @throws Exception|LengowException folder not writable
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
        $this->feed->write(LengowFeed::HEADER, $fields);
        $isFirst = true;
        // get the maximum of character for yaml format
        $maxCharacter = 0;
        foreach ($fields as $field) {
            if (Tools::strlen($field) > $maxCharacter) {
                $maxCharacter = Tools::strlen($field);
            }
        }
        foreach ($products as $p) {
            $idProduct = (int) $p['id_product'];
            $idProductAttribute = (int) $p['id_product_attribute'];
            // ignore products with faulty combinations
            if (in_array($idProduct, $this->excludedProducts, true)) {
                continue;
            }
            $productData = [];
            $product = new LengowProduct(
                $idProduct,
                $this->language->id,
                [
                    'carrier' => $this->carrier,
                    'image_size' => LengowProduct::getMaxImageType(),
                    'language' => $this->language,
                ]
            );
            // export simple and parent products
            if ($idProduct && $idProductAttribute === 0) {
                foreach ($fields as $field) {
                    if (isset(self::$defaultFields[$field])) {
                        $productData[$field] = $product->getData(self::$defaultFields[$field]);
                    } else {
                        $productData[$field] = $product->getData($field);
                    }
                }
                // get additional data
                $productData = self::setAdditionalFieldsValues($product, null, $productData);
                // write parent product
                $this->feed->write(LengowFeed::BODY, $productData, $isFirst, $maxCharacter);
                ++$productCount;
            }
            // export combinations
            if ($idProduct && $idProductAttribute > 0) {
                if (!$this->loadCacheCombinations($product, $fields)) {
                    LengowMain::log(
                        LengowLog::CODE_EXPORT,
                        LengowMain::setLogMessage(
                            'log.export.error_no_product_combination',
                            ['product_id' => $product->id]
                        ),
                        $this->logOutput
                    );
                    // indicates that a product has failed combinations
                    $this->excludedProducts[] = $product->id;
                    continue;
                }
                if (isset($this->cacheCombination[$idProduct][$idProductAttribute])) {
                    // get additional data
                    $combinationDatas = self::setAdditionalFieldsValues(
                        $product,
                        $idProductAttribute,
                        $this->cacheCombination[$idProduct][$idProductAttribute]
                    );
                    $this->feed->write(LengowFeed::BODY, $combinationDatas, $isFirst, $maxCharacter);
                    ++$productCount;
                }
            }
            if ($productCount > 0 && $productCount % 50 === 0) {
                LengowMain::log(
                    LengowLog::CODE_EXPORT,
                    LengowMain::setLogMessage(
                        'log.export.count_product',
                        ['product_count' => $productCount]
                    ),
                    $this->logOutput
                );
            }
            if ($this->limit > 0 && $productCount >= $this->limit) {
                break;
            }
            // clean data for next product
            unset($productData, $product);
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
            $isFirst = false;
        }
        $success = $this->feed->end();
        if (!$success) {
            throw new LengowException(LengowMain::setLogMessage('log.export.error_folder_not_writable'));
        }
        if (!$this->stream) {
            $feedUrl = $this->feed->getUrl();
            if ($feedUrl && php_sapi_name() !== 'cli') {
                LengowMain::log(
                    LengowLog::CODE_EXPORT,
                    LengowMain::setLogMessage('log.export.your_feed_available_here', ['feed_url' => $feedUrl]),
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
     * @return bool
     *
     * @throws Exception
     */
    public function loadCacheCombinations($product, $fields)
    {
        if (!isset($this->cacheCombination[$product->id])) {
            $this->cacheCombination = [];
            $combinations = $product->getCombinations();
            if (empty($combinations)) {
                return false;
            }
            foreach ($combinations as $combination) {
                $idProductAttribute = (int) $combination['id_product_attribute'];
                foreach ($fields as $field) {
                    if (isset(self::$defaultFields[$field])) {
                        $this->cacheCombination[$product->id][$idProductAttribute][$field] = $product->getData(
                            self::$defaultFields[$field],
                            $idProductAttribute
                        );
                    } else {
                        $this->cacheCombination[$product->id][$idProductAttribute][$field] = $product->getData(
                            $field,
                            $idProductAttribute
                        );
                    }
                }
            }
        }

        return true;
    }

    /**
     * Get Total product (Active/Inactive, In Stock/ Out Stock)
     *
     * @return int
     */
    public function getTotalProduct()
    {
        $join = ' INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps
            ON (ps.id_product = p.id_product AND ps.id_shop = ' . (int) $this->idShop . ') ';
        $where = '';
        if (!$this->inactive) {
            $where = ' WHERE ps.active = 1 ';
        }
        $query = ' SELECT SUM(total) as total FROM (';
        $query .= ' ( SELECT COUNT(*) as total';
        $query .= ' FROM ' . _DB_PREFIX_ . 'product p ' . $join . ' ' . $where . ')';
        $query .= ' UNION ALL ';
        $query .= ' ( SELECT COUNT(*) as total';
        $query .= ' FROM ' . _DB_PREFIX_ . 'product p';
        $query .= ' INNER JOIN ' . _DB_PREFIX_ . 'product_attribute pa ON (pa.id_product = p.id_product)';
        $query .= ' ' . $join . ' ' . $where . ' ) ';
        $query .= '  ) as tmp ';
        try {
            $collection = Db::getInstance()->executeS($query);

            return (int) $collection[0]['total'];
        } catch (PrestaShopDatabaseException $e) {
            return 0;
        }
    }

    /**
     * Get Count export product
     *
     * @return int
     */
    public function getTotalExportProduct()
    {
        if ($this->variation) {
            $query = ' SELECT SUM(total) as total FROM ( ( ';
            $query .= 'SELECT COUNT(*) as total ' . $this->buildTotalQuery();
            $query .= ' ) UNION ALL ( ';
            $query .= 'SELECT COUNT(*) as total ' . $this->buildTotalQuery(true);
            $query .= ' ) ) as tmp';
        } else {
            $query = 'SELECT COUNT(*) as total ' . $this->buildTotalQuery();
        }
        try {
            $collection = Db::getInstance()->executeS($query);

            return (int) $collection[0]['total'];
        } catch (PrestaShopDatabaseException $e) {
            return 0;
        }
    }

    /**
     * Get Count export product
     *
     * @param bool $variation count variation product
     *
     * @return string
     */
    public function buildTotalQuery($variation = false)
    {
        $where = [];

        $query = ' FROM ' . _DB_PREFIX_ . 'product p';
        if ($this->selection) {
            $query .= ' INNER JOIN ' . _DB_PREFIX_ . 'lengow_product lp
                ON (lp.id_product = p.id_product
                AND lp.id_shop = ' . (int) $this->idShop . ')';
        }
        if ($variation) {
            $query .= ' INNER JOIN ' . _DB_PREFIX_ . 'product_attribute pa
                ON (pa.id_product = p.id_product) ';
        }
        $query .= ' INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps
            ON (ps.id_product = p.id_product AND ps.id_shop = ' . (int) $this->idShop . ') ';
        if (!$this->inactive) {
            $where[] = ' ps.active = 1 ';
        }
        $where[] = ' ps.id_shop = ' . (int) $this->idShop;
        if (!$this->outOfStock) {
            if ($variation) {
                // verify if multishop and share stock is active
                if (
                    Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 1
                    && Context::getContext()->shop->getContextShopGroup()->share_stock === 1
                ) {
                    $query .= ' INNER JOIN ' . _DB_PREFIX_ . 'stock_available sa ON
                        (sa.id_product=p.id_product
                        AND pa.id_product_attribute = sa.id_product_attribute
                        AND sa.id_shop = 0
                        AND sa.quantity > 0)';
                } else {
                    $query .= ' INNER JOIN ' . _DB_PREFIX_ . 'stock_available sa ON
                        (sa.id_product=p.id_product
                        AND pa.id_product_attribute = sa.id_product_attribute
                        AND sa.id_shop = ' . (int) $this->idShop . '
                        AND sa.quantity > 0)';
                }
            } else {
                $query .= ' INNER JOIN ' . _DB_PREFIX_ . 'stock_available sa ON
                (sa.id_product=p.id_product AND id_product_attribute = 0 AND sa.quantity > 0
                AND sa.id_shop = ' . (int) $this->idShop . ' )';
            }
        }
        if (!empty($this->productIds)) {
            $where[] = ' p.`id_product` IN (' . implode(',', $this->productIds) . ')';
        }
        if (!empty($where)) {
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
            $query .= ' ) UNION ALL ( ';
            $query .= 'SELECT p.id_product, pa.id_product_attribute ' . $this->buildTotalQuery(true);
            $query .= ' ) ) as tmp ORDER BY id_product, id_product_attribute';
        } else {
            $query = 'SELECT p.id_product, \'0\' as id_product_attribute ' . $this->buildTotalQuery();
        }
        if ($this->limit && $this->limit > 0) {
            if ($this->offset && $this->offset > 0) {
                $query .= ' LIMIT ' . $this->offset . ', ' . $this->limit . ' ';
            } else {
                $query .= ' LIMIT 0,' . $this->limit . ' ';
            }
        }
        try {
            return Db::getInstance()->executeS($query);
        } catch (PrestaShopDatabaseException $e) {
            return [];
        }
    }

    /**
     * Get fields to export
     *
     * @return array
     */
    protected function getFields()
    {
        $fields = [];
        // check field name to lower to avoid duplicates
        $formattedFields = [];
        foreach (array_keys(self::$defaultFields) as $key) {
            $fields[] = $key;
            $formattedFields[] = LengowFeed::formatFields($key, $this->format, $this->legacy);
        }
        // get product Features
        $features = Feature::getFeatures($this->language->id);
        foreach ($features as $feature) {
            $formattedFeature = LengowFeed::formatFields($feature['name'], $this->format, $this->legacy);
            if (!in_array($formattedFeature, $formattedFields, true)) {
                $fields[] = $feature['name'];
                $formattedFields[] = $formattedFeature;
            } else {
                if ($this->legacy) {
                    $fields[] = $feature['name'] . '_1';
                }
            }
        }
        // if export product variations -> get variations attributes
        if ($this->variation) {
            $attributes = AttributeGroup::getAttributesGroups($this->language->id);
            foreach ($attributes as $attribute) {
                // don't export empty attributes
                if ($attribute['name'] === '') {
                    continue;
                }
                $formattedAttribute = LengowFeed::formatFields($attribute['name'], $this->format, $this->legacy);
                if (!in_array($formattedAttribute, $formattedFields, true)) {
                    $fields[] = $attribute['name'];
                    $formattedFields[] = $formattedAttribute;
                }
            }
        }

        // allow to add extra fields
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
        $params = [];
        foreach (self::$exportParams as $param) {
            switch ($param) {
                case self::PARAM_MODE:
                    $authorizedValue = ['size', 'total'];
                    $type = 'string';
                    $example = 'size';
                    break;
                case self::PARAM_FORMAT:
                    $authorizedValue = LengowFeed::$availableFormats;
                    $type = 'string';
                    $example = LengowFeed::FORMAT_CSV;
                    break;
                case self::PARAM_SHOP:
                    $availableShops = [];
                    $shops = LengowShop::findAll(true);
                    foreach ($shops as $shop) {
                        $availableShops[] = $shop['id_shop'];
                    }
                    $authorizedValue = $availableShops;
                    $type = 'integer';
                    $example = 1;
                    break;
                case self::PARAM_CURRENCY:
                    $availableCurrencies = [];
                    $currencies = Currency::getCurrencies();
                    foreach ($currencies as $currency) {
                        $availableCurrencies[] = $currency['iso_code'];
                    }
                    $authorizedValue = $availableCurrencies;
                    $type = 'string';
                    $example = 'EUR';
                    break;
                case self::PARAM_LANGUAGE:
                    $availableLanguages = [];
                    $languages = Language::getLanguages();
                    foreach ($languages as $language) {
                        $availableLanguages[] = $language['iso_code'];
                    }
                    $authorizedValue = $availableLanguages;
                    $type = 'string';
                    $example = 'fr';
                    break;
                case self::PARAM_OFFSET:
                case self::PARAM_LIMIT:
                    $authorizedValue = 'all integers';
                    $type = 'integer';
                    $example = 100;
                    break;
                case self::PARAM_PRODUCT_IDS:
                    $authorizedValue = 'all integers';
                    $type = 'string';
                    $example = '101,108,215';
                    break;
                default:
                    $authorizedValue = [0, 1];
                    $type = 'integer';
                    $example = 1;
                    break;
            }
            $params[$param] = [
                'authorized_values' => $authorizedValue,
                'type' => $type,
                'example' => $example,
            ];
        }

        return json_encode($params);
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
        /*
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
     * @param int|null $idProductAttribute PrestaShop product attribute id
     * @param array|null $arrayProduct product data
     *
     * @return array
     */
    public static function setAdditionalFieldsValues($product, $idProductAttribute = null, $arrayProduct = null)
    {
        /**
         * Write here your process
         * $arrayProduct['my_header_value'] = 'your value';
         */
        // this two lines are useless, but PrestaShop validator require it
        $product = $product;
        $idProductAttribute = $idProductAttribute;

        return $arrayProduct;
    }

    public function getProductsListData() {
        $lengowProduct = new LengowProduct();
        $productsData = [];
        try {
            $exportFields = $this->getNewFields();
            $products = $lengowProduct->getIdProductWithMostData();

            foreach ($products as $p) {
                $idProduct = (int)$p['id_product'];
                $idProductAttribute = (int)$p['id_product_attribute'];

                if (in_array($idProduct, $this->excludedProducts, true)) {
                    continue;
                }

                $productData = [];
                $product = new LengowProduct($idProduct, $this->language->id, [
                    'carrier' => $this->carrier,
                    'image_size' => LengowProduct::getMaxImageType(),
                    'language' => $this->language,
                ]);

                if ($idProduct) {
                    foreach ($exportFields as $field) {
                        $data = $product->getData($field, $idProductAttribute);
                        // Ensure data is properly encoded
                        $productData[$field] = $data;
                    }

                    $productsData[] = $productData;
                }
            }
        } catch (Exception $e) {
            LengowMain::log(LengowLog::CODE_EXPORT, LengowMain::setLogMessage('log.export.error', ['message' => $e->getMessage()]), $this->logOutput);
        }

        $lengowFeed = new LengowFeed(1, 'json', false);
        $allProductsArray = [];

        foreach ($productsData as $product) {
            $productJson = $lengowFeed->getBody($product, true, 0);
            if ($productJson !== false) {
                $productArray = json_decode($productJson, true); // Decode the JSON string to an associative array
                if (json_last_error() === JSON_ERROR_NONE) {
                    $allProductsArray[] = $productArray;
                }
            }
        }

        return json_encode($allProductsArray);
    }


}
