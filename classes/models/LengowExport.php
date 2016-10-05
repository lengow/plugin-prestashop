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
 * Lengow Export class
 */
class LengowExport
{
    /**
     * Default fields for export
     */
    public static $DEFAULT_FIELDS;

    /**
     * All available params for export
     */
    public static $EXPORT_PARAMS = array(
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
     * New fields for v3
     */
    protected $new_fields = array(
        'id'                             => 'id',
        'sku'                            => 'reference',
        'sku_supplier'                   => 'supplier_reference',
        'ean'                            => 'ean',
        'upc'                            => 'upc',
        'name'                           => 'name',
        'quantity'                       => 'quantity',
        'minimal_quantity'               => 'minimal_quantity',
        'availability'                   => 'available',
        'is_virtual'                     => 'is_virtual',
        'condition'                      => 'condition',
        'category'                       => 'breadcrumb',
        'status'                         => 'active',
        'url'                            => 'url',
        'url_rewrite'                    => 'url_rewrite',
        'price_excl_tax'                 => 'price_sale_duty_free',
        'price_incl_tax'                 => 'price_sale',
        'price_before_discount_excl_tax' => 'price_duty_free',
        'price_before_discount_incl_tax' => 'price',
        'discount_percent'               => 'price_sale_percent',
        'discount_start_date'            => 'sale_from',
        'discount_end_date'              => 'sale_to',
        'ecotax'                         => 'ecotax',
        'shipping_cost'                  => 'price_shipping',
        'shipping_delay'                 => 'delivery_time',
        'currency'                       => 'currency',
        'image_url_1'                    => 'image_1',
        'image_url_2'                    => 'image_2',
        'image_url_3'                    => 'image_3',
        'image_url_4'                    => 'image_4',
        'image_url_5'                    => 'image_5',
        'image_url_6'                    => 'image_6',
        'image_url_7'                    => 'image_7',
        'image_url_8'                    => 'image_8',
        'image_url_9'                    => 'image_9',
        'image_url_10'                   => 'image_10',
        'type'                           => 'type',
        'parent_id'                      => 'id_parent',
        'variation'                      => 'variation',
        'language'                       => 'language',
        'description'                    => 'description',
        'description_short'              => 'short_description',
        'description_html'               => 'description_html',
        'meta_keyword'                   => 'meta_keywords',
        'meta_description'               => 'meta_description',
        'manufacturer'                   => 'manufacturer',
        'supplier'                       => 'supplier',
        'weight'                         => 'weight',
    );

    /**
     * Legacy fields for export
     */
    protected $legacy_fields = array(
        'id_product'            => 'id',
        'name_product'          => 'name',
        'reference_product'     => 'reference',
        'supplier_reference'    => 'supplier_reference',
        'manufacturer'          => 'manufacturer',
        'category'              => 'breadcrumb',
        'description'           => 'description',
        'description_short'     => 'short_description',
        'price_product'         => 'price',
        'wholesale_price'       => 'wholesale_price',
        'price_ht'              => 'price_duty_free',
        'price_reduction'       => 'price_sale',
        'pourcentage_reduction' => 'price_sale_percent',
        'quantity'              => 'quantity',
        'weight'                => 'weight',
        'ean'                   => 'ean',
        'upc'                   => 'upc',
        'ecotax'                => 'ecotax',
        'active'                => 'active',
        'available_product'     => 'available',
        'url_product'           => 'url',
        'fdp'                   => 'price_shipping',
        'id_mere'               => 'id_parent',
        'delais_livraison'      => 'delivery_time',
        'image_product_1'       => 'image_1',
        'image_product_2'       => 'image_2',
        'image_product_3'       => 'image_3',
        'image_product_4'       => 'image_4',
        'image_product_5'       => 'image_5',
        'image_product_6'       => 'image_6',
        'image_product_7'       => 'image_7',
        'image_product_8'       => 'image_8',
        'image_product_9'       => 'image_9',
        'image_product_10'      => 'image_10',
        'reduction_from'        => 'sale_from',
        'reduction_to'          => 'sale_to',
        'meta_keywords'         => 'meta_keywords',
        'meta_description'      => 'meta_description',
        'url_rewrite'           => 'url_rewrite',
        'product_type'          => 'type',
        'product_variation'     => 'variation',
        'currency'              => 'currency',
        'condition'             => 'condition',
        'supplier'              => 'supplier',
        'minimal_quantity'      => 'minimal_quantity',
        'is_virtual'            => 'is_virtual',
        'available_for_order'   => 'available_for_order',
        'available_date'        => 'available_date',
        'show_price'            => 'show_price',
        'visibility'            => 'visibility',
        'available_now'         => 'available_now',
        'available_later'       => 'available_later',
        'stock_availables'      => 'stock_availables',
        'description_html'      => 'description_html',
        'availability'          => 'availability',
    );

    /**
     * Additional head attributes export
     */
    protected $head_attributes_export;

    /**
     * Additional head image export
     */
    protected $head_images_export;

    /**
     * Format to return
     */
    protected $format;

    /**
     * Product's Carrier
     */
    protected $carrier;

    /**
     * Feed
     */
    protected $feed;

    /**
     * Filename
     */
    protected $filename;

    /**
     * Full export products + attributes
     */
    protected $full = true;

    /**
     * Current Shop Id
     */
    protected $shopId;

    /**
     * Export selected products
     */
    protected $all = false;

    /**
     * Max images
     */
    protected $maxImages = 10;

    /**
     * Attributes to export
     */
    protected $attributes = array();

    /**
     * Features to export
     */
    protected $features = array();

    /**
     * Stream return
     */
    protected $stream = true;

    /**
     * Product data
     */
    protected $data = array();

    /**
     * Export Lengow selection
     */
    protected $exportLengowSelection = false;

    /**
     * Export out of stock product
     */
    protected $exportOutStock = false;

    /**
     * Export product variations
     */
    protected $exportVariation = true;

    /**
     * Include active products
     */
    protected $showInactiveProduct = false;

    /**
     * Use legacy fields
     */
    protected $legacy = false;

    /**
     * @var integer amount of products to export
     */
    protected $limit = 0;

    /**
     * @var array product ids to be exported
     */
    protected $product_ids = array();

    /**
     * Update export date.
     */
    protected $updateExportDate;

    /**
     * Cache combination
     */
    protected $cacheCombination;

    /**
     * Construct new Lengow export.
     *
     * @param array params optional options
     * int     $limit              The number of product to be exported
     * int     $offset             From what product export
     * int     $shop_id            Shop id for export
     * int     $language_id        language for export
     * string  $product_ids        Ids product to export
     * string  $format             Export Format (csv|yaml|xml|json)
     * boolean $stream             Display file when call script (1) | Save File (0)
     * boolean $out_of_stock       Export product in stock and out stock (1) | Export Only in stock product (0)
     * boolean $selection          Export selected product (1) | Export all products (0)
     * boolean $inactive           Export active and inactive product (1) | Export Only active product (0)
     * boolean $variation          Export product variation (1) | Export Only simple product (0)
     * boolean $legacy_fields      Export with legacy fields (1) | Export with new fields (0)
     * boolean $update_export_date Update 'LENGOW_LAST_EXPORT' when launching export process (1)
     *                                | Do not update 'LENGOW_LAST_EXPORT' when exporting from toolbox (0)
     * boolean $log_output         See logs (only when stream = 0) (1) | no logs (0)
     *
     * @return LengowExport
     */
    public function __construct($params = array())
    {
        $this->setFormat(isset($params["format"]) ? $params["format"] : 'csv');
        $this->offset = (isset($params["offset"]) ? $params["offset"] : false);
        $this->productIds = (isset($params["product_ids"]) ? $params["product_ids"] : false);
        $this->stream = (isset($params["stream"]) ? $params["stream"] : false);
        $this->limit =  (isset($params["limit"]) ? (int)$params["limit"] : false);
        $this->shopId = (int)(isset($params["shop_id"]) ? (int)$params["shop_id"] : Context::getContext()->shop->id);
        $this->language = (isset($params["language_id"])
            ? new Language($params["language_id"])
            : new Language(Configuration::get('PS_LANG_DEFAULT', null, null, $this->shopId))
        );
        $this->exportLengowSelection = (isset($params["selection"])
            ? (bool)$params["selection"]
            : (bool)LengowConfiguration::get('LENGOW_EXPORT_SELECTION_ENABLED', null, null, $this->shopId)
        );
        $this->exportOutStock = (isset($params["out_of_stock"])
            ? (bool)$params["out_of_stock"]
            : (bool)LengowConfiguration::get('LENGOW_EXPORT_OUT_STOCK', null, null, $this->shopId)
        );
        $this->exportVariation = (isset($params["variation"])
            ? (bool)$params["variation"]
            : (bool)LengowConfiguration::get('LENGOW_EXPORT_VARIATION_ENABLED', null, null, $this->shopId)
        );
        $this->showInactiveProduct = (isset($params["inactive"]) ? (bool)$params["inactive"] : false);
        if ($this->stream) {
            $this->log_output = false;
        } else {
            $this->log_output = isset($params['log_output']) ? (bool)$params['log_output'] : true;
        }
        $this->updateExportDate = (isset($params['update_export_date']) ? (bool)$params['update_export_date'] : true);
        if (!Context::getContext()->currency) {
            Context::getContext()->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }
        $this->legacy = isset($params['legacy_fields']) ? (bool)$params['legacy_fields'] : null;
        $this->checkCurrency();
        $this->setCarrier();
        return $this;
    }

    /**
     * Check currency to export
     *
     * @throws LengowException
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
     * @throws LengowException
     *
     * @return boolean
     */
    public function setCarrier()
    {
        $carrier = LengowCarrier::getActiveCarrier();
        if (!$carrier->id) {
            throw new LengowException(LengowMain::setLogMessage('log.export.error_no_carrier_selected'));
        }
        $this->carrier = $carrier;
        return true;
    }

    /**
     * Set format to export
     *
     * @param string $format The export format
     *
     * @throws LengowException
     *
     * @return boolean.
     */
    public function setFormat($format)
    {
        if (!in_array($format, LengowFeed::$AVAILABLE_FORMATS)) {
            throw new LengowException(LengowMain::setLogMessage('log.export.error_illegal_export_format'));
        }
        $this->format = $format;
        return true;
    }

    /**
     * Set or not legacy fields to export
     */
    public function setLegacyFields()
    {
        if (is_null($this->legacy)) {
            $result = LengowConnector::queryApi(
                'get',
                '/v3.0/subscriptions',
                $this->shopId
            );
            if (isset($result->legacy)) {
                $this->legacy = (bool)$result->legacy;
            } else {
                $this->legacy = false;
            }
        }
        LengowExport::$DEFAULT_FIELDS = $this->legacy ? $this->legacy_fields : $this->new_fields;
    }

    /**
     * Execute the export
     *
     * @return mixed
     */
    public function exec()
    {
        try {
            // clean logs
            LengowMain::cleanLog();
            LengowMain::log('Export', LengowMain::setLogMessage('log.export.start'), $this->log_output);
            $shop = new LengowShop($this->shopId);
            LengowMain::log(
                'Export',
                LengowMain::setLogMessage('log.export.start_for_shop', array(
                    'name_shop' => $shop->name,
                    'id_shop'   => $shop->id
                )),
                $this->log_output
            );
            // set legacy fields option
            $this->setLegacyFields();
            // get fields to export
            $export_fields = $this->getFields();
            // get products to be exported
            $products = $this->exportIds();
            LengowMain::log(
                'Export',
                LengowMain::setLogMessage('log.export.nb_product_found', array("nb_product" => count($products))),
                $this->log_output
            );
            $this->export($products, $export_fields, $shop);
            if ($this->updateExportDate) {
                LengowConfiguration::updateValue('LENGOW_LAST_EXPORT', date('Y-m-d H:i:s'), false, null, $this->shopId);
            }
            LengowMain::log(
                'Export',
                LengowMain::setLogMessage('log.export.end'),
                $this->log_output
            );
        } catch (LengowException $e) {
            $error_message = $e->getMessage();
        } catch (Exception $e) {
            $error_message = '[Prestashop error] "'.$e->getMessage().'" '.$e->getFile().' | '.$e->getLine();
        }
        if (isset($error_message)) {
            $decoded_message = LengowMain::decodeLogMessage($error_message, 'en');
            LengowMain::log(
                'Export',
                LengowMain::setLogMessage('log.export.export_failed', array(
                    'decoded_message' => $decoded_message
                )),
                $this->log_output
            );
        }
    }

    /**
     * Export products
     *
     * @param array $products list of products to be exported
     * @param array $fields   list of fields
     * @param Shop  $shop     shop being exported
     */
    public function export($products, $fields, $shop)
    {
        $product_count = 0;
        $this->feed = new LengowFeed(
            $this->stream,
            $this->format,
            $this->legacy,
            isset($shop->name) ? $shop->name : 'default'
        );
        $this->feed->write('header', $fields);
        $is_first = true;
        // Get the maximum of character for yaml format
        $max_character = 0;
        foreach ($fields as $field) {
            if (Tools::strlen($field) > $max_character) {
                $max_character = Tools::strlen($field);
            }
        }
        foreach ($products as $p) {
            $product_data = array();
            if ($p['id_product'] && $p['id_product_attribute'] == 0) {
                $product = new LengowProduct(
                    $p['id_product'],
                    $this->language->id,
                    array(
                        "carrier"    => $this->carrier,
                        "image_size" => LengowProduct::getMaxImageType(),
                        "language"   => $this->language
                    )
                );
                foreach ($fields as $field) {
                    if (isset(LengowExport::$DEFAULT_FIELDS[$field])) {
                        $product_data[$field] = $product->getData(
                            LengowExport::$DEFAULT_FIELDS[$field],
                            null
                        );
                    } else {
                        $product_data[$field] = $product->getData($field, null);
                    }
                }
                // write parent product
                $this->feed->write('body', $product_data, $is_first, $max_character);
                $product_count++;
            }
            if ($p['id_product'] && $p['id_product_attribute'] > 0) {
                $this->loadCacheCombinations($p['id_product'], $fields);
                if (isset($this->cacheCombination[$p['id_product']][$p['id_product_attribute']])) {
                    $this->feed->write(
                        'body',
                        $this->cacheCombination[$p['id_product']][$p['id_product_attribute']],
                        $is_first,
                        $max_character
                    );
                    $product_count++;
                }
            }
            if ($product_count > 0 && $product_count % 50 == 0) {
                LengowMain::log(
                    'Export',
                    LengowMain::setLogMessage('log.export.count_product', array(
                        'product_count' => $product_count
                    )),
                    $this->log_output
                );
            }
            if ($this->limit > 0 && $product_count >= $this->limit) {
                break;
            }
            $is_first = false;
        }
        $success = $this->feed->end();
        if (!$success) {
            throw new LengowException(
                LengowMain::setLogMessage('log.export.error_folder_not_writable')
            );
        }
        if (!$this->stream) {
            $feed_url = $this->feed->getUrl();
            if ($feed_url && php_sapi_name() != "cli") {
                LengowMain::log(
                    'Export',
                    LengowMain::setLogMessage('log.export.your_feed_available_here', array('feed_url' => $feed_url)),
                    $this->log_output
                );
            }
        }
    }

    /**
     * Load cache combinations
     *
     * @param integer $productId product id
     * @param array   $fields    list of fields
     */
    public function loadCacheCombinations($productId, $fields)
    {
        if (isset($this->cacheCombination[$productId])) {
            return $this->cacheCombination[$productId];
        }
        unset($this->cacheCombination);
        $product = new LengowProduct(
            $productId,
            $this->language->id,
            array(
                "carrier"    => $this->carrier,
                "image_size" => LengowProduct::getMaxImageType()
            )
        );
        $combinations = $product->getCombinations();
        if (empty($combinations)) {
            throw new LengowException(LengowMain::setLogMessage('log.export.error_no_product_combination'));
        }
        foreach ($combinations as $combination) {
            $paId = $combination['id_product_attribute'];
            foreach ($fields as $field) {
                if (isset(LengowExport::$DEFAULT_FIELDS[$field])) {
                    $this->cacheCombination[$productId][$paId][$field] = $product->getData(
                        LengowExport::$DEFAULT_FIELDS[$field],
                        $paId
                    );
                } else {
                    $this->cacheCombination[$productId][$paId][$field] = $product->getData(
                        $field,
                        $paId
                    );
                }
            }
        }
    }

    /**
     * Get Total product (Active/Inactive, In Stock/ Out Stock)
     *
     * @return integer
     */
    public function getTotalProduct()
    {
        if (_PS_VERSION_ >= '1.5') {
            $join = ' INNER JOIN '._DB_PREFIX_.'product_shop ps ON
            (ps.id_product = p.id_product AND ps.id_shop = '.(int)$this->shopId.') ';
        } else {
            $join = '';
        }
        if (_PS_VERSION_ < '1.5') {
            $where = ' WHERE p.active = 1 ';
        } else {
            $where = ' WHERE ps.active = 1 ';
        }
        $query = ' SELECT SUM(total) as total FROM (';
        $query.= ' ( SELECT COUNT(*) as total';
        $query.= ' FROM '._DB_PREFIX_.'product p '.$join.' '.$where.')';
        $query.= ' UNION ';
        $query.= ' ( SELECT COUNT(*) as total';
        $query.= ' FROM '._DB_PREFIX_.'product p';
        $query.= ' INNER JOIN '._DB_PREFIX_.'product_attribute pa ON (pa.id_product = p.id_product)';
        $query.= ' '.$join.' '.$where.' ) ';
        $query.= '  ) as tmp ';
        $collection = Db::getInstance()->executeS($query);
        return $collection[0]['total'];
    }

    /**
     * Get Count export product
     *
     * @return integer
     */
    public function getTotalExportProduct()
    {
        if ($this->exportVariation) {
            $query = ' SELECT SUM(total) as total FROM ( ( ';
            $query.= 'SELECT COUNT(*) as total '.$this->buildTotalQuery();
            $query.= ' ) UNION ( ';
            $query.= 'SELECT COUNT(*) as total '.$this->buildTotalQuery(true);
            $query.= ' ) ) as tmp';
        } else {
            $query = 'SELECT COUNT(*) as total '.$this->buildTotalQuery();
        }
        $collection = Db::getInstance()->executeS($query);
        return $collection[0]['total'];
    }

    /**
     * Get Count export product
     *
     * @param boolean $variation (count variation product)
     *
     * @return string
     */
    public function buildTotalQuery($variation = false)
    {
        $where = array();
        $query= ' FROM '._DB_PREFIX_.'product p';
        if ($this->exportLengowSelection) {
            $query.= ' INNER JOIN '._DB_PREFIX_.'lengow_product lp ON (lp.id_product = p.id_product AND
            lp.id_shop = '.(int)$this->shopId.')';
        }
        if ($variation) {
            $query.= ' INNER JOIN '._DB_PREFIX_.'product_attribute pa ON (pa.id_product = p.id_product) ';
        }
        if (_PS_VERSION_ >= '1.5') {
            $query.= ' INNER JOIN '._DB_PREFIX_.'product_shop ps ON
            (ps.id_product = p.id_product AND ps.id_shop = '.(int)$this->shopId.') ';
        }
        if (!$this->showInactiveProduct) {
            if (_PS_VERSION_ < '1.5') {
                $where[] = ' p.active = 1 ';
            } else {
                $where[] = ' ps.active = 1 ';
            }
        }
        if (!(_PS_VERSION_ < '1.5')) {
            $where[] = ' ps.id_shop = '.(int)$this->shopId;
        }
        if (!$this->exportOutStock) {
            if (_PS_VERSION_ >= '1.5') {
                if ($variation) {
                    $query.= ' INNER JOIN '._DB_PREFIX_.'stock_available sa ON
                    (sa.id_product=p.id_product
                    AND pa.id_product_attribute = sa.id_product_attribute
                    AND sa.id_shop = '.(int)$this->shopId.'
                    AND sa.quantity > 0)';
                } else {
                    $query.= ' INNER JOIN '._DB_PREFIX_.'stock_available sa ON
                    (sa.id_product=p.id_product AND id_product_attribute = 0 AND sa.quantity > 0
                    AND sa.id_shop = '.(int)$this->shopId.' )';
                }
            } else {
                $where[] = ' p.`quantity` > 0';
            }
        }
        if ($this->productIds != null) {
            $where[] = ' p.`id_product` IN ('.implode(',', $this->productIds).')';
        }
        if (count($where)>0) {
            $query.= ' WHERE '.join(' AND ', $where);
        }
        return $query;
    }

    /**
     * Get the products to export
     *
     * @return array IDs product
     */
    public function exportIds()
    {
        if ($this->exportVariation) {
            $query = ' SELECT * FROM ( ( ';
            $query.= 'SELECT p.id_product, \'0\' as id_product_attribute '.$this->buildTotalQuery();
            $query.= ' ) UNION ( ';
            $query.=  'SELECT p.id_product, pa.id_product_attribute '.$this->buildTotalQuery(true);
            $query.= ' ) ) as tmp ORDER BY id_product, id_product_attribute';
        } else {
            $query = 'SELECT p.id_product, \'0\' as id_product_attribute '.$this->buildTotalQuery();
        }
        if ($this->limit > 0) {
            if ($this->offset > 0) {
                $query.= ' LIMIT '.((int)$this->offset).', '.(int)$this->limit.' ';
            } else {
                $query.= ' LIMIT 0,'.(int)$this->limit.' ';
            }
        }
        return Db::getInstance()->executeS($query);
    }

    /**
     * Get fields to export
     *
     * @return array
     */
    protected function getFields()
    {
        $fields = array();
        foreach (self::$DEFAULT_FIELDS as $key => $value) {
            // This line is useless, but Prestashop validator require it
            $value = $value;
            $fields[] = $key;
        }
        //Features
        $features = Feature::getFeatures($this->language->id);
        foreach ($features as $feature) {
            if (in_array($feature['name'], $fields)) {
                $fields[] = $feature['name'].'_1';
            } else {
                $fields[] = $feature['name'];
            }
        }
        // if export product variations -> get variations attributes
        if ($this->exportVariation) {
            $attributes = AttributeGroup::getAttributesGroups($this->language->id);
            foreach ($attributes as $attribute) {
                //dont export empty attributes
                if ($attribute['name'] == '') {
                    continue;
                }
                if (!in_array($attribute['name'], $fields)) {
                    $fields[] = $attribute['name'];
                } else {
                    $fields[] = $attribute['name'].'_2';
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
        foreach (self::$EXPORT_PARAMS as $param) {
            switch ($param) {
                case 'mode':
                    $authorized_value = array('size', 'total');
                    $type             = 'string';
                    $example          = 'size';
                    break;
                case 'format':
                    $authorized_value = LengowFeed::$AVAILABLE_FORMATS;
                    $type             = 'string';
                    $example          = 'csv';
                    break;
                case 'shop':
                    $available_shops = array();
                    $shops = LengowShop::findAll(true);
                    foreach ($shops as $shop) {
                        $available_shops[] = $shop['id_shop'];
                    }
                    $authorized_value = $available_shops;
                    $type             = 'integer';
                    $example          = 1;
                    break;
                case 'currency':
                    $available_currencies = array();
                    $currencies = Currency::getCurrencies();
                    foreach ($currencies as $currency) {
                        $available_currencies[] = $currency['iso_code'];
                    }
                    $authorized_value = $available_currencies;
                    $type             = 'string';
                    $example          = 'EUR';
                    break;
                case 'language':
                    $available_languages = array();
                    $languages = Language::getLanguages();
                    foreach ($languages as $language) {
                        $available_languages[] = $language['iso_code'];
                    }
                    $authorized_value = $available_languages;
                    $type             = 'string';
                    $example          = 'fr';
                    break;
                case 'offset':
                case 'limit':
                    $authorized_value = 'all integers';
                    $type             = 'integer';
                    $example          = 100;
                    break;
                case 'product_ids':
                    $authorized_value = 'all integers';
                    $type             = 'string';
                    $example          = '101,108,215';
                    break;
                default:
                    $authorized_value = array(0, 1);
                    $type             = 'integer';
                    $example          = 1;
                    break;
            }
            $params[$param] = array(
                'authorized_values' => $authorized_value,
                'type'              => $type,
                'example'           => $example
            );
        }

        return json_encode($params);
    }

    /**
     * Override this function in override/lengow.export.class.php to add header
     *
     * @param array $fields
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
     * @param LengowProduct $product
     * @param integer       $id_product_attribute
     * @param array         $array_product
     *
     * @return array
     */
    public static function setAdditionalFieldsValues($product, $id_product_attribute = null, $array_product = null)
    {
        /**
         * Write here your process
         * $array_product['my_header_value'] = 'your value';
         */
        // This two lines are useless, but Prestashop validator require it.
        $product = $product;
        $id_product_attribute = $id_product_attribute;
        return $array_product;
    }
}
