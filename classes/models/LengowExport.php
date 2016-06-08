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
    public static $DEFAULT_FIELDS = array(
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
     * Include active products
     */
    protected $showInactiveProduct = false;

    /**
     * Export out of stock product
     */
    protected $exportOutStock = false;

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
     * string #format : Export Format (csv|yaml|xml|json)
     * boolean #stream : Display file when call script (1) | Save File (0)
     * boolean #out_stock : Export product in stock and out stock (1) | Export Only in stock product (0)
     * int #limit : Limit product to export
     * boolean #show_inactive_product : Export active and inactive product (1) | Export Only active product (0)
     * boolean #show_product_combination : Export product declinaison (1) | Export Only simple product (0)
     * boolean #update_export_date : Update 'LENGOW_LAST_EXPORT' when launching export process (1)
     *                              | Do not update 'LENGOW_LAST_EXPORT' when exporting from toolbox (0)
     * @return LengowExport
     */
    public function __construct($params = array())
    {
        $this->setFormat(isset($params["format"]) ? $params["format"] : 'csv');
        $this->offset = (isset($params["offset"]) ? $params["offset"] : false);
        $this->productIds = (isset($params["product_ids"]) ? $params["product_ids"] : false);
        $this->stream = (isset($params["stream"]) ? $params["stream"] : false);
        $this->limit =  (isset($params["limit"]) ? (int)$params["limit"] : false);
        $this->showInactiveProduct = (isset($params["show_inactive_product"]) ?
            (bool)$params["show_inactive_product"] : false);
        $this->shopId = (int)(isset($params["shop_id"]) ? (int)$params["shop_id"] : Context::getContext()->shop->id);
        $this->language = isset($params["language_id"]) ?
            new Language($params["language_id"]) :
            new Language(LengowConfiguration::get('PS_LANG_DEFAULT', null, null, $this->shopId));
        $this->exportLengowSelection = (isset($params["selection"]) ?
            (bool)$params["selection"] :
            Configuration::get('LENGOW_EXPORT_SELECTION_ENABLED', null, null, $this->shopId));
        $this->exportOutStock =  (isset($params["out_stock"]) ?
            $params["out_stock"] :
            Configuration::get('LENGOW_EXPORT_OUT_STOCK', null, null, $this->shopId));
        $this->exportVariation = isset($params["export_variation"]) ?
            (bool)$params["export_variation"] :
            (bool)Configuration::get('LENGOW_EXPORT_VARIATION_ENABLED', null, null, $this->shopId);
        $this->log_output = (isset($params['log_output']) ? (bool)$params['log_output'] : !$this->stream);
        $this->updateExportDate = (isset($params['update_export_date']) ? (bool)$params['update_export_date'] : true);
        if (!Context::getContext()->currency) {
            Context::getContext()->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }
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
     * Execute the export
     *
     * @return mixed
     */
    public function exec()
    {
        try {
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
                Configuration::updatevalue('LENGOW_LAST_EXPORT', date('Y-m-d H:i:s'), null, null, $this->shopId);
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
        $file_feed = null;
        $this->feed = new LengowFeed(
            $this->stream,
            $this->format,
            isset($shop->name) ? $shop->name : 'default',
            $file_feed
        );
        $this->feed->write('header', $fields);
        $is_first = true;
        foreach ($products as $p) {
            $product_data = array();
            if ($p['id_product'] && $p['id_product_attribute'] == 0) {
                $product = new LengowProduct(
                    $p['id_product'],
                    $this->language->id,
                    array(
                        "carrier" => $this->carrier,
                        "image_size" => LengowProduct::getMaxImageType(),
                        "language" => $this->language
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
                $this->feed->write('body', $product_data, $is_first);
                $product_count++;
            }
            if ($p['id_product'] && $p['id_product_attribute'] > 0) {
                $this->loadCacheCombinations($p['id_product'], $fields);
                if (isset($this->cacheCombination[$p['id_product']][$p['id_product_attribute']])) {
                    $this->feed->write('body', $this->cacheCombination[$p['id_product']][$p['id_product_attribute']]);
                    $product_count++;
                }
            }
            if ($product_count > 0 && $product_count % 10 == 0) {
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
                "carrier" => $this->carrier,
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
        if ($this->exportVariation) {
            $query = ' SELECT SUM(total) as total FROM (';
            $query.= ' ( SELECT COUNT(*) as total';
            $query.= ' FROM '._DB_PREFIX_.'product p '.$join.' '.$where.')';
            $query.= ' UNION ';
            $query.= ' ( SELECT COUNT(*) as total';
            $query.= ' FROM '._DB_PREFIX_.'product p';
            $query.= ' INNER JOIN '._DB_PREFIX_.'product_attribute pa ON (pa.id_product = p.id_product)';
            $query.= ' '.$join.' '.$where.' ) ';
            $query.= '  ) as tmp ';
        } else {
            $query = ' SELECT COUNT(*) as total';
            $query.= ' FROM '._DB_PREFIX_.'product p '.$join.' '.$where.'';
        }
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
                $fields[] = $feature['name'] . '_1';
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
                    $fields[] = $attribute['name'] . '_2';
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
