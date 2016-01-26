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

class LengowOrderController extends LengowController
{
    /**
     * Display data page
     */
    public function display()
    {
        $last_import =  LengowMain::getLastImport();

        $orderCollection = array(
            'last_import_date'  => $last_import['timestamp'],
            'last_import_type'  => $last_import['type'],
            'link'              => LengowMain::getImportUrl()
        );

        $this->context->smarty->assign('lengow_table', $this->buildTable());
        $this->context->smarty->assign('orderCollection', $orderCollection);
        parent::display();
    }

    /**
     * Update data
     */
    public function postProcess()
    {
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
        if ($action) {
            switch ($action) {
                case 'load_table':
                    echo 'lengow_jquery("#lengow_order_table_wrapper").html("'.
                        preg_replace('/\r|\n/', '', addslashes($this->buildTable())).'");';
                    break;
            }
            exit();
        }
    }
    /**
     * Reload Total product / Exported product
     * @param $shopId
     * @return string
     */
    public function buildTable($shopId = null)
    {
        $fields_list = array();
        $fields_list['marketplace'] = array(
            'title' => $this->module->l('Marketplace'),
            'align' => 'center',
            'filter' => true,
            'filter_key' => 'lo.marketplace',
        );
        if (_PS_VERSION_ >= '1.5') {
            if (Shop::isFeatureActive()) {
                $fields_list['shop_name'] = array(
                    'title' => $this->module->l('Shop'),
                    'filter' => true,
                    'filter_key' => 'shop.name',
                );
            }
        }
        $fields_list['id_order_lengow'] = array(
            'title' => $this->module->l('Id Order'),
            'class' => 'center',
            'filter' => true,
            'filter_key' => 'lo.id_order_lengow',
        );
        $fields_list['reference'] = array(
            'title' => $this->module->l('Ref Presta'),
            'class' => 'center',
            'filter' => true,
            'filter_key' => 'o.reference',
        );
        $fields_list['order_date'] = array(
            'title' => $this->module->l('Date'),
            'class' => 'center',
            'filter' => true,
            'filter_key' => 'lo.date_add',
        );

        $fields_list['delivery_country_iso'] = array(
            'title' => $this->module->l('Country'),
            'align' => 'center',
            'type' => 'flag_country'
        );
        $fields_list['nb_item'] = array(
            'title' => $this->module->l('Items'),
            'align' => 'center',
        );
        $fields_list['total_paid'] = array(
            'title' => $this->module->l('Total'),
            'align' => 'center',
            'type' => 'price',
            'button_search' => true
        );

//        $fields_list['reference'] = array(
//            'title' => $this->module->l('Reference'),
//            'align' => 'left',
//            'width' => 80,
//            'filter' => true,
//            'filter_key' => 'p.reference',
//        );
//        $fields_list['category_name'] = array(
//            'title' => $this->module->l('Category'),
//            'width' => 'auto',
//            'filter' => true,
//            'filter_key' => 'cl.name',
//        );
//        $fields_list['price'] = array(
//            'title' => $this->module->l('Original price'),
//            'width' => 90,
//            'type' => 'price',
//            'class' => 'right',
//            'filter_key' => 'a!price'
//        );
//        $fields_list['price_final'] = array(
//            'title' => $this->module->l('Final price'),
//            'width' => 90,
//            'type' => 'price',
//            'class' => 'right',
//            'havingFilter' => true,
//            'orderby' => false
//        );
//        $fields_list['quantity'] = array(
//            'title' => $this->module->l('Quantity'),
//            'width' => 90,
//            'class' => 'right',
//            'filter_key' => 'sav!quantity',
//            'orderby' => true,
//            'hint' => $this->module->l('This is the quantity available in the current shop/group.'),
//        );
//        $fields_list['id_lengow_product'] = array(
//            'title' => $this->module->l('Lengow status'),
//            'width' => 'auto',
//            'class' => 'center',
//            'type' => 'switch_product',
//            'button_search' => true
//        );

        $select = array(
            "lo.id",
            "lo.id_order_lengow",
            "lo.marketplace",
            "lo.total_paid",
            "lo.delivery_country_iso",
            "lo.order_item as nb_item",
            "o.reference",
            'lo.date_add as order_date'
        );
        $from = 'FROM '._DB_PREFIX_.'lengow_orders lo';
//
//        $join[] = ' INNER JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = p.id_product
//        AND pl.id_lang = 1 '.(_PS_VERSION_ < '1.5' ? '': ' AND pl.id_shop = '.(int)$shopId).')';
//        $join[] = ' LEFT JOIN '._DB_PREFIX_.'lengow_product lp ON (lp.id_product = p.id_product
//        AND lp.id_shop = '.(int)$shopId.' ) ';
//        if (_PS_VERSION_ >= '1.5') {
//            $join[] = 'INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON (p.`id_product` = ps.`id_product`
//            AND ps.id_shop = ' . (int)$shopId . ') ';
//            $join[] = ' LEFT JOIN '._DB_PREFIX_.'stock_available sav ON (sav.id_product = p.id_product
//            AND sav.id_product_attribute = 0 AND sav.id_shop = ' . (int)$shopId . ')';
//        }
        $join = array();

        $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.id_order = lo.id_order) ';
        if (_PS_VERSION_ >= '1.5') {
            if (Shop::isFeatureActive()) {
                $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'shop` shop ON (lo.id_shop = shop.id_shop) ';
                $select[] = 'shop.name as shop_name';
            }
        }

        $currentPage = isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;

        $list = new LengowList(array(
            "id" => 'shop_'.$shopId,
            "fields_list" => $fields_list,
            "identifier" => 'id',
            "selection" => true,
            "controller" => 'AdminLengowOrder',
            "shop_id" => $shopId,
            "current_page" => $currentPage,
            "ajax" => true,
            "sql" => array(
                "select" => $select,
                "from" => $from,
                "join" => $join,
                "where" => array(),
            )
        ));

        $list->executeQuery();
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
