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
 * Lengow Feed Controller Class
 */
class LengowFeedController extends LengowController
{
    /**
     * @var LengowList Lengow list instance
     */
    protected $list;

    /**
     * Process Post Parameters
     */
    public function postProcess()
    {
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
        if ($action) {
            switch ($action) {
                case 'change_option_selected':
                    $state = isset($_REQUEST['state']) ? $_REQUEST['state'] : null;
                    $idShop = isset($_REQUEST['id_shop']) ? (int)$_REQUEST['id_shop'] : null;
                    if ($state !== null) {
                        LengowConfiguration::updatevalue(
                            'LENGOW_EXPORT_SELECTION_ENABLED',
                            $state,
                            null,
                            null,
                            $idShop
                        );
                        $state = LengowConfiguration::get('LENGOW_EXPORT_SELECTION_ENABLED', null, null, $idShop);
                        $data = array();
                        $data['shop_id'] = $idShop;
                        if ($state) {
                            $data['state'] = true;
                        } else {
                            $data['state'] = false;
                        }
                        $result = array_merge($data, $this->reloadTotal($idShop));
                        echo Tools::jsonEncode($result);
                    }
                    break;
                case 'select_product':
                    $state = isset($_REQUEST['state']) ? $_REQUEST['state'] : null;
                    $idShop = isset($_REQUEST['id_shop']) ? (int)$_REQUEST['id_shop'] : null;
                    $productId = isset($_REQUEST['id_product']) ? $_REQUEST['id_product'] : null;
                    if ($state !== null) {
                        LengowProduct::publish($productId, $state, $idShop);
                        echo Tools::jsonEncode($this->reloadTotal($idShop));
                    }
                    break;
                case 'load_table':
                    $idShop = isset($_REQUEST['id_shop']) ? (int)$_REQUEST['id_shop'] : null;
                    $data = array();
                    $data['shop_id'] = $idShop;
                    $data['footer_content'] = preg_replace('/\r|\n/', '', $this->buildTable($idShop));
                    if ($this->toolbox) {
                        $data['bootstrap_switch_readonly'] = true;
                    }
                    echo Tools::jsonEncode($data);
                    break;
                case 'lengow_export_action':
                    $idShop = isset($_REQUEST['id_shop']) ? (int)$_REQUEST['id_shop'] : null;
                    $selection = isset($_REQUEST['selection']) ? $_REQUEST['selection'] : null;
                    $selectAll = isset($_REQUEST['select_all']) ? $_REQUEST['select_all'] : null;
                    $exportAction = isset($_REQUEST['export_action']) ? $_REQUEST['export_action'] : null;
                    $data = array();
                    if ($selectAll === 'true') {
                        $this->buildTable($idShop);
                        $sql = $this->list->buildQuery(false, true);
                        try {
                            $db = Db::getInstance()->executeS($sql);
                        } catch (PrestaShopDatabaseException $e) {
                            $db = array();
                        }
                        $all = array();
                        foreach ($db as $value) {
                            $all[] = $value['id_product'];
                        }
                        foreach ($all as $id) {
                            if ($exportAction === 'lengow_add_to_export') {
                                LengowProduct::publish($id, 1, $idShop);
                            } else {
                                LengowProduct::publish($id, 0, $idShop);
                            }
                            foreach ($selection as $id => $v) {
                                $data['product_id'][] = $id;
                            }
                        }
                        $data = array_merge($data, $this->reloadTotal($idShop));
                    } elseif ($selection) {
                        foreach ($selection as $id => $v) {
                            // this line is useless, but Prestashop validator require it
                            $v = $v;
                            if ($exportAction === 'lengow_add_to_export') {
                                LengowProduct::publish($id, 1, $idShop);
                            } else {
                                LengowProduct::publish($id, 0, $idShop);
                            }
                            $data['product_id'][] = $id;
                        }
                        $data = array_merge($data, $this->reloadTotal($idShop));
                    } else {
                        $data['message'] = $this->locale->t('product.screen.no_product_selected');
                    }
                    echo Tools::jsonEncode($data);
                    break;
            }
            exit();
        }
    }

    /**
     * Display data page
     */
    public function display()
    {
        $shopCollection = array();
        if (_PS_VERSION_ < '1.5') {
            $results = array(array('id_shop' => 1));
        } else {
            if ($currentShop = Shop::getContextShopID()) {
                $results = array(array('id_shop' => $currentShop));
            } else {
                try {
                    $sql = 'SELECT id_shop FROM ' . _DB_PREFIX_ . 'shop WHERE active = 1';
                    $results = Db::getInstance()->ExecuteS($sql);
                } catch (PrestaShopDatabaseException $e) {
                    $results = array();
                }
            }
        }
        foreach ($results as $row) {
            $shop = new LengowShop($row['id_shop']);
            $lengowExport = new LengowExport(array('shop_id' => $shop->id));
            $shopCollection[] = array(
                'shop' => $shop,
                'link' => LengowMain::getExportUrl($shop->id),
                'total_product' => $lengowExport->getTotalProduct(),
                'total_export_product' => $lengowExport->getTotalExportProduct(),
                'last_export' => LengowConfiguration::get(
                    'LENGOW_LAST_EXPORT',
                    null,
                    null,
                    $shop->id
                ),
                'option_selected' => LengowConfiguration::get(
                    'LENGOW_EXPORT_SELECTION_ENABLED',
                    null,
                    null,
                    $shop->id
                ),
                'list' => $this->buildTable($shop->id),
            );
        }
        $this->context->smarty->assign('shopCollection', $shopCollection);
        parent::display();
    }

    /**
     * Reload Total product / Exported product
     *
     * @param integer $idShop Prestashop shop id
     *
     * @return array Number of product exported/total for this shop
     */
    public function reloadTotal($idShop)
    {
        $lengowExport = new LengowExport(array('shop_id' => $idShop));
        $result = array();
        $result['total_export_product'] = $lengowExport->getTotalExportProduct();
        $result['total_product'] = $lengowExport->getTotalProduct();

        return $result;
    }

    /**
     * Build product grid
     *
     * @param integer $idShop Prestashop shop id
     *
     * @return string
     */
    public function buildTable($idShop)
    {
        $fieldsList = array();

        $fieldsList['id_product'] = array(
            'title' => $this->locale->t('product.table.id_product'),
            'class' => 'center',
            'width' => '5%',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'p.id_product',
        );
        $fieldsList['image'] = array(
            'title' => $this->locale->t('product.table.image'),
            'class' => 'center',
            'image' => 'p',
            'width' => '5%',
        );
        $fieldsList['name'] = array(
            'title' => $this->locale->t('product.table.name'),
            'class' => 'feed_name',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'pl.name',
            'width' => '20%',
            'display_callback' => 'LengowFeedController::displayLink',
        );
        $fieldsList['reference'] = array(
            'title' => $this->locale->t('product.table.reference'),
            'class' => 'left',
            'width' => '14%',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'p.reference',
            'display_callback' => 'LengowFeedController::displayLink',
        );
        $fieldsList['category_name'] = array(
            'title' => $this->locale->t('product.table.category_name'),
            'width' => '14%',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'cl.name',
        );
        $fieldsList['price'] = array(
            'title' => $this->locale->t('product.table.price'),
            'width' => '9%',
            'filter_order' => true,
            'type' => 'price',
            'class' => 'left',
            'filter_key' => 'p.price',
        );
        $fieldsList['price_final'] = array(
            'title' => $this->locale->t('product.table.final_price'),
            'width' => '7%',
            'type' => 'price',
            'class' => 'left',
            'havingFilter' => true,
            'orderby' => false,
        );
        if (_PS_VERSION_ >= '1.5') {
            $quantityFilterKey = 'sav.quantity';
        } else {
            $quantityFilterKey = 'p.quantity';
        }
        $fieldsList['quantity'] = array(
            'title' => $this->locale->t('product.table.quantity'),
            'width' => '7%',
            'filter_order' => true,
            'class' => 'left',
            'filter_key' => $quantityFilterKey,
            'orderby' => true,
        );
        $fieldsList['id_lengow_product'] = array(
            'title' => $this->locale->t('product.table.lengow_status'),
            'width' => '10%',
            'class' => 'center no-link',
            'type' => 'switch_product',
            'filter_order' => true,
            'filter_key' => 'id_lengow_product',
        );

        $join = array();
        $where = array();

        $select = array(
            'p.id_product',
            'p.reference',
            'p.price',
            'pl.name',
            '0 as price_final',
            'IF(lp.id_product, 1, 0) as id_lengow_product',
            'cl.name as category_name',
            "'' as search",
        );
        $from = 'FROM ' . _DB_PREFIX_ . 'product p';

        $join[] = ' INNER JOIN ' . _DB_PREFIX_ . 'product_lang pl ON (pl.id_product = p.id_product
            AND pl.id_lang = ' . $this->context->language->id .
            (_PS_VERSION_ < '1.5' ? '' : ' AND pl.id_shop = ' . (int)$idShop) . ')';
        $join[] = ' LEFT JOIN ' . _DB_PREFIX_ . 'lengow_product lp ON (lp.id_product = p.id_product
            AND lp.id_shop = ' . (int)$idShop . ' ) ';
        if (_PS_VERSION_ >= '1.5') {
            $join[] = 'INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON (p.`id_product` = ps.`id_product`
                AND ps.id_shop = ' . (int)$idShop . ') ';
            $join[] = ' LEFT JOIN ' . _DB_PREFIX_ . 'stock_available sav ON (sav.id_product = p.id_product
                AND sav.id_product_attribute = 0 AND sav.id_shop = ' . (int)$idShop . ')';
        }
        if (_PS_VERSION_ >= '1.5') {
            if (Shop::isFeatureActive()) {
                $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                    ON (ps.`id_category_default` = cl.`id_category`
                    AND pl.`id_lang` = cl.`id_lang` AND cl.id_shop = ' . (int)$idShop . ')';
                $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'shop` shop ON (shop.id_shop = ' . (int)$idShop . ') ';
            } else {
                $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                    ON (p.`id_category_default` = cl.`id_category`
                    AND pl.`id_lang` = cl.`id_lang` AND cl.id_shop = 1)';
            }
            $select[] = ' sav.quantity ';
            if (!LengowConfiguration::get('LENGOW_EXPORT_INACTIVE', null, null, (int)$idShop)) {
                $where[] = ' ps.active = 1 ';
            }
        } else {
            $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (p.`id_category_default` = cl.`id_category`
                AND pl.`id_lang` = cl.`id_lang`)';
            $select[] = ' p.quantity ';
            if (!LengowConfiguration::get('LENGOW_EXPORT_INACTIVE')) {
                $where[] = ' p.active = 1 ';
            }
        }

        $currentPage = isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
        $orderValue = isset($_REQUEST['order_value']) ? $_REQUEST['order_value'] : '';
        $orderColumn = isset($_REQUEST['order_column']) ? $_REQUEST['order_column'] : '';
        $nbPerPage = isset($_REQUEST['nb_per_page']) ? $_REQUEST['nb_per_page'] : '';
        $this->list = new LengowList(
            array(
                'id' => 'shop_' . $idShop,
                'fields_list' => $fieldsList,
                'identifier' => 'id_product',
                'selection' => true,
                'controller' => 'AdminLengowFeed',
                'shop_id' => $idShop,
                'current_page' => $currentPage,
                'ajax' => true,
                'order_value' => $orderValue,
                'order_column' => $orderColumn,
                'nb_per_page' => $nbPerPage,
                'sql' => array(
                    'select' => $select,
                    'from' => $from,
                    'join' => $join,
                    'where' => $where,
                    'order' => 'p.id_product ASC',
                ),
            )
        );

        $collection = $this->list->executeQuery();

        $tempContext = new Context();
        $tempContext->shop = new Shop($idShop);
        $tempContext->employee = $this->context->employee;
        $tempContext->country = $this->context->country;

        // price calculation
        $nb = count($collection);
        if ($collection) {
            for ($i = 0; $i < $nb; $i++) {
                $productId = $collection[$i]['id_product'];
                if (_PS_VERSION_ < '1.5') {
                    $collection[$i]['price_final'] = Product::getPriceStatic(
                        $productId,
                        true,
                        null,
                        2,
                        null,
                        false,
                        true,
                        1,
                        true
                    );
                } else {
                    $nothing = '';
                    $collection[$i]['price_final'] = Product::getPriceStatic(
                        $productId,
                        true,
                        null,
                        2,
                        null,
                        false,
                        true,
                        1,
                        true,
                        null,
                        null,
                        null,
                        $nothing,
                        true,
                        true,
                        $tempContext
                    );
                }
                $collection[$i]['image'] = '';
                if (_PS_VERSION_ < '1.5') {
                    $coverImage = Product::getCover($productId);
                    if ($coverImage) {
                        $idImage = $coverImage['id_image'];
                        $imageProduct = new Image($idImage);
                        $collection[$i]['image'] = cacheImage(
                            _PS_IMG_DIR_ . 'p/' . $imageProduct->getExistingImgPath() . '.jpg',
                            'product_mini_' . (int)($productId) . '.jpg',
                            45,
                            'jpg'
                        );
                    }
                } else {
                    $coverImage = Product::getCover($collection[$i]['id_product'], $tempContext);
                    if ($coverImage) {
                        $idImage = $coverImage['id_image'];
                        $pathToImage = _PS_IMG_DIR_ . 'p/' . Image::getImgFolderStatic($idImage)
                            . (int)$idImage . '.jpg';
                        $collection[$i]['image'] = ImageManager::thumbnail(
                            $pathToImage,
                            'product_mini_' . $collection[$i]['id_product'] . '_' . $idShop . '.jpg',
                            45,
                            'jpg'
                        );
                    }
                }
            }
        }
        $this->list->updateCollection($collection);
        $paginationBlock = $this->list->renderPagination(array('nav_class' => 'lgw-pagination'));
        $lengowLink = new LengowLink();
        $html = '<div class="lengow_table_top">';
        $html .= '<div class="lengow_toolbar">';
        if (!$this->toolbox) {
            $messageRemoveConfirmation = $this->locale->t(
                'product.screen.remove_confirmation',
                array('nb' => $this->list->getTotal())
            );
            $html .= '<a href="#" data-id_shop="' . $idShop . '" style="display:none;"
                data-href="' . $lengowLink->getAbsoluteAdminLink('AdminLengowFeed', true) . '"
                data-message="' . $messageRemoveConfirmation . '"
                data-action="lengow_export_action"
                data-export-action="lengow_remove_from_export"
                class="lgw-btn lgw-btn-red lengow_remove_from_export">
                <i class="fa fa-minus"></i> ' . $this->locale->t('product.screen.remove_from_export') . '</a>';
            $messageAddConfirmation = $this->locale->t(
                'product.screen.add_confirmation',
                array('nb' => $this->list->getTotal())
            );
            $html .= '<a href="#" data-id_shop="' . $idShop . '" style="display:none;"
                data-href="' . $lengowLink->getAbsoluteAdminLink('AdminLengowFeed', true) . '"
                data-message="' . $messageAddConfirmation . '"
                data-action="lengow_export_action"
                data-export-action="lengow_add_to_export"
                class="lgw-btn lengow_add_to_export">
                <i class="fa fa-plus"></i> ' . $this->locale->t('product.screen.add_from_export') . '</a>';
            $html .= '<div class="lengow_select_all_shop lgw-container" style="display:none;">';
            $html .= '<input type="checkbox" id="select_all_shop_' . $idShop . '"/>&nbsp;&nbsp;';
            $html .= '<span>' . $this->locale->t(
                'product.screen.select_all_products',
                array('nb' => $this->list->getTotal())
            );
            $html .= '</span>';
            $html .= '</div>';
        }
        $html .= '</div>';
        $html .= $paginationBlock;
        $html .= '<div class="clearfix"></div>';
        $html .= '</div>';
        $html .= $this->list->display();
        $html .= '<div class="lengow_table_bottom">';
        $html .= $paginationBlock;
        $html .= '<div class="clearfix"></div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Get product link
     *
     * @param string $key row key
     * @param string $value row value
     * @param array $item item values
     *
     * @throws Exception
     *
     * @return string
     */
    public static function displayLink($key, $value, $item)
    {
        // this line is useless, but Prestashop validator require it
        $key = $key;
        $toolbox = Context::getContext()->smarty->getVariable('toolbox')->value;
        $link = new LengowLink();
        if ($item['id_product']) {
            if (!$toolbox) {
                if (_PS_VERSION_ < '1.7') {
                    return '<a href="' . $link->getAbsoluteAdminLink(
                        (_PS_VERSION_ < '1.5' ? 'AdminCatalog' : 'AdminProducts'),
                        false,
                        true
                    ) . '&updateproduct&id_product=' . $item['id_product'] . '" target="_blank">' . $value . '</a>';
                } else {
                    return '<a href="' .
                    $link->getAdminLink('AdminProducts', true, array('id_product' => $item['id_product'])) .
                    '" target="_blank">' . $value . '</a>';
                }
            } else {
                return $value;
            }
        } else {
            return $value;
        }
    }
}
