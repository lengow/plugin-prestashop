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

        $this->context->smarty->assign('report_mail_address', LengowConfiguration::getReportEmailAddress());
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
        $fields_list['lengow_status'] = array(
            'title' => $this->module->l('Status'),
            'align' => 'center',
            'display_callback' => 'LengowOrderController::displayLengowState',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'lo.order_lengow_state',
            'filter_type' => 'select',
            'filter_collection' => array(
                array('id' => 'new', 'text' => 'new'),
                array('id' => 'waiting_shipment', 'text' => 'waiting_shipment'),
                array('id' => 'shipped', 'text' => 'shipped'),
                array('id' => 'closed', 'text' => 'closed'),
                array('id' => 'refunded', 'text' => 'refunded'),
            ),
        );
        $fields_list['marketplace'] = array(
            'title' => $this->module->l('Marketplace'),
            'align' => 'center',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'lo.marketplace',
            'filter_type' => 'select',
            'filter_collection' => $this->getMarketplaces(),
        );
        if (_PS_VERSION_ >= '1.5') {
            if (Shop::isFeatureActive()) {
                $fields_list['shop_name'] = array(
                    'title' => $this->module->l('Shop'),
                    'filter' => true,
                    'filter_order' => true,
                    'filter_key' => 'shop.name',
                    'filter_type' => 'select',
                    'filter_collection' => $this->getShops()
                );
            }
        }
        $fields_list['id_order_lengow'] = array(
            'title' => $this->module->l('Id Order'),
            'class' => 'center',
            'display_callback' => 'LengowOrderController::displayOrderLink',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'lo.id_order_lengow',
        );
        $fields_list['reference'] = array(
            'title' => $this->module->l('Ref Presta'),
            'class' => 'center',
            'display_callback' => 'LengowOrderController::displayOrderLink',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'o.reference',
        );
        $fields_list['order_date'] = array(
            'title' => $this->module->l('Date'),
            'class' => 'center',
            'type' => 'date',
            'filter' => true,
            'filter_type' => 'date',
            'filter_key' => 'lo.order_date',
            'filter_order' => true,
        );

        $fields_list['delivery_country_iso'] = array(
            'title' => $this->module->l('Country'),
            'align' => 'center',
            'type' => 'flag_country',
            'filter_key' => 'lo.delivery_country_iso',
            'filter_order' => true,
        );
        $fields_list['nb_item'] = array(
            'title' => $this->module->l('Items'),
            'align' => 'center',
            'filter_key' => 'lo.order_item',
            'filter_order' => true,
        );
        $fields_list['total_paid'] = array(
            'title' => $this->module->l('Total'),
            'align' => 'center',
            'type' => 'price',
            'class' => 'nowrap',
            'filter_key' => 'lo.total_paid',
            'filter_order' => true,
        );
        $fields_list['log_status'] = array(
            'title' => $this->module->l('Status Lgw'),
            'align' => 'center',
            'type' => 'log_status',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'log_status',
            'filter_type' => 'select',
            'filter_collection' => array(
                array('id' => 1, 'text' => 'success'),
                array('id' => 2, 'text' => 'error')
            ),
        );
        $select = array(
            'lo.id',
            'lo.id_order_lengow',
            'lo.marketplace',
            'lo.total_paid',
            'lo.delivery_country_iso',
            'lo.order_item as nb_item',
            'o.reference',
            'lo.date_add as order_date',
            'lo.order_lengow_state as lengow_status',
            'lo.id_order'
        );
        $select_having = array(
            '(SELECT IFNULL(lli.type, 0) FROM ps_lengow_logs_import lli
            INNER JOIN ps_lengow_orders lo ON (lo.id = lli.id_order_lengow)
            WHERE lo.id_order = o.id_order AND lli.is_finished = 0 LIMIT 1) as log_status',
        );
        $from = 'FROM '._DB_PREFIX_.'lengow_orders lo';
        $join = array();

        $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.id_order = lo.id_order) ';
        if (_PS_VERSION_ >= '1.5') {
            if (Shop::isFeatureActive()) {
                $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'shop` shop ON (lo.id_shop = shop.id_shop) ';
                $select[] = 'shop.name as shop_name';
            }
        }

        $currentPage = isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
        $orderValue = isset($_REQUEST['order_value']) ? $_REQUEST['order_value'] : '';
        $orderColumn = isset($_REQUEST['order_column']) ? $_REQUEST['order_column'] : '';

        $list = new LengowList(array(
            "id" => 'order',
            "fields_list" => $fields_list,
            "identifier" => 'id',
            "selection" => true,
            "selection_condition" => 'log_status',
            "controller" => 'AdminLengowOrder',
            "shop_id" => $shopId,
            "current_page" => $currentPage,
            "order_value" => $orderValue,
            "order_column" => $orderColumn,
            "ajax" => true,
            "sql" => array(
                "select" => $select,
                "from" => $from,
                "join" => $join,
                "select_having" => $select_having,
                "order" => 'order_date DESC',
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
        $html.='<a href="#" style="display:none;"
                data-href="'.$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true).'"
                class="lengow_btn lengow_link_tooltip lengow_remove_from_export">
                <i class="fa fa-download"></i> Re Import Order</a>';
        $html.='<a href="#" style="display:none;"
                        data-href="'.$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true).'"
                class="lengow_btn lengow_link_tooltip lengow_add_to_export">
                <i class="fa fa-arrow-right"></i> Re Send Order</a>';
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

    public function getMarketplaces()
    {
        $marketplaces = array();
        $sql = 'SELECT DISTINCT(marketplace) as name FROM `' . _DB_PREFIX_ . 'lengow_orders`';
        $collection = Db::getInstance()->executeS($sql);
        foreach ($collection as $row) {
            $marketplaces[]= array('id' => $row['name'], 'text' =>$row['name']);
        }
        return $marketplaces;
    }

    public function getShops()
    {
        $shops = array();
        $sql = 'SELECT id_shop, name FROM '._DB_PREFIX_.'shop WHERE active = 1';
        $collection = Db::getInstance()->ExecuteS($sql);
        foreach ($collection as $row) {
            $shops[]= array('id' => $row['id_shop'], 'text' =>$row['name']);
        }
        return $shops;
    }

    public static function displayLengowState($key, $value, $item)
    {
        return '<span class="lengow_label lengow_label_'.$value.'">'.$value.'</span>';
    }

    public static function displayOrderLink($key, $value, $item)
    {
        $link = new LengowLink();
        return '<a href="'.$link->getAbsoluteAdminLink('AdminOrders').'&vieworder&id_order='.$item['id_order'].
        '" target="_blank">'.$value.'</a>';
    }

}
