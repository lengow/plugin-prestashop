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


        $this->context->smarty->assign('cron_active', LengowCron::getCron());
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
                case 're_import':
                    $id_order_lengow = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
                    LengowOrder::reImportOrder($id_order_lengow);

                    $list = $this->loadTable();
                    $row = $list->getRow(' id = '.(int)$id_order_lengow);
                    $html = $list->displayRow($row);
                    $html = preg_replace('/\r|\n/', '', addslashes($html));
                    echo 'lengow_jquery("#order_'.$id_order_lengow.'").replaceWith("'.$html.'");';
                    break;
                case 're_send':
                    $id_order_lengow = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
                    LengowOrder::reSendOrder($id_order_lengow);

                    $list = $this->loadTable();
                    $row = $list->getRow(' id = '.(int)$id_order_lengow);
                    $html = $list->displayRow($row);
                    $html = preg_replace('/\r|\n/', '', addslashes($html));
                    echo 'lengow_jquery("#order_'.$id_order_lengow.'").replaceWith("'.$html.'");';
                    break;
                case 'import_all':
                    if (_PS_VERSION_ < '1.5') {
                        $import = new LengowImport(array(
                            'log_output' => false,
                        ));
                    } else {
                        if (Shop::getContextShopID()) {
                            $import = new LengowImport(array(
                                'shop_id' => Shop::getContextShopID(),
                                'log_output' => false,
                            ));
                        } else {
                            $import = new LengowImport(array(
                                'log_output' => false,
                            ));
                        }
                    }
                    $return = $import->exec();
                    $message = $this->loadMessage($return);

                    echo 'lengow_jquery("#lengow_wrapper_messages").html("';
                    echo '<div class=\"lengow_alert\">'.addslashes(join('<br/>', $message)).'</div>");';
                    echo 'lengow_jquery("#lengow_import_orders").html("Update Orders");';
                    echo 'lengow_jquery("#lengow_order_table_wrapper").html("'.
                        preg_replace('/\r|\n/', '', addslashes($this->buildTable())).'");';
                    break;
                case 'update_order':
                    $import = new LengowImport(array(
                        'log_output' => false,
                        'marketplace_sku' => Tools::getValue('marketplace_sku'),
                        'marketplace_name' => Tools::getValue('marketplace_name'),
                        'delivery_address_id' => Tools::getValue('delivery_address_id'),
                        'shop_id' => Tools::getValue('shop_id'),
                        'type' => Tools::getValue('type'),
                    ));
                    $return = $import->exec();
                    $message = array();
                    if (isset($result['marketplace_sku']) && isset($result['marketplace_name'])) {
                        $message[] = LengowMain::decodeLogMessage('toolbox.order.order_import_success', null, array(
                            'marketplace_sku' => $result['marketplace_sku'],
                            'marketplace_name' => $result['marketplace_name']
                        ));
                    } else {
                        $message[] = LengowMain::decodeLogMessage('toolbox.order.order_import_failed', null, array(
                            'log_url' => '/modules/lengow/toolbox/log.php'
                        ));
                    }
                    echo 'lengow_jquery("#lengow_wrapper_messages").html("';
                    echo '<div class=\"lengow_alert\">'.addslashes(join('<br/>', $message)).'</div>");';
                    echo 'lengow_jquery("#lengow_update_order").html("'
                        .$this->locale->t('toolbox.order.import_one_order').'");';
                    echo 'lengow_jquery("#lengow_order_table_wrapper").html("'.
                        preg_replace('/\r|\n/', '', addslashes($this->buildTable())).'");';
                    break;
                case 'update_some_orders':
                    $import = new LengowImport(array(
                        'log_output' => false,
                        'days' => Tools::getValue('days'),
                        'shop_id' => Tools::getValue('shop_id'),
                    ));
                    $return = $import->exec();
                    $message = $this->loadMessage($return);
                    echo 'lengow_jquery("#lengow_wrapper_messages").html("';
                    echo '<div class=\"lengow_alert\">'.addslashes(join('<br/>', $message)).'</div>");';
                    echo 'lengow_jquery("#lengow_update_some_orders").html("'
                        .$this->locale->t('toolbox.order.import_shop_order').'");';
                    echo 'lengow_jquery("#lengow_order_table_wrapper").html("'.
                        preg_replace('/\r|\n/', '', addslashes($this->buildTable())).'");';
                    break;
                case 'synchronize':
                    $id_order = isset($_REQUEST['id_order']) ? (int)$_REQUEST['id_order'] : 0;
                    $lengow_order = new LengowOrder($id_order);
                    $lengow_order->synchronizeOrder();
                    $lengow_link = new LengowLink();
                    $prestashop_order_controller = $lengow_link->getAbsoluteAdminLink('AdminOrders', false, true);
                    $order_url = $prestashop_order_controller.'&id_order='.$id_order.'&vieworder';
                    Tools::redirectAdmin($order_url);
                    break;
                case 'load_marketplace':
                    $shop_id = Tools::getValue('shop_id');

                    $marketplaces = LengowMarketplace::getMarketplacesByShop($shop_id);
                    $this->context->smarty->assign('marketplaces', $marketplaces);
                    $module = Module::getInstanceByName('lengow');

                    $display_select_marketplace = $module->display(
                        _PS_MODULE_LENGOW_DIR_,
                        'views/templates/admin/lengow_order/helpers/view/select_marketplace.tpl'
                    );
                    echo 'lengow_jquery("#select_marketplace").html("'.
                        preg_replace('/\r|\n/', '', addslashes($display_select_marketplace)).'");';
                    exit();
                    break;
                case 'cancel_re_import':
                    $id_order = isset($_REQUEST['id_order']) ? (int)$_REQUEST['id_order'] : 0;
                    $lengow_order = new LengowOrder($id_order);
                    $new_id_order = $lengow_order->cancelAndreImportOrder();
                    if (!$new_id_order) {
                        $new_id_order = $id_order;
                    }
                    $lengow_link = new LengowLink();
                    $prestashop_order_controller = $lengow_link->getAbsoluteAdminLink('AdminOrders', false, true);
                    $order_url = $prestashop_order_controller.'&id_order='.$new_id_order.'&vieworder';
                    Tools::redirectAdmin($order_url);
                    break;
            }
            exit();
        }
    }

    public function loadTable()
    {
        $toolbox = Context::getContext()->smarty->getVariable('toolbox')->value;
        $fields_list = array();
        $fields_list['lengow_status'] = array(
            'title'             => $this->locale->t('order.table.order_lengow_state'),
            'align'             => 'center',
            'class'             => 'link',
            'display_callback'  => 'LengowOrderController::displayLengowState',
            'filter'            => true,
            'filter_order'      => true,
            'filter_key'        => 'lo.order_lengow_state',
            'filter_type'       => 'select',
            'filter_collection' => array(
                array('id' => 'waiting_shipment', 'text' => 'waiting_shipment'),
                array('id' => 'shipped', 'text' => 'shipped'),
                array('id' => 'closed', 'text' => 'closed'),
                array('id' => 'refunded', 'text' => 'refunded'),
            ),
        );
        $fields_list['marketplace_name'] = array(
            'title'             => $this->locale->t('order.table.marketplace_name'),
            'align'             => 'center',
            'class'             => 'link',
            'display_callback'  => 'LengowOrderController::displayMarketplaceName',
            'filter'            => true,
            'filter_order'      => true,
            'filter_key'        => 'lo.marketplace_name',
            'filter_type'       => 'select',
            'filter_collection' => $this->getMarketplaces(),
        );
        if (_PS_VERSION_ >= '1.5') {
            if (Shop::isFeatureActive() && !Shop::getContextShopID()) {
                $fields_list['shop_name'] = array(
                    'class'             => 'link',
                    'title'             => $this->locale->t('order.table.shop_name'),
                    'filter'            => true,
                    'filter_order'      => true,
                    'filter_key'        => 'shop.name',
                    'filter_type'       => 'select',
                    'filter_collection' => $this->getShops()
                );
            }
        }
        $fields_list['marketplace_sku'] = array(
            'title'             => $this->locale->t('order.table.marketplace_sku'),
            'class'             => 'center link',
            'display_callback'  => 'LengowOrderController::displayOrderLink',
            'filter'            => true,
            'filter_order'      => true,
            'filter_key'        => 'lo.marketplace_sku',
        );
        $fields_list['reference'] = array(
            'title'             => $this->locale->t('order.table.reference_prestashop'),
            'class'             => 'center link reference',
            'display_callback'  => 'LengowOrderController::displayOrderLink',
            'filter'            => true,
            'filter_order'      => true,
            'filter_key'        => 'o.reference',
        );
        $fields_list['order_date'] = array(
            'title'             => $this->locale->t('order.table.order_date'),
            'class'             => 'center link',
            'type'              => 'date',
            'filter'            => true,
            'filter_type'       => 'date',
            'filter_key'        => 'lo.order_date',
            'filter_order'      => true,
        );
        $fields_list['delivery_country_iso'] = array(
            'title'             => $this->locale->t('order.table.delivery_country'),
            'class'             => 'center link',
            'type'              => 'flag_country',
            'filter_key'        => 'lo.delivery_country_iso',
            'filter_order'      => true,
        );
        $fields_list['nb_item'] = array(
            'title'             => $this->locale->t('order.table.order_item'),
            'class'             => 'center link',
            'filter_key'        => 'lo.order_item',
            'filter_order'      => true,
        );
        $fields_list['total_paid'] = array(
            'title'             => $this->locale->t('order.table.total_paid'),
            'type'              => 'price',
            'class'             => 'nowrap center link',
            'filter_key'        => 'lo.total_paid',
            'filter_order'      => true,
        );
        $fields_list['log_status'] = array(
            'title'             => $this->locale->t('order.table.status_lengow'),
            'align'             => 'center',
            'class'             => 'lengow_status',
            'type'              => 'log_status',
            'display_callback'  => 'LengowOrderController::displayLogStatus',
            'filter'            => true,
            'filter_order'      => true,
            'filter_key'        => 'log_status',
            'filter_type'       => 'select',
            'filter_collection' => array(
                array('id' => 1, 'text' => 'success'),
                array('id' => 2, 'text' => 'error')
            ),
        );
        if ($toolbox) {
            $fields_list['extra'] = array(
                'title'             => $this->locale->t('order.table.extra'),
                'type'              => 'text',
                'display_callback'  => 'LengowOrderController::displayLengowExtra'
            );
        }
        $select = array(
            'lo.id',
            'lo.marketplace_sku',
            'lo.marketplace_name',
            'IFNULL(lo.marketplace_label,lo.marketplace_name) as marketplace_label',
            'lo.total_paid',
            'lo.extra',
            'lo.delivery_country_iso',
            'lo.order_item as nb_item',
            (_PS_VERSION_ < 1.5 ? 'o.id_order as reference' : 'o.reference'),
            'lo.order_date',
            'lo.order_lengow_state as lengow_status',
            'lo.id_order',
            'lo.currency'
        );
        $select_having = array(
            ' (SELECT IFNULL(lli.type, 0) FROM '._DB_PREFIX_.'lengow_logs_import lli
            WHERE lli.id_order_lengow = lo.id AND lli.is_finished = 0 LIMIT 1) as log_status',
        );
        $from = 'FROM '._DB_PREFIX_.'lengow_orders lo';
        $join = array();

        $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.id_order = lo.id_order) ';
        if (_PS_VERSION_ >= '1.5') {
            if (Shop::isFeatureActive()) {
                if (Shop::getContextShopID()) {
                    $join[] = 'INNER JOIN `' . _DB_PREFIX_ . 'shop` shop ON (lo.id_shop = shop.id_shop
                    AND shop.id_shop = '.(int)Shop::getContextShopID().') ';
                } else {
                    $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'shop` shop ON (lo.id_shop = shop.id_shop) ';
                }
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
            "current_page" => $currentPage,
            "order_value" => $orderValue,
            "order_column" => $orderColumn,
            "ajax" => true,
            "sql" => array(
                "select" => $select,
                "from" => $from,
                "join" => $join,
                "select_having" => $select_having,
                "order" => 'IF (order_lengow_state = "waiting_shipment",1,0) DESC, order_date DESC',
            )
        ));
        return $list;
    }

    /**
     * Reload Total product / Exported product
     * @return string
     */
    public function buildTable()
    {

        $list = $this->loadTable();

        $list->executeQuery();
        $paginationBlock = $list->renderPagination(array(
            'nav_class' => 'lengow_feed_pagination'
        ));

        $lengow_link = new LengowLink();

        $html='<div class="lengow_table_top">';
        $html.='<div class="lengow_toolbar">';
        $html.='<input type="checkbox" id="select_order" class="lengow_select_all"/>';
        $html.='<a href="#" style="display:none;"
                data-href="'.$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true).'"
                class="lengow_btn lengow_link_tooltip lengow_mass_re_import btn btn-primary">
                <i class="fa fa-download"></i> '.$this->locale->t('order.screen.button_reimport_order').' </a>';
        $html.='<a href="#" style="display:none;"
                        data-href="'.$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true).'"
                class="lengow_btn lengow_link_tooltip lengow_mass_re_send btn btn-primary">
                <i class="fa fa-arrow-right"></i> '.$this->locale->t('order.screen.button_resend_order').' </a>';
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
        $sql = 'SELECT DISTINCT(marketplace_name) as name,
        IFNULL(marketplace_label, marketplace_name) as marketplace_label FROM `' . _DB_PREFIX_ . 'lengow_orders`';
        $collection = Db::getInstance()->executeS($sql);
        foreach ($collection as $row) {
            $marketplaces[]= array('id' => $row['name'], 'text' =>$row['marketplace_label']);
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
        return '<span class="lengow_label lengow_label_'.$value.'" title="'.$item['id'].'" >'.$value.'</span>';
    }

    public static function displayOrderLink($key, $value, $item)
    {
        $toolbox = Context::getContext()->smarty->getVariable('toolbox')->value;
        $link = new LengowLink();
        if ($item['id_order']) {
            if (!$toolbox) {
                return '<a href="'.$link->getAbsoluteAdminLink('AdminOrders', false, true).'&vieworder&id_order='.
                $item['id_order'].'" target="_blank">' . $value . '</a>';
            } else {
                return $value;
            }
        } else {
            if ($key == 'reference') {
                return '<span class="lengow_label lengow_label_red">NOT IMPORTED</span>';
            } else {
                return $value;
            }
        }
    }

    public static function displayMarketplaceName($key, $value, $item)
    {
        return $item['marketplace_label'];
    }

    public static function displayLogStatus($key, $value, $item)
    {
        //check if order actions in progress

        if ($item['id_order'] > 0) {
            $actions = LengowAction::getOrderActiveAction($item['id_order'], 'ship');
            if ($actions) {
                $value = '<i class="fa fa-info-circle lengow_orange lengow_link_tooltip" data-html="true"
                        data-original-title="Tracking send, waiting returns"
                        ></i>';
                return $value;
            }
        }

        $errorMessage = array();
        $logCollection = LengowOrder::getOrderLogs($item['id'], null, false);
        if (count($logCollection)>0) {
            foreach ($logCollection as $row) {
                $errorMessage[] = LengowMain::decodeLogMessage($row['message']);
            }
        }
        $link = new LengowLink();
        if ($item[$key]) {
            if ($item[$key] == '2') {
                $value = '<i class="fa fa-info-circle lengow_red lengow_link_tooltip" data-html="true"
                                    data-original-title="'.join('<br/>', $errorMessage).'"
                                    ></i>';
                $value.= ' <a href="#"  class="lengow_re_send"
                                    data-href="'.$link->getAbsoluteAdminLink('AdminLengowOrder', true).'"
                                    data-action="re_send"
                                    data-order="'.$item['id'].'"
                                    data-type="'.$item[$key].'"
                                    >Re Send</a>';
            } else {
                $value = '<i class="fa fa-info-circle lengow_red lengow_link_tooltip" data-html="true"
                                    data-original-title="'.join('<br/>', $errorMessage).'"
                                    ></i>';
                $value.= ' <a href="#" class="lengow_re_import"
                                    data-href="'.$link->getAbsoluteAdminLink('AdminLengowOrder', true).'"
                                    data-action="re_import"
                                    data-order="'.$item['id'].'"
                                    data-type="'.$item[$key].'"
                                    >Re Import</a>';
            }
        } else {
            $value = '<i class="fa fa-circle lengow_green"></i>';
        }
        return $value;
    }

    public static function displayLengowExtra($key, $value, $item)
    {
        return $item['extra'];
    }

    public function loadMessage($return)
    {
        $message = array();
        if (isset($return['order_new']) && count($return['order_new']) > 0) {
            $message[]= (int)$return['order_new'].' imported orders';
        }
        if (isset($return['order_update']) && count($return['order_update']) > 0) {
            $message[]= (int)$return['order_update'].' updated orders';
        }
        if (isset($return['order_error']) && count($return['order_error']) > 0) {
            $message[]= (int)$return['order_error'].' orders in error';
        }

        if (isset($return['error'])) {
            foreach ($return['error'] as $shop => $values) {
                if ((int)$shop > 0) {
                    $shop = new LengowShop($shop);
                    $shopName = $shop->name. ' : ';
                } else {
                    $shopName = '';
                }
                if (is_array($values)) {
                    $message[]= $shopName.join(', ', LengowMain::decodeLogMessage($values));
                } else {
                    $message[]= $shopName.LengowMain::decodeLogMessage($values);
                }
            }
        }
        if (LengowImport::isInProcess()) {
            $message[] = LengowMain::decodeLogMessage('lengow_log.error.rest_time_to_import', null, array(
                'rest_time' => LengowImport::restTimeToImport()
            ));
        }

        return $message;
    }
}
