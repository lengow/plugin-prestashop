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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_.'lengow/lengow.php';

/**
 * The Lengow's Configuration Admin Controller.
 *
 */
class AdminLengowFeedController extends ModuleAdminController
{

    /**
     * Construct the admin selection of products
     */
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->context->smarty->assign('lengow_link', new LengowLink());
        $this->lang = true;
        $this->explicitSelect = true;
        $this->lite_display = true;
        $this->meta_title = 'Configuration';
        $this->list_no_link = true;
        if (_PS_VERSION_ >= '1.6') {
            $this->bootstrap = true;
        }
        $this->template = 'layout.tpl';
        $this->display = 'view';

        parent::__construct();

        $this->postProcess();

        $shopCollection = array();

        $sql = 'SELECT * FROM '._DB_PREFIX_.'shop';
        if ($results = Db::getInstance()->ExecuteS($sql)) {
            foreach ($results as $row) {
                $shop = new Shop($row['id_shop']);
                $lengowExport = new LengowExport(array(
                    "shop_id" => $shop->id
                ));

                $shopCollection[]= array(
                    'shop' => $shop,
                    'link' => LengowCore::getExportUrl($shop->id),
                    'total_product' => $lengowExport->getTotalProduct(),
                    'total_export_product' => $lengowExport->getTotalExportProduct(),
                    'last_export' => Configuration::get('LENGOW_LAST_EXPORT', null, null, $shop->id),
                    'option_selected' => Configuration::get('LENGOW_EXPORT_SELECTION', null, null, $shop->id),
                    'option_variation' => Configuration::get('LENGOW_EXPORT_ALL_VARIATIONS', null, null, $shop->id),
                    'option_product_out_of_stock' => Configuration::get('LENGOW_EXPORT_OUT_STOCK', null, null, $shop->id),
                    'list' => $this->buildList($shop->id)
                );
            }
        }
        $this->context->smarty->assign('shopCollection', $shopCollection);

    }

    /**
     * v3
     * Set Options
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
                        Configuration::updatevalue('LENGOW_EXPORT_ALL_VARIATIONS', $state, null, null, $shopId);
                        $this->reloadTotal($shopId);
                    }
                    break;
                case 'change_option_selected':
                    $state = isset($_REQUEST['state']) ? $_REQUEST['state'] : null;
                    $shopId = isset($_REQUEST['id_shop']) ? (int)$_REQUEST['id_shop'] : null;
                    if ($state !== null) {
                        Configuration::updatevalue('LENGOW_EXPORT_SELECTION', $state, null, null, $shopId);
                        $this->reloadTotal($shopId);
                        $state = Configuration::get('LENGOW_EXPORT_SELECTION', null, null, $shopId);
                        if ($state) {
                            echo "$('#block_".$shopId." .lengow_feed_block_footer_content').show();";
                        } else {
                            echo "$('#block_".$shopId." .lengow_feed_block_footer_content').hide();";
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
                        $this->setProductSelection($productId, $state, $shopId);
                        $this->reloadTotal($shopId);
                    }
                    break;
                case 'load_table':
                    $shopId = isset($_REQUEST['id_shop']) ? (int)$_REQUEST['id_shop'] : null;
                    echo '$("#block_'.$shopId.' .lengow_feed_block_footer_content").html("'.
                        preg_replace('/\r|\n/', '', addslashes($this->buildList($shopId))).'");';
                    break;
            }
            exit();
        }
    }

    public function reloadTotal($shopId)
    {
        $lengowExport = new LengowExport(array(
            "shop_id" => $shopId
        ));
        echo '$("#block_'.$shopId.' .lengow_exported").html("'.$lengowExport->getTotalExportProduct().'");';
        echo '$("#block_'.$shopId.' .lengow_total").html("'.$lengowExport->getTotalProduct().'");';
    }

    public function setProductSelection($productId, $value, $shopId)
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
                Db::getInstance()->Insert('lengow_product', array(
                    'id_product' => $productId,
                    'id_shop' => $shopId
                ));
            }
        }
    }

    public function buildList($shopId)
    {
        $fields_list = array();

        $fields_list['id_product'] = array(
            'title' => $this->l('ID'),
            'class' => 'center',
            'width' => 70,
            'filter' => true,
            'filter_key' => 'p.id_product',
        );
        $fields_list['image'] = array(
            'title' => $this->l('Image'),
            'align' => 'center',
            'image' => 'p',
            'width' => 70,
        );
        $fields_list['name'] = array(
            'title' => $this->l('Name'),
            'filter' => true,
            'filter_key' => 'pl.name',
        );
        $fields_list['reference'] = array(
            'title' => $this->l('Reference'),
            'align' => 'left',
            'width' => 80,
            'filter' => true,
            'filter_key' => 'p.reference',
        );
//
//        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
//            $this->fields_list['shopname'] = array(
//                'title' => $this->l('Default shop:'),
//                'width' => 230,
//                'filter_key' => 'shop!name',
//            );
//        } else {
        $fields_list['category_name'] = array(
            'title' => $this->l('Category'),
            'width' => 'auto',
            'filter_key' => 'cl!name',
        );
//        }
        $fields_list['price'] = array(
            'title' => $this->l('Original price'),
            'width' => 90,
            'type' => 'price',
            'class' => 'right',
            'filter_key' => 'a!price'
        );
        $fields_list['price_final'] = array(
            'title' => $this->l('Final price'),
            'width' => 90,
            'type' => 'price',
            'class' => 'right',
            'havingFilter' => true,
            'orderby' => false
        );
        $fields_list['quantity'] = array(
            'title' => $this->l('Quantity'),
            'width' => 90,
            'class' => 'right',
            'filter_key' => 'sav!quantity',
            'orderby' => true,
            'hint' => $this->l('This is the quantity available in the current shop/group.'),
        );
        $fields_list['id_lengow_product'] = array(
            'title' => $this->l('Lengow status'),
            'width' => 'auto',
            'class' => 'center',
            'type' => 'switch_product',
            'button_search' => true
        );

//        if ((int)$this->id_current_category) {
//            $this->fields_list['position'] = array(
//                'title' => $this->l('Position'),
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
        AND pl.id_lang = 1 AND pl.id_shop = '.(int)$shopId.')';
        $join[] = ' LEFT JOIN '._DB_PREFIX_.'lengow_product lp ON (lp.id_product = p.id_product
        AND lp.id_shop = '.(int)$shopId.' ) ';
        if (_PS_VERSION_ >= '1.5') {
            $join[] = 'INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON (p.`id_product` = ps.`id_product`
            AND ps.id_shop = ' . (int)$shopId . ') ';
            $join[] = ' LEFT JOIN '._DB_PREFIX_.'stock_available sav ON (sav.id_product = p.id_product
            AND sav.id_product_attribute = 0 AND sav.id_shop = ' . (int)$shopId . ')';
        }
        if (Shop::isFeatureActive()) {
            $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (ps.`id_category_default` = cl.`id_category`
            AND pl.`id_lang` = cl.`id_lang` AND cl.id_shop = ' . (int)$shopId . ')';
            $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'shop` shop ON (shop.id_shop = ' . (int)$shopId . ') ';
        } else {
            $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (p.`id_category_default` = cl.`id_category`
            AND pl.`id_lang` = cl.`id_lang` AND cl.id_shop = 1)';
        }
        if (_PS_VERSION_ >= '1.5') {
            $select[] = ' sav.quantity ';
            $where[] = ' ps.active = 1 ';
        } else {
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
            "sql" => array(
                "select" => $select,
                "from" => $from,
                "join" => $join,
                "where" => $where,
            )
        ));
        $collection = $list->executeQuery();
        //calcul price
        $nb = count($collection);
        if ($collection) {
            for ($i = 0; $i < $nb; $i++) {
                $collection[$i]['price'] = Tools::convertPrice(
                    $collection[$i]['price'],
                    $this->context->currency,
                    true,
                    $this->context
                );
                $collection[$i]['price_final'] = Product::getPriceStatic(
                    $collection[$i]['id_product'],
                    true,
                    null,
                    2,
                    null,
                    false,
                    true,
                    1,
                    true
                );

                $join = array();
                $join[] = ' INNER JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = p.`id_product`
                ' . (!Shop::isFeatureActive() ? ' AND i.cover=1' : '') . ')';
                if (Shop::isFeatureActive()) {
                    $aliasImage = 'imgs';
                    $join[] = 'INNER JOIN `' . _DB_PREFIX_ . 'image_shop` imgs ON (imgs.`id_image` = i.`id_image`
                    AND imgs.`cover` = 1 AND imgs.id_shop=' . (int)$shopId . ')';
                } else {
                    $aliasImage = 'i';
                }
                $imageSql = ' SELECT '.$aliasImage.'.id_image';
                $imageSql.= ' FROM '._DB_PREFIX_.'product p ';
                $imageSql.= join(' ', $join);
                $imageSql.= ' WHERE p.id_product = '.$collection[$i]['id_product'];
                $imageSql.= ' GROUP BY p.id_product';
                $imageCollection = Db::getInstance()->executeS($imageSql, true, false);
                if (count($imageCollection) > 0) {
                    $path_to_image = _PS_IMG_DIR_.'p/'.Image::getImgFolderStatic($imageCollection[0]['id_image']).
                        (int)$imageCollection[0]['id_image'].'.jpg';
                    $collection[$i]['image'] = ImageManager::thumbnail(
                        $path_to_image,
                        'product_mini_'.$collection[$i]['id_product'].'_'.$shopId.'.jpg',
                        45,
                        'jpg'
                    );
                } else {
                    $collection[$i]['image'] = '';
                }
            }
        }
        $list->updateCollection($collection);
        $paginationBlock = $list->renderPagination(array(
            'nav_class' => 'lengow_feed_pagination'
        ));


        return $paginationBlock.$list->display().$paginationBlock;
    }
}
