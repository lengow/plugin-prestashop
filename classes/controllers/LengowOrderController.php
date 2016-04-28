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

    protected $list;

    /**
     * Display data page
     */
    public function display()
    {
        $this->assignLastImportationInfos();
        $this->assignNbOrderImported();

        // datas for toolbox
        $shop = array();
        $shops = LengowShop::findAll();
        foreach ($shops as $s) {
            $shop[$s['id_shop']] = new LengowShop($s['id_shop']);
        }
        $marketplaces = array();
        $days = LengowConfiguration::get('LENGOW_IMPORT_DAYS');
        $this->context->smarty->assign('shop', $shop);
        $this->context->smarty->assign('marketplaces', $marketplaces);
        $this->context->smarty->assign('days', $days);

        $this->context->smarty->assign('lengow_table', $this->buildTable());
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
                    $this->assignLastImportationInfos();
                    $module = Module::getInstanceByName('lengow');
                    $display_last_importation = $module->display(
                        _PS_MODULE_LENGOW_DIR_,
                        'views/templates/admin/lengow_order/helpers/view/last_importation.tpl'
                    );
                    $order_table = $this->buildTable();
                    if ($this->list->getTotal() > 0) {
                        $display_list_order = $order_table;
                    } else {
                        $display_list_order = $module->display(
                            _PS_MODULE_LENGOW_DIR_,
                            'views/templates/admin/lengow_order/helpers/view/no_order.tpl'
                        );
                    }
                    echo 'lengow_jquery("#lengow_last_importation").html("'.
                        preg_replace('/\r|\n/', '', addslashes($display_last_importation)).'");';
                    echo 'lengow_jquery("#lengow_import_orders").html("'
                        .$this->locale->t('order.screen.button_update_orders').'");';
                    echo 'lengow_jquery("#lengow_order_table_wrapper").html("'.
                        preg_replace('/\r|\n/', '', addslashes($display_list_order)).'");';
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
                    $result = $import->exec();
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

    public function assignLastImportationInfos()
    {
        $last_import =  LengowMain::getLastImport();
        $orderCollection = array(
            'last_import_date'  => $last_import['timestamp'],
            'last_import_type'  => $last_import['type'],
            'link'              => LengowMain::getImportUrl()
        );
        $this->context->smarty->assign('cron_active', LengowCron::getCron());
        $this->context->smarty->assign('report_mail_address', LengowConfiguration::getReportEmailAddress());
        $this->context->smarty->assign('orderCollection', $orderCollection);
    }

    public function assignNbOrderImported()
    {
        $sql = 'SELECT COUNT(*) as `total` FROM `'._DB_PREFIX_.'lengow_orders`';
        $nb_order_imported = Db::getInstance()->executeS($sql);
        $this->context->smarty->assign('nb_order_imported', (int)$nb_order_imported[0]['total']);
    }

    public function loadTable()
    {
        if (_PS_VERSION_ >= '1.5' && Shop::isFeatureActive() && !Shop::getContextShopID()) {
            $width = '10%';
        } else {
            $width = '12%';
        }
        $fields_list = array();
        $fields_list['log_status'] = array(
            'title'             => $this->locale->t('order.table.action_lengow'),
            'class'             => 'lengow_status',
            'type'              => 'log_status',
            'width'             => $width,
            'display_callback'  => 'LengowOrderController::displayLogStatus',
            'filter'            => true,
            'filter_order'      => true,
            'filter_key'        => 'log_status',
            'filter_type'       => 'select',
            'filter_collection' => array(
                array('id' => 1, 'text' => $this->locale->t('order.screen.action_success')),
                array('id' => 2, 'text' => $this->locale->t('order.screen.action_error'))
            ),
        );
        $fields_list['lengow_status'] = array(
            'title'             => $this->locale->t('order.table.order_lengow_state'),
            'class'             => 'text-center link',
            'width'             => $width,
            'display_callback'  => 'LengowOrderController::displayLengowState',
            'filter'            => true,
            'filter_order'      => true,
            'filter_key'        => 'lo.order_lengow_state',
            'filter_type'       => 'select',
            'filter_collection' => array(
                array('id' => 'accepted', 'text' => $this->locale->t('order.screen.status_accepted')),
                array('id' => 'waiting_shipment', 'text' => $this->locale->t('order.screen.status_waiting_shipment')),
                array('id' => 'shipped', 'text' => $this->locale->t('order.screen.status_shipped')),
                array('id' => 'closed', 'text' => $this->locale->t('order.screen.status_closed')),
                array('id' => 'canceled', 'text' => $this->locale->t('order.screen.status_canceled')),
            ),
        );
        $fields_list['marketplace_name'] = array(
            'title'             => $this->locale->t('order.table.marketplace_name'),
            'class'             => 'link',
            'width'             => $width,
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
                    'width'             => $width,
                    'title'             => $this->locale->t('order.table.shop_name'),
                    'filter'            => true,
                    'filter_order'      => true,
                    'filter_key'        => 'shop.id_shop',
                    'filter_type'       => 'select',
                    'filter_collection' => $this->getShops()
                );
            }
        }
        $fields_list['marketplace_sku'] = array(
            'title'             => $this->locale->t('order.table.marketplace_sku'),
            'width'             => '14%',
            'class'             => 'link',
            'display_callback'  => 'LengowOrderController::displayOrderLink',
            'filter'            => true,
            'filter_order'      => true,
            'filter_key'        => 'lo.marketplace_sku',
        );
        $fields_list['reference'] = array(
            'title'             => $this->locale->t('order.table.reference_prestashop'),
            'class'             => 'link reference',
            'width'             => $width,
            'display_callback'  => 'LengowOrderController::displayOrderLink',
            'filter'            => true,
            'filter_order'      => true,
            'filter_key'        => 'o.reference',
        );
        $fields_list['order_date'] = array(
            'title'             => $this->locale->t('order.table.order_date'),
            'class'             => 'link',
            'type'              => 'date',
            'width'             => $width,
            'filter'            => true,
            'filter_type'       => 'date',
            'filter_key'        => 'lo.order_date',
            'filter_order'      => true,
        );
        $fields_list['delivery_country_iso'] = array(
            'title'             => $this->locale->t('order.table.delivery_country'),
            'class'             => 'link',
            'width'             => '5%',
            'type'              => 'flag_country',
            'filter_key'        => 'lo.delivery_country_iso',
            'filter_order'      => true,
        );
        $fields_list['nb_item'] = array(
            'title'             => $this->locale->t('order.table.order_item'),
            'width'             => '5%',
            'class'             => 'link text-right',
            'filter_key'        => 'lo.order_item',
            'filter_order'      => true,
        );
        $fields_list['total_paid'] = array(
            'title'             => $this->locale->t('order.table.total_paid'),
            'width'             => '7%',
            'type'              => 'price',
            'class'             => 'link nowrap',
            'filter_key'        => 'lo.total_paid',
            'filter_order'      => true,
        );
        if ($this->toolbox) {
            $fields_list['extra'] = array(
                'title'             => $this->locale->t('order.table.extra'),
                'type'              => 'text',
                'display_callback'  => 'LengowOrderController::displayLengowExtra'
            );
        }
        $fields_list['search'] = array(
            'title'         => '',
            'width'         => '10%',
            'button_search' => true
        );
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
            'lo.currency',
            "'' as search"
        );
        $select_having = array(
            ' (SELECT IFNULL(lli.type, 0) FROM '._DB_PREFIX_.'lengow_logs_import lli
            WHERE lli.id_order_lengow = lo.id AND lli.is_finished = 0 LIMIT 1) as log_status',
        );
        $from = 'FROM '._DB_PREFIX_.'lengow_orders lo';
        $join = array();

        $join[] = 'LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.id_order = lo.id_order) ';
        if (_PS_VERSION_ >= '1.5') {
            if (Shop::isFeatureActive()) {
                if (Shop::getContextShopID()) {
                    $join[] = 'INNER JOIN `'._DB_PREFIX_.'shop` shop ON (lo.id_shop = shop.id_shop
                    AND shop.id_shop = '.(int)Shop::getContextShopID().') ';
                } else {
                    $join[] = 'LEFT JOIN `'._DB_PREFIX_.'shop` shop ON (lo.id_shop = shop.id_shop) ';
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
        $this->list = $this->loadTable();

        $this->list->executeQuery();
        $paginationBlock = $this->list->renderPagination(array(
            'nav_class' => 'lgw-pagination'
        ));

        $lengow_link = new LengowLink();

        $html='<div class="lengow_table_top">';
        $html.='<div class="lengow_toolbar">';
        $html.='<a href="#" style="display:none;"
                data-href="'.$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true).'"
                class="lgw-btn lengow_link_tooltip lengow_mass_re_import btn btn-primary">
                <i class="fa fa-download"></i> '.$this->locale->t('order.screen.button_reimport_order').'</a>';
        $html.='<a href="#" style="display:none;"
                        data-href="'.$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true).'"
                class="lgw-btn lengow_link_tooltip lengow_mass_re_send btn btn-primary">
                <i class="fa fa-arrow-right"></i> '.$this->locale->t('order.screen.button_resend_order').'</a>';
        $html.='</div>';
        $html.= $paginationBlock;
        $html.='<div class="clearfix"></div>';
        $html.='</div>';
        $html.= $this->list->display();
        $html.='<div class="lengow_table_bottom">';
        $html.= $paginationBlock;
        $html.='<div class="clearfix"></div>';
        $html.='</div>';

        return $html;
    }

    public function getMarketplaces()
    {
        $marketplaces = array();
        $sql = 'SELECT DISTINCT(marketplace_name) as name,
        IFNULL(marketplace_label, marketplace_name) as marketplace_label FROM `'._DB_PREFIX_.'lengow_orders`';
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
        // This two lines are useless, but Prestashop validator require it
        $key = $key;
        $item = $item;
        if (empty($value)) {
            $value = 'not_synchronized';
        }
        return '<span class="lgw-label lgw-label_'.$value.'">'
            .LengowMain::decodeLogMessage('order.screen.status_'.$value).'</span>';
    }

    public static function displayOrderLink($key, $value, $item)
    {
        // This line is useless, but Prestashop validator require it
        $key = $key;
        $toolbox = Context::getContext()->smarty->getVariable('toolbox')->value;
        $link = new LengowLink();
        if ($item['id_order']) {
            if (!$toolbox) {
                return '<a href="'.$link->getAbsoluteAdminLink('AdminOrders', false, true).'&vieworder&id_order='.
                $item['id_order'].'" target="_blank">'.$value.'</a>';
            } else {
                return $value;
            }
        } else {
            return $value;
        }
    }

    public static function displayMarketplaceName($key, $value, $item)
    {
        // This two lines are useless, but Prestashop validator require it
        $key = $key;
        $value = $value;
        return $item['marketplace_label'];
    }

    public static function displayLogStatus($key, $value, $item)
    {
        //check if order actions in progress
        if ($item['id_order'] > 0) {
            $actions = LengowAction::getOrderActiveAction($item['id_order'], 'ship');
            if ($actions) {
                $value = '<span class="lengow_link_tooltip lgw-label lgw-label_orange"
                    data-html="true"
                    data-original-title="'.LengowMain::decodeLogMessage('order.screen.action_waiting_return').'"
                    >'.LengowMain::decodeLogMessage('order.screen.action_sent').'</span>';
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
            $message = '<ul>'.join('', $errorMessage).'</ul>';
            if ($item[$key] == '2') {
                $message = LengowMain::decodeLogMessage('order.screen.action_sent_not_work')
                    .'<br/><br/>'.join('<br/>', $errorMessage);
                $value = '<a href="#"
                    class="lengow_re_send lengow_link_tooltip lgw-label lgw-label_red"
                    data-href="'.$link->getAbsoluteAdminLink('AdminLengowOrder', true).'"
                    data-action="re_send"
                    data-order="'.$item['id'].'"
                    data-type="'.$item[$key].'"
                    data-html="true"
                    data-original-title="'.$message.'"
                    >'.LengowMain::decodeLogMessage('order.screen.not_sent').'<i class="fa fa-refresh"></i></a>';
            } else {
                $message = LengowMain::decodeLogMessage('order.screen.order_not_imported')
                    .'<br/><br/>'.join('<br/>', $errorMessage);
                $value = '<a href="#"
                    class="lengow_re_import lengow_link_tooltip lgw-label lgw-label_red"
                    data-href="'.$link->getAbsoluteAdminLink('AdminLengowOrder', true).'"
                    data-action="re_import"
                    data-order="'.$item['id'].'"
                    data-type="'.$item[$key].'"
                    data-html="true"
                    data-original-title="'.$message.'"
                    >'.LengowMain::decodeLogMessage('order.screen.not_imported').'<i class="fa fa-refresh"></i></a>';
            }
        } else {
            $value = '<span class="lgw-label lgw-label_green">ok</span>';
        }
        return $value;
    }

    public static function displayLengowExtra($key, $value, $item)
    {
        // This line is useless, but Prestashop validator require it
        $key = $key;
        if (!empty($value)) {
            $value = htmlentities($value);
            return '<input id="link_extra_'.$item['id'].'" value="'.$value.'" readonly>
                <a class="lengow_copy lengow_link_tooltip" data-clipboard-target="#link_extra_'.$item['id'].'"
                data-original-title="'.LengowMain::decodeLogMessage('product.screen.button_copy').'">
                <i class="fa fa-download"></i></a>';
        }
        return '';
    }

    public function loadMessage($return)
    {
        $message = array();
        if (isset($return['order_new'])) {
            $message[]= $this->locale->t('lengow_log.error.nb_order_imported', array(
                'nb_order' => (int)$return['order_new']
            ));
        }
        if (isset($return['order_update'])) {
            $message[]= $this->locale->t('lengow_log.error.nb_order_updated', array(
                'nb_order' => (int)$return['order_update']
            ));
        }
        if (isset($return['order_error'])) {
            $message[]= $this->locale->t('lengow_log.error.nb_order_with_error', array(
                'nb_order' => (int)$return['order_error']
            ));
        }
        if (isset($return['error'])) {
            foreach ($return['error'] as $shop => $values) {
                if ((int)$shop > 0) {
                    $shop = new LengowShop($shop);
                    $shopName = $shop->name.' : ';
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
            $message[] = $this->locale->t('lengow_log.error.rest_time_to_import', array(
                'rest_time' => LengowImport::restTimeToImport()
            ));
        }

        return $message;
    }
}
