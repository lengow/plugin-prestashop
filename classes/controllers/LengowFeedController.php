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

class LengowFeedController extends LengowController
{
    /**
     * Update data
     */
    public function postProcess()
    {
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
        if ($action) {
            switch ($action) {
                case 'change_option_product_variation':
                    $state = isset($_REQUEST['state']) ? $_REQUEST['state'] : null;
                    $shopId = isset($_REQUEST['id_shop']) ? (int)$_REQUEST['id_shop'] : null;
                    if ($state !== null) {
                        Configuration::updatevalue('LENGOW_EXPORT_VARIATION_ENABLED', $state, null, null, $shopId);
                        $this->reloadTotal($shopId);
                    }
                    break;
                case 'change_option_selected':
                    $state = isset($_REQUEST['state']) ? $_REQUEST['state'] : null;
                    $shopId = isset($_REQUEST['id_shop']) ? (int)$_REQUEST['id_shop'] : null;
                    if ($state !== null) {
                        Configuration::updatevalue('LENGOW_EXPORT_SELECTION_ENABLED', $state, null, null, $shopId);
                        $this->reloadTotal($shopId);
                        $state = Configuration::get('LENGOW_EXPORT_SELECTION_ENABLED', null, null, $shopId);
                        if ($state) {
                            echo "lengow_jquery('#block_".$shopId." .lengow_feed_block_footer_content').show();";
                        } else {
                            echo "lengow_jquery('#block_".$shopId." .lengow_feed_block_footer_content').hide();";
                        }
                    }
                    break;
                case 'change_option_product_out_of_stock':
                    $state = isset($_REQUEST['state']) ? $_REQUEST['state'] : null;
                    $shopId = isset($_REQUEST['id_shop']) ? (int)$_REQUEST['id_shop'] : null;
                    if ($state !== null) {
                        Configuration::updatevalue('LENGOW_EXPORT_OUT_STOCK', $state, null, null, $shopId);
                        $this->reloadTotal($shopId);
                    }
                    break;
                case 'select_product':
                    $state = isset($_REQUEST['state']) ? $_REQUEST['state'] : null;
                    $shopId = isset($_REQUEST['id_shop']) ? (int)$_REQUEST['id_shop'] : null;
                    $productId = isset($_REQUEST['id_product']) ? $_REQUEST['id_product'] : null;
                    if ($state !== null) {
                        LengowProduct::publish($productId, $state, $shopId);
                        $this->reloadTotal($shopId);
                    }
                    break;
                case 'load_table':
                    $shopId = isset($_REQUEST['id_shop']) ? (int)$_REQUEST['id_shop'] : null;
                    echo 'lengow_jquery("#block_'.$shopId.' .lengow_feed_block_footer_content").html("'.
                        preg_replace('/\r|\n/', '', addslashes($this->buildTable($shopId))).'");';
                    break;
                case 'add_to_export':
                    $shopId = isset($_REQUEST['id_shop']) ? (int)$_REQUEST['id_shop'] : null;
                    $selection = isset($_REQUEST['selection']) ? $_REQUEST['selection'] : null;
                    if ($selection) {
                        foreach ($selection as $id => $v) {
                            LengowProduct::publish($id, 1, $shopId);
                            echo 'lengow_jquery("#block_'.$shopId.' .lengow_product_selection_'.$id.'")';
                            echo '.bootstrapSwitch("state",true, true);';
                        }
                        $this->reloadTotal($shopId);
                    } else {
                        echo 'alert("Please select a product");';
                    }
                    break;
                case 'remove_from_export':
                    $shopId = isset($_REQUEST['id_shop']) ? (int)$_REQUEST['id_shop'] : null;
                    $selection = isset($_REQUEST['selection']) ? $_REQUEST['selection'] : null;
                    if ($selection) {
                        foreach ($selection as $id => $v) {
                            LengowProduct::publish($id, 0, $shopId);
                            echo 'lengow_jquery("#block_'.$shopId.' .lengow_product_selection_'.$id.'")';
                            echo '.bootstrapSwitch("state",false, true);';
                        }
                        $this->reloadTotal($shopId);
                    } else {
                        echo 'alert("Please select a product");';
                    }
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
                $sql = 'SELECT id_shop FROM '._DB_PREFIX_.'shop WHERE active = 1';
                $results = Db::getInstance()->ExecuteS($sql);
            }
        }
        foreach ($results as $row) {
            $shop = new LengowShop($row['id_shop']);
            $lengowExport = new LengowExport(array(
                "shop_id" => $shop->id
            ));
            $shopCollection[]= array(
                'shop' => $shop,
                'link' => LengowMain::getExportUrl($shop->id),
                'total_product' => $lengowExport->getTotalProduct(),
                'total_export_product' => $lengowExport->getTotalExportProduct(),
                'last_export' => Configuration::get('LENGOW_LAST_EXPORT', null, null, $shop->id),
                'option_selected' => Configuration::get('LENGOW_EXPORT_SELECTION_ENABLED', null, null, $shop->id),
                'option_variation' => Configuration::get('LENGOW_EXPORT_VARIATION_ENABLED', null, null, $shop->id),
                'option_product_out_of_stock' => Configuration::get('LENGOW_EXPORT_OUT_STOCK', null, null, $shop->id),
                'list' => $this->buildTable($shop->id)
            );
        }
        $this->context->smarty->assign('shopCollection', $shopCollection);
        parent::display();
    }

    /**
     * Reload Total product / Exported product
     * @param $shopId
     */
    public function reloadTotal($shopId)
    {
        $lengowExport = new LengowExport(array(
            "shop_id" => $shopId
        ));
        echo 'lengow_jquery("#block_'.$shopId.' .lengow_exported").html("'.$lengowExport->getTotalExportProduct().'");';
        echo 'lengow_jquery("#block_'.$shopId.' .lengow_total").html("'.$lengowExport->getTotalProduct().'");';
    }

    /**
     * Reload Total product / Exported product
     * @param $shopId
     * @return string
     */
    public function buildTable($shopId)
    {
        $fields_list = array();

        $fields_list['id_product'] = array(
            'title' => $this->module->l('ID'),
            'class' => 'center',
            'width' => 70,
            'filter' => true,
            'filter_key' => 'p.id_product',
        );
        $fields_list['image'] = array(
            'title' => $this->module->l('Image'),
            'align' => 'center',
            'image' => 'p',
            'width' => 70,
        );
        $fields_list['name'] = array(
            'title' => $this->module->l('Name'),
            'filter' => true,
            'filter_key' => 'pl.name',
        );
        $fields_list['reference'] = array(
            'title' => $this->module->l('Reference'),
            'align' => 'left',
            'width' => 80,
            'filter' => true,
            'filter_key' => 'p.reference',
        );
//
//        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
//            $this->fields_list['shopname'] = array(
//                'title' => $this->module->l('Default shop:'),
//                'width' => 230,
//                'filter_key' => 'shop!name',
//            );
//        } else {
        $fields_list['category_name'] = array(
            'title' => $this->module->l('Category'),
            'width' => 'auto',
            'filter' => true,
            'filter_key' => 'cl.name',
        );
//        }
        $fields_list['price'] = array(
            'title' => $this->module->l('Original price'),
            'width' => 90,
            'type' => 'price',
            'class' => 'right',
            'filter_key' => 'a!price'
        );
        $fields_list['price_final'] = array(
            'title' => $this->module->l('Final price'),
            'width' => 90,
            'type' => 'price',
            'class' => 'right',
            'havingFilter' => true,
            'orderby' => false
        );
        $fields_list['quantity'] = array(
            'title' => $this->module->l('Quantity'),
            'width' => 90,
            'class' => 'right',
            'filter_key' => 'sav!quantity',
            'orderby' => true,
            'hint' => $this->module->l('This is the quantity available in the current shop/group.'),
        );
        $fields_list['id_lengow_product'] = array(
            'title' => $this->module->l('Lengow status'),
            'width' => 'auto',
            'class' => 'center',
            'type' => 'switch_product',
            'button_search' => true
        );

//        if ((int)$this->id_current_category) {
//            $this->fields_list['position'] = array(
//                'title' => $this->module->l('Position'),
//                'width' => 70,
//                'filter_key' => 'cp!position',
//                'align' => 'center',
//                'position' => 'position'
//            );
//        }


        $select = array(
            "p.id_product",
            "p.reference",
            "p.price",
            "pl.name",
            "0 as price_final",
            "IF(lp.id_product, 1, 0) as id_lengow_product",
            "cl.name as category_name",
        );
        $from = 'FROM '._DB_PREFIX_.'product p';

        $join[] = ' INNER JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = p.id_product
        AND pl.id_lang = 1 '.(_PS_VERSION_ < '1.5' ? '': ' AND pl.id_shop = '.(int)$shopId).')';
        $join[] = ' LEFT JOIN '._DB_PREFIX_.'lengow_product lp ON (lp.id_product = p.id_product
        AND lp.id_shop = '.(int)$shopId.' ) ';
        if (_PS_VERSION_ >= '1.5') {
            $join[] = 'INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON (p.`id_product` = ps.`id_product`
            AND ps.id_shop = ' . (int)$shopId . ') ';
            $join[] = ' LEFT JOIN '._DB_PREFIX_.'stock_available sav ON (sav.id_product = p.id_product
            AND sav.id_product_attribute = 0 AND sav.id_shop = ' . (int)$shopId . ')';
        }
        if (_PS_VERSION_ >= '1.5') {
            if (Shop::isFeatureActive()) {
                $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                ON (ps.`id_category_default` = cl.`id_category`
                AND pl.`id_lang` = cl.`id_lang` AND cl.id_shop = ' . (int)$shopId . ')';
                $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'shop` shop ON (shop.id_shop = ' . (int)$shopId . ') ';
            } else {
                $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                ON (p.`id_category_default` = cl.`id_category`
                AND pl.`id_lang` = cl.`id_lang` AND cl.id_shop = 1)';
            }
            $select[] = ' sav.quantity ';
            $where[] = ' ps.active = 1 ';
        } else {
            $join[] = 'LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (p.`id_category_default` = cl.`id_category`
            AND pl.`id_lang` = cl.`id_lang`)';
            $select[] = ' p.quantity ';
            $where[] = ' p.active = 1 ';
        }

        $currentPage = isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;

        $list = new LengowList(array(
            "id" => 'shop_'.$shopId,
            "fields_list" => $fields_list,
            "identifier" => 'id_product',
            "selection" => true,
            "controller" => 'AdminLengowFeed',
            "shop_id" => $shopId,
            "current_page" => $currentPage,
            "ajax" => true,
            "sql" => array(
                "select" => $select,
                "from" => $from,
                "join" => $join,
                "where" => $where,
            )
        ));

        $collection = $list->executeQuery();

        $tempContext = new Context();
        $tempContext->shop = new Shop($shopId);
        $tempContext->employee = $this->context->employee;
        $tempContext->country = $this->context->country;

        //calcul price
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
                        $id_image = $coverImage['id_image'];
                        $imageProduct = new Image($id_image);
                        $collection[$i]['image'] = cacheImage(
                            _PS_IMG_DIR_.'p/'.$imageProduct->getExistingImgPath().'.jpg',
                            'product_mini_'.(int)($productId).'.jpg',
                            45,
                            'jpg'
                        );
                    }
                } else {
                    $coverImage = Product::getCover($collection[$i]['id_product'], $tempContext);
                    if ($coverImage) {
                        $id_image = $coverImage['id_image'];
                        $path_to_image = _PS_IMG_DIR_.'p/'.Image::getImgFolderStatic($id_image).(int)$id_image.'.jpg';
                        $collection[$i]['image'] = ImageManager::thumbnail(
                            $path_to_image,
                            'product_mini_' . $collection[$i]['id_product'] . '_' . $shopId . '.jpg',
                            45,
                            'jpg'
                        );
                    }
                }
            }
        }
        $list->updateCollection($collection);
        $paginationBlock = $list->renderPagination(array(
            'nav_class' => 'lengow_feed_pagination'
        ));

        $lengow_link = new LengowLink();

        $html='<div class="lengow_table_top">';
        $html.='<div class="lengow_toolbar">';
        $html.='<input type="checkbox" id="select_shop_'.$shopId.'" class="lengow_select_all"/>';
        $html.='<a href="#" data-id_shop="'.$shopId.'" style="display:none;"
                data-href="'.$lengow_link->getAbsoluteAdminLink('AdminLengowFeed', true).'"
                class="lengow_btn lengow_link_tooltip lengow_remove_from_export" title="Remove from export">
                <i class="fa fa-minus"></i></a>';
        $html.='<a href="#" data-id_shop="'.$shopId.'" style="display:none;"
                        data-href="'.$lengow_link->getAbsoluteAdminLink('AdminLengowFeed', true).'"
                class="lengow_btn lengow_link_tooltip lengow_add_to_export" title="Add from export">
                <i class="fa fa-plus"></i></a>';
        $html.='</div>';
        $html.= $paginationBlock;
        $html.='<div class="lengow_clear"></div>';
        $html.='</div>';
        $html.= $list->display();
        $html.='<div class="lengow_table_bottom">';
        $html.= $paginationBlock;
        $html.='<div class="lengow_clear"></div>';
        $html.='</div>';

        return $html;
    }
}
