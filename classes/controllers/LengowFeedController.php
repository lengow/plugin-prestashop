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
/*
 * Lengow Feed Controller Class
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
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
                case 'change_option_selected__selection':
                    $state = Tools::getValue('state_selection');
                    $idShop = (int) Tools::getValue('id_shop');
                    if ($state !== null) {
                        LengowConfiguration::updatevalue(
                            LengowConfiguration::SELECTION_ENABLED,
                            $state,
                            null,
                            null,
                            $idShop
                        );
                        $state = LengowConfiguration::get(LengowConfiguration::SELECTION_ENABLED, null, null, $idShop);
                        $data = [];
                        $data['shop_id'] = $idShop;
                        $data['option'] = 'selection';
                        if ($state) {
                            $data['state'] = true;
                            LengowConfiguration::updatevalue(
                                LengowConfiguration::OUT_OF_STOCK_ENABLED,
                                1,
                                null,
                                null,
                                $idShop
                            );
                            LengowConfiguration::updatevalue(
                                LengowConfiguration::VARIATION_ENABLED,
                                1,
                                null,
                                null,
                                $idShop
                            );
                            LengowConfiguration::updatevalue(
                                LengowConfiguration::INACTIVE_ENABLED,
                                0,
                                null,
                                null,
                                $idShop
                            );
                        } else {
                            $data['state'] = false;
                        }

                        $result = array_merge($data, $this->reloadTotal($idShop));
                        echo json_encode($result);
                    }
                    break;
                case 'change_option_selected__out_of_stock':
                    $state = Tools::getValue('state_out_of_stock');
                    $idShop = (int) Tools::getValue('id_shop');
                    if ($state !== null) {
                        LengowConfiguration::updatevalue(
                            LengowConfiguration::OUT_OF_STOCK_ENABLED,
                            $state,
                            null,
                            null,
                            $idShop
                        );
                        $state = LengowConfiguration::get(LengowConfiguration::OUT_OF_STOCK_ENABLED, null, null, $idShop);
                        $data = [];
                        $data['shop_id'] = $idShop;
                        $data['state'] = null;
                        $data['option'] = 'out_of_stock';
                        if ($state) {
                            LengowConfiguration::updatevalue(
                                LengowConfiguration::SELECTION_ENABLED,
                                0,
                                null,
                                null,
                                $idShop
                            );
                        }

                        $result = array_merge($data, $this->reloadTotal($idShop));
                        echo json_encode($result);
                    }
                    break;
                case 'change_option_selected__variation':
                    $state = Tools::getValue('state_variation');
                    $idShop = (int) Tools::getValue('id_shop');
                    if ($state !== null) {
                        LengowConfiguration::updatevalue(
                            LengowConfiguration::VARIATION_ENABLED,
                            $state,
                            null,
                            null,
                            $idShop
                        );
                        $state = LengowConfiguration::get(LengowConfiguration::VARIATION_ENABLED, null, null, $idShop);
                        $data = [];
                        $data['shop_id'] = $idShop;
                        $data['state'] = null;
                        $data['option'] = 'variation';
                        if ($state) {
                            LengowConfiguration::updatevalue(
                                LengowConfiguration::SELECTION_ENABLED,
                                0,
                                null,
                                null,
                                $idShop
                            );
                        }

                        $result = array_merge($data, $this->reloadTotal($idShop));
                        echo json_encode($result);
                    }
                    break;
                case 'change_option_selected__inactive':
                    $state = Tools::getValue('state_inactive');
                    $idShop = (int) Tools::getValue('id_shop');
                    if ($state !== null) {
                        LengowConfiguration::updatevalue(
                            LengowConfiguration::INACTIVE_ENABLED,
                            $state,
                            null,
                            null,
                            $idShop
                        );
                        $state = LengowConfiguration::get(LengowConfiguration::INACTIVE_ENABLED, null, null, $idShop);
                        $data = [];
                        $data['shop_id'] = $idShop;
                        $data['state'] = null;
                        $data['option'] = 'inactive';
                        if ($state) {
                            LengowConfiguration::updatevalue(
                                LengowConfiguration::SELECTION_ENABLED,
                                0,
                                null,
                                null,
                                $idShop
                            );
                        }

                        $result = array_merge($data, $this->reloadTotal($idShop));
                        echo json_encode($result);
                    }
                    break;
                case 'select_product':
                    $state = isset($_REQUEST['state']) ? $_REQUEST['state'] : null;
                    $idShop = isset($_REQUEST['id_shop']) ? (int) $_REQUEST['id_shop'] : null;
                    $productId = isset($_REQUEST['id_product']) ? $_REQUEST['id_product'] : null;
                    if ($state !== null) {
                        LengowProduct::publish($productId, $state, $idShop);
                        echo json_encode($this->reloadTotal($idShop));
                    }
                    break;
                case 'load_table':
                    $idShop = isset($_REQUEST['id_shop']) ? (int) $_REQUEST['id_shop'] : null;
                    $data = [];
                    $data['shop_id'] = $idShop;
                    $data['footer_content'] = preg_replace('/\r|\n/', '', $this->buildTable($idShop));
                    echo json_encode($data);
                    break;
                case 'lengow_export_action':
                    $idShop = isset($_REQUEST['id_shop']) ? (int) $_REQUEST['id_shop'] : null;
                    $selection = isset($_REQUEST['selection']) ? $_REQUEST['selection'] : null;
                    $selectAll = isset($_REQUEST['select_all']) ? $_REQUEST['select_all'] : null;
                    $exportAction = isset($_REQUEST['export_action']) ? $_REQUEST['export_action'] : null;
                    $data = [];
                    if ($selectAll === 'true') {
                        $this->buildTable($idShop);
                        $sql = $this->list->buildQuery(false, true);
                        try {
                            $db = Db::getInstance()->executeS($sql);
                        } catch (PrestaShopDatabaseException $e) {
                            $db = [];
                        }
                        $all = [];
                        foreach ($db as $value) {
                            $all[] = $value['id_product'];
                        }
                        foreach ($all as $id) {
                            if ($exportAction === 'lengow_add_to_export') {
                                LengowProduct::publish($id, 1, $idShop);
                            } else {
                                LengowProduct::publish($id, 0, $idShop);
                            }
                            foreach (array_keys($selection) as $keyId) {
                                $data['product_id'][] = $keyId;
                            }
                        }
                        $data = array_merge($data, $this->reloadTotal($idShop));
                    } elseif ($selection) {
                        foreach (array_keys($selection) as $id) {
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
                    echo json_encode($data);
                    break;
                case 'update_fields':
                    $fields = Tools::getValue('fields');
                    if (is_array($fields)) {
                        foreach ($fields as $key => $field) {
                            if (in_array($field['prestashop_value'], ['id', 'category', 'name', 'price_incl_tax', 'language'])) {
                                $field['exported'] = '1';
                            }
                            $defaultKey = $key;
                            $prestashopValue = isset($field['prestashop_value']) ? pSQL($field['prestashop_value']) : '';
                            $lengowField = isset($field['lengow_field']) ? pSQL($field['lengow_field']) : '';
                            $exported = isset($field['exported']) ? (int) $field['exported'] : 0;

                            $sql = 'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'lengow_exported_fields WHERE default_key = "' . $defaultKey . '"';
                            $exists = Db::getInstance()->getValue($sql);

                            if ($exists) {
                                $sql = 'UPDATE ' . _DB_PREFIX_ . 'lengow_exported_fields
                                SET prestashop_value = "' . $prestashopValue . '",
                                    lengow_field = "' . $lengowField . '",
                                    exported = "' . $exported . '"
                                WHERE default_key = "' . $defaultKey . '"';
                            } else {
                                $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'lengow_exported_fields (default_key, prestashop_value, lengow_field, exported)
                                VALUES ("' . $defaultKey . '", "' . $prestashopValue . '", "' . $lengowField . '", "' . $exported . '")';
                            }

                            Db::getInstance()->execute($sql);
                        }
                        return Tools::redirectAdmin($this->lengowLink->getAbsoluteAdminLink('AdminLengowFeed'));
                    }
                    break;
            }
            exit;
        }
    }

    /**
     * Display data page
     */
    public function display()
    {
        $lengowExport = new LengowExport();
        $lengowProduct = new LengowProduct();
        $fields = $lengowExport->getConfigFields();
        $shopCollection = [];
        if ($currentShop = Shop::getContextShopID()) {
            $results = [['id_shop' => $currentShop]];
        } else {
            try {
                $sql = 'SELECT id_shop FROM ' . _DB_PREFIX_ . 'shop WHERE active = 1';
                $results = Db::getInstance()->ExecuteS($sql);
            } catch (PrestaShopDatabaseException $e) {
                $results = [];
            }
        }
        foreach ($results as $row) {
            $shop = new LengowShop($row['id_shop']);
            $lengowExport = new LengowExport([LengowExport::PARAM_SHOP_ID => $shop->id]);
            $shopCollection[] = [
                'shop' => $shop,
                'link' => LengowMain::getExportUrl($shop->id),
                'total_product' => $lengowExport->getTotalProduct(),
                'total_export_product' => $lengowExport->getTotalExportProduct(),
                'option_selected__selection' => LengowConfiguration::get(
                    LengowConfiguration::SELECTION_ENABLED,
                    null,
                    null,
                    $shop->id
                ),
                'option_selected__out_of_stock' => LengowConfiguration::get(
                    LengowConfiguration::OUT_OF_STOCK_ENABLED,
                    null,
                    null,
                    $shop->id
                ),
                'option_selected__variation' => LengowConfiguration::get(
                    LengowConfiguration::VARIATION_ENABLED,
                    null,
                    null,
                    $shop->id
                ),
                'option_selected__inactive' => LengowConfiguration::get(
                    LengowConfiguration::INACTIVE_ENABLED,
                    null,
                    null,
                    $shop->id
                ),
                'list' => $this->buildTable($shop->id),
            ];
        }

        $productsData = $lengowExport->getProductsListData();

        $this->context->smarty->assign('shopCollection', $shopCollection);
        $this->context->smarty->assign('fields', $fields);
        $this->context->smarty->assign('json_products', $productsData);
        parent::display();
    }

    /**
     * Reload Total product / Exported product
     *
     * @param int $idShop PrestaShop shop id
     *
     * @return array Number of product exported/total for this shop
     */
    public function reloadTotal($idShop)
    {
        $lengowExport = new LengowExport([LengowExport::PARAM_SHOP_ID => $idShop]);
        $result = [];
        $result['total_export_product'] = $lengowExport->getTotalExportProduct();
        $result['total_product'] = $lengowExport->getTotalProduct();

        return $result;
    }

    /**
     * Build product grid
     *
     * @param int $idShop PrestaShop shop id
     *
     * @return string
     */
    public function buildTable($idShop)
    {
        $fieldsList = [];

        $fieldsList['id_product'] = [
            'title' => $this->locale->t('product.table.id_product'),
            'class' => 'center',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'p.id_product',
        ];
        $fieldsList['image'] = [
            'title' => $this->locale->t('product.table.image'),
            'class' => 'center',
            'image' => 'p',
        ];
        $fieldsList['name'] = [
            'title' => $this->locale->t('product.table.name'),
            'class' => 'feed_name',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'pl.name',
            'display_callback' => 'LengowFeedController::displayLink',
        ];
        $fieldsList['reference'] = [
            'title' => $this->locale->t('product.table.reference'),
            'class' => 'left',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'p.reference',
            'display_callback' => 'LengowFeedController::displayLink',
        ];
        $fieldsList['category_name'] = [
            'title' => $this->locale->t('product.table.category_name'),
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'cl.name',
        ];
        $fieldsList['price'] = [
            'title' => $this->locale->t('product.table.price'),
            'filter_order' => true,
            'type' => 'price',
            'class' => 'left',
            'filter_key' => 'p.price',
        ];
        $fieldsList['price_final'] = [
            'title' => $this->locale->t('product.table.final_price'),
            'type' => 'price',
            'class' => 'left',
            'havingFilter' => true,
            'orderby' => false,
        ];
        $quantityFilterKey = 'sav.quantity';
        $fieldsList['quantity'] = [
            'title' => $this->locale->t('product.table.quantity'),
            'filter_order' => true,
            'class' => 'left',
            'filter_key' => $quantityFilterKey,
            'orderby' => true,
        ];
        $fieldsList['id_lengow_product'] = [
            'title' => $this->locale->t('product.table.lengow_status'),
            'class' => 'center no-link',
            'type' => 'switch_product',
            'filter_order' => true,
            'filter_key' => 'id_lengow_product',
        ];

        $join = [];
        $where = [];

        $select = [
            'p.id_product',
            'p.reference',
            'p.price',
            'pl.name',
            '0 as price_final',
            'IF(lp.id_product, 1, 0) as id_lengow_product',
            'cl.name as category_name',
            "'' as search",
        ];
        $from = 'FROM ' . _DB_PREFIX_ . 'product p';

        $join[] = ' INNER JOIN ' . _DB_PREFIX_ .
            'product_lang pl ON (
            pl.id_product = p.id_product
            AND pl.id_lang = ' . $this->context->language->id .
            ' AND pl.id_shop = ' . (int) $idShop . ')';
        $join[] = ' LEFT JOIN ' . _DB_PREFIX_ . 'lengow_product lp ON (lp.id_product = p.id_product
            AND lp.id_shop = ' . (int) $idShop . ' ) ';
        $join[] = 'INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON (p.`id_product` = ps.`id_product`
            AND ps.id_shop = ' . (int) $idShop . ') ';
        $join[] = ' LEFT JOIN ' . _DB_PREFIX_ . 'stock_available sav ON (sav.id_product = p.id_product
            AND sav.id_product_attribute = 0 AND sav.id_shop = ' . (int) $idShop . ')';

        if (Shop::isFeatureActive()) {
            $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                ON (ps.`id_category_default` = cl.`id_category`
                AND pl.`id_lang` = cl.`id_lang` AND cl.id_shop = ' . (int) $idShop . ')';
            $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'shop` shop ON (shop.id_shop = ' . (int) $idShop . ') ';
        } else {
            $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                ON (p.`id_category_default` = cl.`id_category`
                AND pl.`id_lang` = cl.`id_lang` AND cl.id_shop = 1)';
        }
        $select[] = ' sav.quantity ';
        if (!LengowConfiguration::get(LengowConfiguration::INACTIVE_ENABLED, null, null, (int) $idShop)) {
            $where[] = ' ps.active = 1 ';
        }

        $currentPage = isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
        $orderValue = isset($_REQUEST['order_value']) ? $_REQUEST['order_value'] : '';
        $orderColumn = isset($_REQUEST['order_column']) ? $_REQUEST['order_column'] : '';
        $nbPerPage = isset($_REQUEST['nb_per_page']) ? $_REQUEST['nb_per_page'] : '';
        $this->list = new LengowList(
            [
                'id' => 'shop_' . $idShop,
                'fields_list' => $fieldsList,
                'identifier' => 'id_product',
                'selection' => true,
                'controller' => 'AdminLengowFeed',
                'shop_id' => $idShop,
                'current_page' => $currentPage,
                'order_value' => $orderValue,
                'order_column' => $orderColumn,
                'nb_per_page' => $nbPerPage,
                'sql' => [
                    'select' => $select,
                    'from' => $from,
                    'join' => $join,
                    'where' => $where,
                    'order' => 'p.id_product ASC',
                ],
            ]
        );

        $collection = $this->list->executeQuery();

        $tempContext = new Context();
        $tempContext->shop = new Shop($idShop);
        $tempContext->employee = $this->context->employee;
        $tempContext->country = $this->context->country;

        // price calculation
        $nb = count($collection);
        if ($collection) {
            for ($i = 0; $i < $nb; ++$i) {
                $productId = $collection[$i]['id_product'];
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
                $collection[$i]['image'] = '';
                $coverImage = Product::getCover($collection[$i]['id_product'], $tempContext);
                if ($coverImage) {
                    $idImage = $coverImage['id_image'];
                    $pathToImage = _PS_IMG_DIR_ . 'p/' . Image::getImgFolderStatic($idImage)
                        . (int) $idImage . '.jpg';
                    $collection[$i]['image'] = ImageManager::thumbnail(
                        $pathToImage,
                        'product_mini_' . $collection[$i]['id_product'] . '_' . $idShop . '.jpg',
                        45,
                        'jpg'
                    );
                }
            }
        }
        $this->list->updateCollection($collection);
        $paginationBlock = $this->list->renderPagination(['nav_class' => 'lgw-pagination']);
        $html = '<div class="lengow_table_top">';
        $html .= '<div class="lengow_toolbar">';
        $messageRemoveConfirmation = $this->locale->t(
            'product.screen.remove_confirmation',
            ['nb' => $this->list->getTotal()]
        );
        $html .= '<a href="#" data-id_shop="' . $idShop . '" style="display:none;"
            data-href="' . $this->lengowLink->getAbsoluteAdminLink('AdminLengowFeed') . '"
            data-message="' . $messageRemoveConfirmation . '"
            data-action="lengow_export_action"
            data-export-action="lengow_remove_from_export"
            class="lgw-btn lgw-btn-red lengow_remove_from_export">
            <i class="fa fa-minus"></i> ' . $this->locale->t('product.screen.remove_from_export') . '</a>';
        $messageAddConfirmation = $this->locale->t(
            'product.screen.add_confirmation',
            ['nb' => $this->list->getTotal()]
        );
        $html .= '<a href="#" data-id_shop="' . $idShop . '" style="display:none;"
            data-href="' . $this->lengowLink->getAbsoluteAdminLink('AdminLengowFeed') . '"
            data-message="' . $messageAddConfirmation . '"
            data-action="lengow_export_action"
            data-export-action="lengow_add_to_export"
            class="lgw-btn lengow_add_to_export">
            <i class="fa fa-plus"></i> ' . $this->locale->t('product.screen.add_from_export') . '</a>';
        $html .= '<div class="lengow_select_all_shop lgw-container" style="display:none;">';
        $html .= '<input type="checkbox" id="select_all_shop_' . $idShop . '"/>&nbsp;&nbsp;';
        $html .= '<span>' . $this->locale->t(
                'product.screen.select_all_products',
                ['nb' => $this->list->getTotal()]
            );
        $html .= '</span>';
        $html .= '</div>';
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
     * @return string
     *
     * @throws Exception
     */
    public static function displayLink($key, $value, $item)
    {
        // this line is useless, but PrestaShop validator require it
        $key = $key;
        $link = new LengowLink();
        if ($item['id_product']) {
            $controller = 'AdminProducts';
            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                $href = $link->getAbsoluteAdminLink($controller) . '&updateproduct&id_product=' . $item['id_product'];
            } else {
                $href = $link->getAdminLink(
                    $controller,
                    true,
                    [
                        'updateproduct' => 1,
                        'id_product' => $item['id_product'],
                    ]
                );
            }

            return '<a href="' . $href . '" target="_blank">' . $value . '</a>';
        }

        return $value;
    }
}
