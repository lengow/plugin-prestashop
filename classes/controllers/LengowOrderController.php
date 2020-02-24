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
 * Lengow Order Controller Class
 */
class LengowOrderController extends LengowController
{
    /**
     * @var LengowList Lengow list instance
     */
    protected $list;

    /**
     * Display data page
     */
    public function display()
    {
        $this->assignLastImportationInfos();
        $this->assignNbOrderImported();
        $this->assignWarningMessages();
        // datas for toolbox
        $shop = array();
        $shops = LengowShop::findAll();
        foreach ($shops as $s) {
            $shop[$s['id_shop']] = new LengowShop($s['id_shop']);
        }
        $days = LengowConfiguration::get('LENGOW_IMPORT_DAYS');
        $this->context->smarty->assign('shop', $shop);
        $this->context->smarty->assign('days', $days);
        $this->context->smarty->assign('showCarrierNotification', LengowCarrier::hasDefaultCarrierNotMatched());
        $this->context->smarty->assign('lengow_table', $this->buildTable());
        parent::display();
    }

    /**
     * Process Post Parameters
     */
    public function postProcess()
    {
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
        if ($action) {
            switch ($action) {
                case 'load_table':
                    $data = array();
                    $data['order_table'] = preg_replace('/\r|\n/', '', $this->buildTable());
                    echo Tools::jsonEncode($data);
                    break;
                case 're_import':
                    $idOrderLengow = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
                    LengowOrder::reImportOrder($idOrderLengow);
                    $list = $this->loadTable();
                    $row = $list->getRow(' id = ' . (int)$idOrderLengow);
                    $html = $list->displayRow($row);
                    $html = preg_replace('/\r|\n/', '', $html);
                    $data = array();
                    $data['id_order_lengow'] = $idOrderLengow;
                    $data['html'] = $html;
                    echo Tools::jsonEncode($data);
                    break;
                case 're_send':
                    $idOrderLengow = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
                    LengowOrder::reSendOrder($idOrderLengow);
                    $list = $this->loadTable();
                    $row = $list->getRow(' id = ' . (int)$idOrderLengow);
                    $html = $list->displayRow($row);
                    $html = preg_replace('/\r|\n/', '', $html);
                    $data = array();
                    $data['id_order_lengow'] = $idOrderLengow;
                    $data['html'] = $html;
                    echo Tools::jsonEncode($data);
                    break;
                case 'import_all':
                    if (_PS_VERSION_ < '1.5') {
                        $import = new LengowImport(array('log_output' => false));
                    } else {
                        if (Shop::getContextShopID()) {
                            $import = new LengowImport(
                                array(
                                    'shop_id' => Shop::getContextShopID(),
                                    'log_output' => false,
                                )
                            );
                        } else {
                            $import = new LengowImport(array('log_output' => false));
                        }
                    }
                    $return = $import->exec();
                    $message = $this->loadMessage($return);
                    $this->assignLastImportationInfos();
                    $this->assignWarningMessages();
                    $module = Module::getInstanceByName('lengow');
                    $displayWarningMessage = $module->display(
                        _PS_MODULE_LENGOW_DIR_,
                        'views/templates/admin/lengow_order/helpers/view/warning_message.tpl'
                    );
                    $displayLastImportation = $module->display(
                        _PS_MODULE_LENGOW_DIR_,
                        'views/templates/admin/lengow_order/helpers/view/last_importation.tpl'
                    );
                    $orderTable = $this->buildTable();
                    if ($this->list->getTotal() > 0) {
                        $displayListOrder = $orderTable;
                    } else {
                        $displayListOrder = $module->display(
                            _PS_MODULE_LENGOW_DIR_,
                            'views/templates/admin/lengow_order/helpers/view/no_order.tpl'
                        );
                    }
                    $data = array();
                    $data['message'] = '<div class=\"lengow_alert\">' . join('<br/>', $message) . '</div>';
                    $data['warning_message'] = preg_replace('/\r|\n/', '', $displayWarningMessage);
                    $data['last_importation'] = preg_replace('/\r|\n/', '', $displayLastImportation);
                    $data['import_orders'] = $this->locale->t('order.screen.button_update_orders');
                    $data['list_order'] = preg_replace('/\r|\n/', '', $displayListOrder);
                    $data['show_carrier_notification'] = LengowCarrier::hasDefaultCarrierNotMatched();
                    echo Tools::jsonEncode($data);
                    break;
                case 'update_order':
                    $import = new LengowImport(
                        array(
                            'marketplace_sku' => Tools::getValue('marketplace_sku'),
                            'marketplace_name' => Tools::getValue('marketplace_name'),
                            'delivery_address_id' => Tools::getValue('delivery_address_id'),
                            'shop_id' => Tools::getValue('shop_id'),
                        )
                    );
                    $result = $import->exec();
                    $message = array();
                    if (isset($result['marketplace_sku']) && isset($result['marketplace_name'])) {
                        $message[] = LengowMain::decodeLogMessage(
                            'toolbox.order.order_import_success',
                            null,
                            array(
                                'marketplace_sku' => $result['marketplace_sku'],
                                'marketplace_name' => $result['marketplace_name'],
                            )
                        );
                    } else {
                        $message[] = LengowMain::decodeLogMessage(
                            'toolbox.order.order_import_failed',
                            null,
                            array('log_url' => '/modules/lengow/toolbox/log.php')
                        );
                    }
                    $data = array();
                    $data['message'] = '<div class=\"lengow_alert\">' . join('<br/>', $message) . '</div>';
                    $data['update_order'] = $this->locale->t('toolbox.order.import_one_order');
                    $data['order_table'] = preg_replace('/\r|\n/', '', $this->buildTable());
                    echo Tools::jsonEncode($data);
                    break;
                case 'update_some_orders':
                    $import = new LengowImport(
                        array(
                            'log_output' => false,
                            'days' => Tools::getValue('days'),
                            'shop_id' => Tools::getValue('shop_id'),
                        )
                    );
                    $return = $import->exec();
                    $message = $this->loadMessage($return);
                    $data = array();
                    $data['message'] = '<div class=\"lengow_alert\">' . addslashes(join('<br/>', $message)) . '</div>';
                    $data['update_some_orders'] = $this->locale->t('toolbox.order.button_import_shop_order');
                    $data['order_table'] = preg_replace('/\r|\n/', '', $this->buildTable());
                    echo Tools::jsonEncode($data);
                    break;
                case 'synchronize':
                    $idOrder = isset($_REQUEST['id_order']) ? (int)$_REQUEST['id_order'] : 0;
                    $lengowOrder = new LengowOrder($idOrder);
                    $synchro = $lengowOrder->synchronizeOrder();
                    if ($synchro) {
                        $synchroMessage = LengowMain::setLogMessage(
                            'log.import.order_synchronized_with_lengow',
                            array('order_id' => $idOrder)
                        );
                    } else {
                        $synchroMessage = LengowMain::setLogMessage(
                            'log.import.order_not_synchronized_with_lengow',
                            array('order_id' => $idOrder)
                        );
                    }
                    LengowMain::log(LengowLog::CODE_IMPORT, $synchroMessage, false, $lengowOrder->lengowMarketplaceSku);
                    $lengowLink = new LengowLink();
                    $prestashopOrderController = $lengowLink->getAbsoluteAdminLink('AdminOrders', false, true);
                    $orderUrl = $prestashopOrderController . '&id_order=' . $idOrder . '&vieworder';
                    Tools::redirectAdmin($orderUrl);
                    break;
                case 'cancel_re_import':
                    $idOrder = isset($_REQUEST['id_order']) ? (int)$_REQUEST['id_order'] : 0;
                    $lengowOrder = new LengowOrder($idOrder);
                    $newIdOrder = $lengowOrder->cancelAndreImportOrder();
                    if (!$newIdOrder) {
                        $newIdOrder = $idOrder;
                    }
                    $lengowLink = new LengowLink();
                    $prestashopOrderController = $lengowLink->getAbsoluteAdminLink('AdminOrders', false, true);
                    $orderUrl = $prestashopOrderController . '&id_order=' . $newIdOrder . '&vieworder';
                    Tools::redirectAdmin($orderUrl);
                    break;
                case 'force_resend':
                    $idOrder = isset($_REQUEST['id_order']) ? (int)$_REQUEST['id_order'] : 0;
                    $actionType = isset($_REQUEST['action_type']) ? $_REQUEST['action_type'] : LengowAction::TYPE_SHIP;
                    $lengowOrder = new LengowOrder($idOrder);
                    $lengowOrder->callAction($actionType);
                    $lengowLink = new LengowLink();
                    $prestashopOrderController = $lengowLink->getAbsoluteAdminLink('AdminOrders', false, true);
                    $orderUrl = $prestashopOrderController . '&id_order=' . $idOrder . '&vieworder';
                    Tools::redirectAdmin($orderUrl);
                    break;
                case 'add_tracking':
                    $idOrder = isset($_REQUEST['id_order']) ? (int)$_REQUEST['id_order'] : 0;
                    $trackingNumber = isset($_REQUEST['tracking_number']) ? $_REQUEST['tracking_number'] : '';
                    if ($trackingNumber !== '' && _PS_VERSION_ < '1.5' && $idOrder > 0) {
                        $order = new Order($idOrder);
                        $order->shipping_number = $trackingNumber;
                        $order->update();
                    }
                    $lengowLink = new LengowLink();
                    $prestashopOrderController = $lengowLink->getAbsoluteAdminLink('AdminOrders', false, true);
                    $orderUrl = $prestashopOrderController . '&id_order=' . $idOrder . '&vieworder';
                    Tools::redirectAdmin($orderUrl);
                    break;
            }
            exit();
        }
    }

    /**
     * Get all warning messages
     */
    public function assignWarningMessages()
    {
        $warningMessages = array();
        $lengowLink = new LengowLink();
        if (LengowConfiguration::get('LENGOW_IMPORT_SINGLE_ENABLED')) {
            $warningMessages[] = $this->locale->t('order.screen.import_single_warning_message');
        }
        if (LengowConfiguration::debugModeIsActive()) {
            $warningMessages[] = $this->locale->t(
                'order.screen.debug_warning_message',
                array('url' => $lengowLink->getAbsoluteAdminLink('AdminLengowMainSetting'))
            );
        }
        if (LengowCarrier::hasDefaultCarrierNotMatched()) {
            $warningMessages[] = $this->locale->t(
                'order.screen.no_carrier_warning_message',
                array('url' => $lengowLink->getAbsoluteAdminLink('AdminLengowOrderSetting'))
            );
        }
        if (!empty($warningMessages)) {
            $message = join('<br/>', $warningMessages);
        } else {
            $message = false;
        }
        $this->context->smarty->assign('warning_message', $message);
    }

    /**
     * Get all last importation data
     */
    public function assignLastImportationInfos()
    {
        $lastImport = LengowMain::getLastImport();
        $orderCollection = array(
            'last_import_date' => $lastImport['timestamp'] !== 'none'
                ? LengowMain::getDateInCorrectFormat($lastImport['timestamp'])
                : '',
            'last_import_type' => $lastImport['type'],
            'link' => LengowMain::getImportUrl(),
        );
        $this->context->smarty->assign('report_mail_address', LengowConfiguration::getReportEmailAddress());
        $this->context->smarty->assign('orderCollection', $orderCollection);
    }

    /**
     * Display data page
     */
    public function assignNbOrderImported()
    {
        $sql = 'SELECT COUNT(*) as `total` FROM `' . _DB_PREFIX_ . 'lengow_orders`';
        try {
            $totalOrders = Db::getInstance()->executeS($sql);
            $nbOrderImported = (int)$totalOrders[0]['total'];
        } catch (PrestaShopDatabaseException $e) {
            $nbOrderImported = 0;
        }
        $this->context->smarty->assign('nb_order_imported', $nbOrderImported);
    }

    /**
     * Load all order information
     *
     * @return LengowList
     */
    public function loadTable()
    {
        if (_PS_VERSION_ >= '1.5' && Shop::isFeatureActive() && !Shop::getContextShopID()) {
            $width = '10%';
        } else {
            $width = '12%';
        }
        $fieldsList = array();
        $fieldsList['log_status'] = array(
            'title' => $this->locale->t('order.table.action_lengow'),
            'class' => 'lengow_status no-link',
            'type' => 'log_status',
            'width' => $width,
            'display_callback' => 'LengowOrderController::displayLogStatus',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'log_status',
            'filter_type' => 'select',
            'filter_collection' => array(
                array('id' => 1, 'text' => $this->locale->t('order.screen.action_success')),
                array('id' => 2, 'text' => $this->locale->t('order.screen.action_error')),
            ),
        );
        $fieldsList['lengow_status'] = array(
            'title' => $this->locale->t('order.table.order_lengow_state'),
            'class' => 'text-center link  no-link',
            'width' => $width,
            'display_callback' => 'LengowOrderController::displayLengowState',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'lo.order_lengow_state',
            'filter_type' => 'select',
            'filter_collection' => array(
                array(
                    'id' => LengowOrder::STATE_ACCEPTED,
                    'text' => $this->locale->t('order.screen.status_accepted'),
                ),
                array(
                    'id' => LengowOrder::STATE_WAITING_SHIPMENT,
                    'text' => $this->locale->t('order.screen.status_waiting_shipment'),
                ),
                array(
                    'id' => LengowOrder::STATE_SHIPPED,
                    'text' => $this->locale->t('order.screen.status_shipped'),
                ),
                array(
                    'id' => LengowOrder::STATE_REFUNDED,
                    'text' => $this->locale->t('order.screen.status_refunded'),
                ),
                array(
                    'id' => LengowOrder::STATE_CLOSED,
                    'text' => $this->locale->t('order.screen.status_closed'),
                ),
                array(
                    'id' => LengowOrder::STATE_CANCELED,
                    'text' => $this->locale->t('order.screen.status_canceled'),
                ),
            ),
        );
        $fieldsList['marketplace_name'] = array(
            'title' => $this->locale->t('order.table.marketplace_name'),
            'class' => 'link',
            'width' => $width,
            'display_callback' => 'LengowOrderController::displayMarketplaceName',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'lo.marketplace_name',
            'filter_type' => 'select',
            'filter_collection' => $this->getMarketplaces(),
        );
        if (_PS_VERSION_ >= '1.5') {
            $fieldsList['shop_name'] = array(
                'class' => 'link shop',
                'width' => $width,
                'title' => $this->locale->t('order.table.shop_name'),
                'filter' => true,
                'filter_order' => true,
                'filter_key' => 'shop.id_shop',
                'filter_type' => 'select',
                'filter_collection' => $this->getShops(),
            );
        }
        $fieldsList['marketplace_sku'] = array(
            'title' => $this->locale->t('order.table.marketplace_sku'),
            'width' => '14%',
            'class' => 'link no-link',
            'display_callback' => 'LengowOrderController::displayOrderLink',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'lo.marketplace_sku',
        );
        $fieldsList['reference'] = array(
            'title' => $this->locale->t('order.table.reference_prestashop'),
            'class' => 'link reference no-link',
            'width' => $width,
            'display_callback' => 'LengowOrderController::displayOrderLink',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'o.reference',
        );
        $fieldsList['order_date'] = array(
            'title' => $this->locale->t('order.table.order_date'),
            'class' => 'link',
            'type' => 'date',
            'width' => $width,
            'filter' => true,
            'filter_type' => 'date',
            'filter_key' => 'lo.order_date',
            'filter_order' => true,
        );
        $fieldsList['delivery_country_iso'] = array(
            'title' => $this->locale->t('order.table.delivery_country'),
            'class' => 'link',
            'width' => '5%',
            'type' => 'flag_country',
            'filter_key' => 'lo.delivery_country_iso',
            'filter_order' => true,
        );
        $fieldsList['nb_item'] = array(
            'title' => $this->locale->t('order.table.order_item'),
            'width' => '5%',
            'class' => 'link text-right',
            'filter_key' => 'lo.order_item',
            'filter_order' => true,
        );
        $fieldsList['total_paid'] = array(
            'title' => $this->locale->t('order.table.total_paid'),
            'width' => '7%',
            'type' => 'price',
            'class' => 'link nowrap',
            'filter_key' => 'lo.total_paid',
            'filter_order' => true,
        );
        if ($this->toolbox) {
            $fieldsList['extra'] = array(
                'title' => $this->locale->t('order.table.extra'),
                'class' => 'no-link',
                'type' => 'text',
                'display_callback' => 'LengowOrderController::displayLengowExtra'
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
            'lo.sent_marketplace',
            'lo.order_process_state',
            'lo.order_item as nb_item',
            (_PS_VERSION_ < 1.5 ? 'o.id_order as reference' : 'o.reference'),
            'lo.order_date',
            'lo.order_lengow_state as lengow_status',
            'lo.id_order',
            'lo.currency',
            "'' as search",
        );
        $selectHaving = array(
            ' (SELECT IFNULL(lli.type, 0) FROM ' . _DB_PREFIX_ . 'lengow_logs_import lli
            WHERE lli.id_order_lengow = lo.id AND lli.is_finished = 0 LIMIT 1) as log_status',
        );
        $from = 'FROM ' . _DB_PREFIX_ . 'lengow_orders lo';
        $join = array();
        $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.id_order = lo.id_order) ';
        if (_PS_VERSION_ >= '1.5') {
            if (Shop::getContextShopID()) {
                $join[] = 'INNER JOIN `' . _DB_PREFIX_ . 'shop` shop ON (lo.id_shop = shop.id_shop
                    AND shop.id_shop = ' . (int)Shop::getContextShopID() . ') ';
            } else {
                $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'shop` shop ON (lo.id_shop = shop.id_shop) ';
            }
            $select[] = 'shop.name as shop_name';
        }
        $currentPage = isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
        $orderValue = isset($_REQUEST['order_value']) ? $_REQUEST['order_value'] : '';
        $orderColumn = isset($_REQUEST['order_column']) ? $_REQUEST['order_column'] : '';
        $nbPerPage = isset($_REQUEST['nb_per_page']) ? $_REQUEST['nb_per_page'] : '';
        $list = new LengowList(
            array(
                'id' => 'order',
                'fields_list' => $fieldsList,
                'identifier' => 'id',
                'selection' => true,
                'selection_condition' => 'log_status',
                'controller' => 'AdminLengowOrder',
                'current_page' => $currentPage,
                'order_value' => $orderValue,
                'order_column' => $orderColumn,
                'nb_per_page' => $nbPerPage,
                'ajax' => true,
                'sql' => array(
                    'select' => $select,
                    'from' => $from,
                    'join' => $join,
                    'select_having' => $selectHaving,
                    'order' => 'IF (order_lengow_state = "waiting_shipment",1,0) DESC, order_date DESC',
                ),
            )
        );
        return $list;
    }

    /**
     * Build order grid
     *
     * @return string
     */
    public function buildTable()
    {
        $this->list = $this->loadTable();
        $this->list->executeQuery();
        $paginationBlock = $this->list->renderPagination(array('nav_class' => 'lgw-pagination'));
        $lengowLink = new LengowLink();
        $html = '<div class="lengow_table_top">';
        $html .= '<div class="lengow_toolbar">';
        $html .= '<a href="#" style="display:none;"
            data-href="' . $lengowLink->getAbsoluteAdminLink('AdminLengowOrder', true) . '"
            class="lgw-btn lengow_link_tooltip lengow_mass_re_import btn btn-primary">
            <i class="fa fa-download"></i> ' . $this->locale->t('order.screen.button_reimport_order') . '</a>';
        $html .= '<a href="#" style="display:none;"
            data-href="' . $lengowLink->getAbsoluteAdminLink('AdminLengowOrder', true) . '"
            class="lgw-btn lengow_link_tooltip lengow_mass_re_send btn btn-primary">
            <i class="fa fa-arrow-right"></i> ' . $this->locale->t('order.screen.button_resend_order') . '</a>';
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
     * Get Marketplace (name and label)
     *
     * @return array
     */
    public function getMarketplaces()
    {
        $marketplaces = array();
        $sql = 'SELECT DISTINCT(marketplace_name) as name,
            IFNULL(marketplace_label, marketplace_name) as marketplace_label
            FROM `' . _DB_PREFIX_ . 'lengow_orders`';
        try {
            $collection = Db::getInstance()->executeS($sql);
        } catch (PrestaShopDatabaseException $e) {
            $collection = array();
        }
        foreach ($collection as $row) {
            $marketplaces[] = array('id' => $row['name'], 'text' => $row['marketplace_label']);
        }
        return $marketplaces;
    }

    /**
     * Get shop (ID and name)
     *
     * @return array
     */
    public function getShops()
    {
        $shops = array();
        $sql = 'SELECT id_shop, name FROM ' . _DB_PREFIX_ . 'shop WHERE active = 1';
        try {
            $collection = Db::getInstance()->ExecuteS($sql);
        } catch (PrestaShopDatabaseException $e) {
            $collection = array();
        }
        foreach ($collection as $row) {
            $shops[] = array('id' => $row['id_shop'], 'text' => $row['name']);
        }
        return $shops;
    }

    /**
     * Generate lengow state
     *
     * @param string $key row key
     * @param string $value row value
     * @param array $item item values
     *
     * @return string
     */
    public static function displayLengowState($key, $value, $item)
    {
        // this two lines are useless, but Prestashop validator require it
        $key = $key;
        $item = $item;
        if (empty($value)) {
            $value = 'not_synchronized';
        }
        return '<span class="lgw-label lgw-label-' . $value . '">'
            . LengowMain::decodeLogMessage('order.screen.status_' . $value) . '</span>';
    }

    /**
     * Generate order link
     *
     * @param string $key row key
     * @param string $value row value
     * @param array $item item values
     *
     * @return string
     */
    public static function displayOrderLink($key, $value, $item)
    {
        // this line is useless, but Prestashop validator require it
        $key = $key;
        $toolbox = Context::getContext()->smarty->getVariable('toolbox')->value;
        $link = new LengowLink();
        if ($item['id_order']) {
            if (!$toolbox) {
                return '<a href="' . $link->getAbsoluteAdminLink('AdminOrders', false, true)
                    . '&vieworder&id_order=' . $item['id_order'] . '" target="_blank">' . $value . '</a>';
            } else {
                return $value;
            }
        } else {
            if ($key === 'reference' && (bool)$item['sent_marketplace']) {
                return '<span class="lgw-label">'
                    . LengowMain::decodeLogMessage('order.screen.status_shipped_by_mkp') . '</span>';
            }
            return $value;
        }
    }

    /**
     * Generate lengow marketplace name
     *
     * @param string $key row key
     * @param string $value row value
     * @param array $item item values
     *
     * @return string
     */
    public static function displayMarketplaceName($key, $value, $item)
    {
        // this two lines are useless, but Prestashop validator require it
        $key = $key;
        $value = $value;
        return $item['marketplace_label'];
    }

    /**
     * Generate logs and lengow action
     *
     * @param string $key row key
     * @param string $value row value
     * @param array $item item values
     *
     * @return string
     */
    public static function displayLogStatus($key, $value, $item)
    {
        if ($item[$key] && (int)$item['order_process_state'] !== LengowOrder::PROCESS_STATE_FINISH) {
            $errorMessages = array();
            $logCollection = LengowOrder::getOrderLogs($item['id'], null, false);
            if (!empty($logCollection)) {
                foreach ($logCollection as $row) {
                    if ($row['message'] !== '') {
                        $errorMessages[] = LengowMain::cleanData(LengowMain::decodeLogMessage($row['message']));
                    } else {
                        $errorMessages[] = LengowMain::decodeLogMessage('order.screen.no_error_message');
                    }
                }
            }
            $link = new LengowLink();
            if ((int)$item[$key] === 2) {
                $message = LengowMain::decodeLogMessage('order.screen.action_sent_not_work')
                    . '<br/>' . join('<br/>', $errorMessages);
                $value = '<a href="#"
                    class="lengow_re_send lengow_link_tooltip lgw-btn lgw-btn-white"
                    data-href="' . $link->getAbsoluteAdminLink('AdminLengowOrder', true) . '"
                    data-action="re_send"
                    data-order="' . $item['id'] . '"
                    data-type="' . $item[$key] . '"
                    data-html="true"
                    data-original-title="' . $message . '"
                    >' . LengowMain::decodeLogMessage('order.screen.not_sent') . ' <i class="fa fa-refresh"></i></a>';
            } else {
                $message = LengowMain::decodeLogMessage('order.screen.order_not_imported')
                    . '<br/>' . join('<br/>', $errorMessages);
                $value = '<a href="#"
                    class="lengow_re_import lengow_link_tooltip lgw-btn lgw-btn-white"
                    data-href="' . $link->getAbsoluteAdminLink('AdminLengowOrder', true) . '"
                    data-action="re_import"
                    data-order="' . $item['id'] . '"
                    data-type="' . $item[$key] . '"
                    data-html="true"
                    data-original-title="' . $message . '">'
                    . LengowMain::decodeLogMessage('order.screen.not_imported') . ' <i class="fa fa-refresh"></i></a>';
            }
        } else {
            // check if order actions in progress
            if ($item['id_order'] > 0 && (int)$item['order_process_state'] === LengowOrder::PROCESS_STATE_IMPORT) {
                $lastActionType = LengowAction::getLastOrderActionType($item['id_order']);
                if ($lastActionType) {
                    $messageLastAction = LengowMain::decodeLogMessage(
                        'order.screen.action_sent',
                        null,
                        array('action_type' => $lastActionType)
                    );
                    $value = '<span class="lengow_link_tooltip lgw-label orange"
                        data-html="true"
                        data-original-title="' . LengowMain::decodeLogMessage('order.screen.action_waiting_return')
                        . '">' . $messageLastAction . '</span>';
                }
            } else {
                $value = '';
            }
        }
        return $value;
    }

    /**
     * Generate extra data (only for toolbox)
     *
     * @param string $key row key
     * @param string $value row value
     * @param array $item item values
     *
     * @return string
     */
    public static function displayLengowExtra($key, $value, $item)
    {
        // this line is useless, but Prestashop validator require it
        $key = $key;
        if (!empty($value)) {
            $value = htmlentities($value);
            return '<input id="link_extra_' . $item['id'] . '" value="' . $value . '" readonly>
                <button href="#" class="lengow_copy lengow_link_tooltip" data-clipboard-target="#link_extra_'
                . $item['id'] . '"data-original-title="' . LengowMain::decodeLogMessage('product.screen.button_copy')
                . '"><i class="fa fa-download"></i></button>';
        }
        return '';
    }

    /**
     * Generate message array (new, update and errors)
     *
     * @param array $return
     *
     * @return array
     */
    public function loadMessage($return)
    {
        $messages = array();
        // if global error return this
        if (isset($return['error'][0])) {
            $messages[] = LengowMain::decodeLogMessage($return['error'][0]);
            return $messages;
        }
        if (isset($return['order_new']) && $return['order_new'] > 0) {
            $messages[] = $this->locale->t(
                'lengow_log.error.nb_order_imported',
                array('nb_order' => (int)$return['order_new'])
            );
        }
        if (isset($return['order_update']) && $return['order_update'] > 0) {
            $messages[] = $this->locale->t(
                'lengow_log.error.nb_order_updated',
                array('nb_order' => (int)$return['order_update'])
            );
        }
        if (isset($return['order_error']) && $return['order_error'] > 0) {
            $messages[] = $this->locale->t(
                'lengow_log.error.nb_order_with_error',
                array('nb_order' => (int)$return['order_error'])
            );
        }
        if (empty($messages)) {
            $messages[] = $this->locale->t('lengow_log.error.no_notification');
        }
        if (isset($return['error'])) {
            foreach ($return['error'] as $shop => $values) {
                if ((int)$shop > 0) {
                    $shop = new LengowShop($shop);
                    $shopName = $shop->name . ' : ';
                } else {
                    $shopName = '';
                }
                $messages[] = $shopName . LengowMain::decodeLogMessage($values);
            }
        }
        return $messages;
    }
}
