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
 * Lengow Order Controller Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
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
                    $data = [];
                    $data['order_table'] = preg_replace('/\r|\n/', '', $this->buildTable());
                    echo json_encode($data);
                    break;
                case 're_import':
                    $idOrderLengow = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
                    LengowOrder::reImportOrder($idOrderLengow);
                    $list = $this->loadTable();
                    $row = $list->getRow(' id = ' . $idOrderLengow);
                    $html = $list->displayRow($row);
                    $html = preg_replace('/\r|\n/', '', $html);
                    $data = [];
                    $data['id_order_lengow'] = $idOrderLengow;
                    $data['html'] = $html;
                    echo json_encode($data);
                    break;
                case 're_send':
                    $idOrderLengow = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
                    LengowOrder::reSendOrder($idOrderLengow);
                    $list = $this->loadTable();
                    $row = $list->getRow(' id = ' . $idOrderLengow);
                    $html = $list->displayRow($row);
                    $html = preg_replace('/\r|\n/', '', $html);
                    $data = [];
                    $data['id_order_lengow'] = $idOrderLengow;
                    $data['html'] = $html;
                    echo json_encode($data);
                    break;
                case 'import_all':
                    if (Shop::getContextShopID()) {
                        $import = new LengowImport(
                            [
                                LengowImport::PARAM_SHOP_ID => Shop::getContextShopID(),
                                LengowImport::PARAM_LOG_OUTPUT => false,
                            ]
                        );
                    } else {
                        $import = new LengowImport([LengowImport::PARAM_LOG_OUTPUT => false]);
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
                    $data = [];
                    $data['message'] = '<div class=\"lengow_alert\">' . join('<br/>', $message) . '</div>';
                    $data['warning_message'] = preg_replace('/\r|\n/', '', $displayWarningMessage);
                    $data['last_importation'] = preg_replace('/\r|\n/', '', $displayLastImportation);
                    $data['import_orders'] = $this->locale->t('order.screen.button_update_orders');
                    $data['list_order'] = preg_replace('/\r|\n/', '', $displayListOrder);
                    $data['show_carrier_notification'] = LengowCarrier::hasDefaultCarrierNotMatched();
                    echo json_encode($data);
                    break;
                case 'synchronize':
                    $idOrder = isset($_REQUEST['id_order']) ? (int) $_REQUEST['id_order'] : 0;
                    $lengowOrder = new LengowOrder($idOrder);
                    $synchro = $lengowOrder->synchronizeOrder();
                    if ($synchro) {
                        $synchroMessage = LengowMain::setLogMessage(
                            'log.import.order_synchronized_with_lengow',
                            ['order_id' => $idOrder]
                        );
                    } else {
                        $synchroMessage = LengowMain::setLogMessage(
                            'log.import.order_not_synchronized_with_lengow',
                            ['order_id' => $idOrder]
                        );
                    }
                    LengowMain::log(LengowLog::CODE_IMPORT, $synchroMessage, false, $lengowOrder->lengowMarketplaceSku);
                    Tools::redirectAdmin(self::getOrderAdminLink($idOrder));
                    break;
                case 'cancel_re_import':
                    $idOrder = isset($_REQUEST['id_order']) ? (int) $_REQUEST['id_order'] : 0;
                    $lengowOrder = new LengowOrder($idOrder);
                    $newIdOrder = $lengowOrder->cancelAndreImportOrder();
                    if (!$newIdOrder) {
                        $newIdOrder = $idOrder;
                    }
                    Tools::redirectAdmin(self::getOrderAdminLink($newIdOrder));
                    break;
                case 'save_shipping_method':
                    $idOrder = (int) Tools::getValue('id_order');
                    $shippingMethod = Tools::getValue('method');
                    $response = ['success' => false, 'message' => ''];

                    if (!$idOrder || !$shippingMethod) {
                        $response['message'] = 'Missing parameters';
                    } else {
                        try {
                            $result = Db::getInstance()->update(
                                'lengow_orders',
                                ['method' => pSQL($shippingMethod)],
                                'id_order = ' . $idOrder
                            );

                            if ($result) {
                                $response = [
                                    'success' => true,
                                    'message' => 'Delivery method successfully updated',
                                ];
                                LengowMain::log(
                                    LengowLog::CODE_IMPORT,
                                    'Updated shipping method : ' . $shippingMethod,
                                    false,
                                    $idOrder
                                );
                            } else {
                                $response['message'] = 'No changes or errors';
                            }
                        } catch (Exception $e) {
                            $response['message'] = 'Error: ' . $e->getMessage();
                        }
                    }
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    break;
                case 'force_resend':
                    $idOrder = isset($_REQUEST['id_order']) ? (int) $_REQUEST['id_order'] : 0;
                    $actionType = isset($_REQUEST['action_type']) ? $_REQUEST['action_type'] : LengowAction::TYPE_SHIP;
                    $lengowOrder = new LengowOrder($idOrder);
                    $lengowOrder->callAction($actionType);
                    Tools::redirectAdmin(self::getOrderAdminLink($idOrder));
                    break;
            }
            exit;
        }
    }

    /**
     * Get all warning messages
     */
    public function assignWarningMessages()
    {
        $warningMessages = [];
        if (LengowConfiguration::debugModeIsActive()) {
            $warningMessages[] = $this->locale->t(
                'order.screen.debug_warning_message',
                ['url' => $this->lengowLink->getAbsoluteAdminLink('AdminLengowMainSetting')]
            );
        }
        if (LengowCarrier::hasDefaultCarrierNotMatched()) {
            $warningMessages[] = $this->locale->t(
                'order.screen.no_carrier_warning_message',
                ['url' => $this->lengowLink->getAbsoluteAdminLink('AdminLengowOrderSetting')]
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
        $orderCollection = [
            'last_import_date' => $lastImport['timestamp'] !== 'none'
                ? LengowMain::getDateInCorrectFormat($lastImport['timestamp'])
                : '',
            'last_import_type' => $lastImport['type'],
            'link' => LengowMain::getCronUrl(),
        ];
        $reportMailEnabled = (bool) LengowConfiguration::getGlobalValue(LengowConfiguration::REPORT_MAIL_ENABLED);
        $this->context->smarty->assign('reportMailEnabled', $reportMailEnabled);
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
            $nbOrderImported = (int) $totalOrders[0]['total'];
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
        $fieldsList = [];
        $fieldsList['log_status'] = [
            'title' => $this->locale->t('order.table.action_lengow'),
            'class' => 'lengow_status no-link nowrap',
            'type' => 'log_status',
            'display_callback' => 'LengowOrderController::displayLogStatus',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'log_status',
            'filter_type' => 'select',
            'filter_collection' => [
                ['id' => 1, 'text' => $this->locale->t('order.screen.action_success')],
                ['id' => 2, 'text' => $this->locale->t('order.screen.action_error')],
            ],
        ];
        $fieldsList['lengow_status'] = [
            'title' => $this->locale->t('order.table.order_lengow_state'),
            'class' => 'text-center link  no-link',
            'display_callback' => 'LengowOrderController::displayLengowState',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'lo.order_lengow_state',
            'filter_type' => 'select',
            'filter_collection' => [
                [
                    'id' => LengowOrder::STATE_ACCEPTED,
                    'text' => $this->locale->t('order.screen.status_accepted'),
                ],
                [
                    'id' => LengowOrder::STATE_WAITING_SHIPMENT,
                    'text' => $this->locale->t('order.screen.status_waiting_shipment'),
                ],
                [
                    'id' => LengowOrder::STATE_SHIPPED,
                    'text' => $this->locale->t('order.screen.status_shipped'),
                ],
                [
                    'id' => LengowOrder::STATE_REFUNDED,
                    'text' => $this->locale->t('order.screen.status_refunded'),
                ],
                [
                    'id' => LengowOrder::STATE_PARTIALLY_REFUNDED,
                    'text' => $this->locale->t('order.screen.status_partially_refunded'),
                ],
                [
                    'id' => LengowOrder::STATE_CLOSED,
                    'text' => $this->locale->t('order.screen.status_closed'),
                ],
                [
                    'id' => LengowOrder::STATE_CANCELED,
                    'text' => $this->locale->t('order.screen.status_canceled'),
                ],
            ],
        ];
        $fieldsList[LengowOrder::FIELD_ORDER_TYPES] = [
            'title' => $this->locale->t('order.table.order_types'),
            'class' => 'text-center link no-link nowrap',
            'type' => 'order_types',
            'display_callback' => 'LengowOrderController::displayOrderTypes',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'lo.order_types',
            'filter_type' => 'select',
            'filter_collection' => [
                [
                    'id' => LengowOrder::TYPE_EXPRESS,
                    'text' => $this->locale->t('order.screen.type_express'),
                ],
                [
                    'id' => LengowOrder::TYPE_DELIVERED_BY_MARKETPLACE,
                    'text' => $this->locale->t('order.screen.type_delivered_by_marketplace'),
                ],
                [
                    'id' => LengowOrder::TYPE_BUSINESS,
                    'text' => $this->locale->t('order.screen.type_business'),
                ],
            ],
        ];
        $fieldsList[LengowOrder::FIELD_MARKETPLACE_SKU] = [
            'title' => $this->locale->t('order.table.marketplace_sku'),
            'class' => 'link no-link',
            'display_callback' => 'LengowOrderController::displayOrderLink',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'lo.marketplace_sku',
        ];
        $fieldsList[LengowOrder::FIELD_MARKETPLACE_NAME] = [
            'title' => $this->locale->t('order.table.marketplace_name'),
            'class' => 'link nowrap',
            'display_callback' => 'LengowOrderController::displayMarketplaceName',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'lo.marketplace_name',
            'filter_type' => 'select',
            'filter_collection' => $this->getMarketplaces(),
        ];
        if (Shop::isFeatureActive() && !Shop::getContextShopID()) {
            $fieldsList['shop_name'] = [
                'class' => 'link shop',
                'title' => $this->locale->t('order.table.shop_name'),
                'filter' => true,
                'filter_order' => true,
                'filter_key' => 'shop.id_shop',
                'filter_type' => 'select',
                'filter_collection' => $this->getShops(),
            ];
        }
        $fieldsList['reference'] = [
            'title' => $this->locale->t('order.table.reference_prestashop'),
            'class' => 'link reference no-link',
            'display_callback' => 'LengowOrderController::displayOrderLink',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'o.reference',
        ];
        $fieldsList[LengowOrder::FIELD_CUSTOMER_NAME] = [
            'title' => $this->locale->t('order.table.customer'),
            'class' => 'link no-link',
            'filter' => true,
            'filter_order' => true,
            'filter_key' => 'lo.customer_name',
        ];
        $fieldsList[LengowOrder::FIELD_ORDER_DATE] = [
            'title' => $this->locale->t('order.table.order_date'),
            'class' => 'link',
            'type' => 'date',
            'filter' => true,
            'filter_type' => 'date',
            'filter_key' => 'lo.order_date',
            'filter_order' => true,
        ];
        $fieldsList[LengowOrder::FIELD_DELIVERY_COUNTRY_ISO] = [
            'title' => $this->locale->t('order.table.delivery_country'),
            'class' => 'link',
            'type' => 'flag_country',
            'filter_key' => 'lo.delivery_country_iso',
            'filter_order' => true,
        ];
        $fieldsList[LengowOrder::FIELD_TOTAL_PAID] = [
            'title' => $this->locale->t('order.table.total_paid'),
            'type' => 'price',
            'class' => 'link nowrap',
            'filter_key' => 'lo.total_paid',
            'filter_order' => true,
        ];
        $select = [
            'lo.id',
            'lo.marketplace_sku',
            'lo.marketplace_name',
            'IFNULL(lo.marketplace_label,lo.marketplace_name) as marketplace_label',
            'lo.total_paid',
            'lo.extra',
            'lo.delivery_country_iso',
            'lo.sent_marketplace',
            'lo.order_process_state',
            'lo.order_types',
            'lo.customer_name',
            'o.reference',
            'lo.order_date',
            'lo.order_lengow_state as lengow_status',
            'lo.id_order',
            'lo.currency',
            "'' as search",
        ];
        $selectHaving = [
            ' (SELECT IFNULL(lli.type, 0) FROM ' . _DB_PREFIX_ . 'lengow_logs_import lli
            WHERE lli.id_order_lengow = lo.id AND lli.is_finished = 0 LIMIT 1) as log_status',
        ];
        $from = 'FROM ' . _DB_PREFIX_ . 'lengow_orders lo';
        $join = [];
        $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.id_order = lo.id_order) ';
        if (Shop::getContextShopID()) {
            $join[] = 'INNER JOIN `' . _DB_PREFIX_ . 'shop` shop ON (lo.id_shop = shop.id_shop
                AND shop.id_shop = ' . (int) Shop::getContextShopID() . ') ';
        } else {
            $join[] = 'LEFT JOIN `' . _DB_PREFIX_ . 'shop` shop ON (lo.id_shop = shop.id_shop) ';
        }
        $select[] = 'shop.name as shop_name';
        $currentPage = isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
        $orderValue = isset($_REQUEST['order_value']) ? $_REQUEST['order_value'] : '';
        $orderColumn = isset($_REQUEST['order_column']) ? $_REQUEST['order_column'] : '';
        $nbPerPage = isset($_REQUEST['nb_per_page']) ? $_REQUEST['nb_per_page'] : '';

        return new LengowList(
            [
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
                'sql' => [
                    'select' => $select,
                    'from' => $from,
                    'join' => $join,
                    'select_having' => $selectHaving,
                    'order' => 'IF (order_lengow_state = "waiting_shipment",1,0) DESC, order_date DESC',
                ],
            ]
        );
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
        $paginationBlock = $this->list->renderPagination(['nav_class' => 'lgw-pagination']);
        $html = '<div class="lengow_table_top">';
        $html .= '<div class="lengow_toolbar">';
        $html .= '<a href="#" style="display:none;"
            data-href="' . $this->lengowLink->getAbsoluteAdminLink('AdminLengowOrder') . '"
            class="lgw-btn lengow_link_tooltip lengow_mass_re_import btn btn-primary">
            <i class="fa fa-download"></i> ' . $this->locale->t('order.screen.button_reimport_order') . '</a>';
        $html .= '<a href="#" style="display:none;"
            data-href="' . $this->lengowLink->getAbsoluteAdminLink('AdminLengowOrder') . '"
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
        $marketplaces = [];
        $sql = 'SELECT DISTINCT(marketplace_name) as name,
            IFNULL(marketplace_label, marketplace_name) as marketplace_label
            FROM `' . _DB_PREFIX_ . 'lengow_orders`';
        try {
            $collection = Db::getInstance()->executeS($sql);
        } catch (PrestaShopDatabaseException $e) {
            $collection = [];
        }
        foreach ($collection as $row) {
            $marketplaces[] = [
                'id' => $row['name'],
                'text' => $row['marketplace_label'],
            ];
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
        $shops = [];
        $sql = 'SELECT id_shop, name FROM ' . _DB_PREFIX_ . 'shop WHERE active = 1';
        try {
            $collection = Db::getInstance()->ExecuteS($sql);
        } catch (PrestaShopDatabaseException $e) {
            $collection = [];
        }
        foreach ($collection as $row) {
            $shops[] = ['id' => $row['id_shop'], 'text' => $row['name']];
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
        // this two lines are useless, but PrestaShop validator require it
        $key = $key;
        $item = $item;
        if (empty($value)) {
            $value = 'not_synchronized';
        }

        return '<span class="lgw-label lgw-order-status lgw-label-' . $value . '">'
            . LengowMain::decodeLogMessage('order.screen.status_' . $value) . '</span>';
    }

    /**
     * Generate order types
     *
     * @param string $key row key
     * @param string $value row value
     * @param array $item item values
     *
     * @return string
     */
    public static function displayOrderTypes($key, $value, $item)
    {
        $return = '<div>';
        $orderTypes = $value !== null ? json_decode($value, true) : [];
        if (isset($orderTypes[LengowOrder::TYPE_EXPRESS]) || isset($orderTypes[LengowOrder::TYPE_PRIME])) {
            $iconLabel = isset($orderTypes[LengowOrder::TYPE_PRIME])
                ? $orderTypes[LengowOrder::TYPE_PRIME]
                : $orderTypes[LengowOrder::TYPE_EXPRESS];
            $return .= self::generateOrderTypeIcon($iconLabel, 'orange-light', 'mod-chrono');
        }
        if (isset($orderTypes[LengowOrder::TYPE_DELIVERED_BY_MARKETPLACE])
            || ($key === LengowOrder::FIELD_ORDER_TYPES && (bool) $item[LengowOrder::FIELD_SENT_MARKETPLACE])
        ) {
            $iconLabel = isset($orderTypes[LengowOrder::TYPE_DELIVERED_BY_MARKETPLACE])
                ? $orderTypes[LengowOrder::TYPE_DELIVERED_BY_MARKETPLACE]
                : LengowOrder::LABEL_FULFILLMENT;
            $return .= self::generateOrderTypeIcon($iconLabel, 'green-light', 'mod-delivery');
        }
        if (isset($orderTypes[LengowOrder::TYPE_BUSINESS])) {
            $return .= self::generateOrderTypeIcon($orderTypes[LengowOrder::TYPE_BUSINESS], 'blue-light', 'mod-pro');
        }
        $return .= '</div>';

        return $return;
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
        // this line is useless, but PrestaShop validator require it
        $key = $key;
        if ($item[LengowOrder::FIELD_ORDER_ID]) {
            $href = self::getOrderAdminLink($item[LengowOrder::FIELD_ORDER_ID]);

            return '<a href="' . $href . '" target="_blank">' . $value . '</a>';
        }

        return $value;
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
        // this line is useless, but PrestaShop validator require it
        $key = $key;
        $value = $value;

        return $item[LengowOrder::FIELD_MARKETPLACE_LABEL];
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
        if ($item[$key] && (int) $item[LengowOrder::FIELD_ORDER_PROCESS_STATE] !== LengowOrder::PROCESS_STATE_FINISH) {
            $errorMessages = [];
            $logCollection = LengowOrderError::getOrderLogs($item[LengowOrder::FIELD_ID], null, false);
            if (!empty($logCollection)) {
                foreach ($logCollection as $row) {
                    if ($row[LengowOrderError::FIELD_MESSAGE] !== '') {
                        $errorMessages[] = LengowMain::cleanData(
                            LengowMain::decodeLogMessage($row[LengowOrderError::FIELD_MESSAGE])
                        );
                    } else {
                        $errorMessages[] = LengowMain::decodeLogMessage('order.screen.no_error_message');
                    }
                }
            }
            $link = new LengowLink();
            if ((int) $item[$key] === 2) {
                $message = LengowMain::decodeLogMessage('order.screen.action_sent_not_work')
                    . '<br/>' . join('<br/>', $errorMessages);
                $value = '<a href="#"
                    class="lengow_re_send lengow_link_tooltip lgw-btn lgw-btn-white"
                    data-href="' . $link->getAbsoluteAdminLink('AdminLengowOrder') . '"
                    data-action="re_send"
                    data-order="' . $item[LengowOrder::FIELD_ID] . '"
                    data-type="' . $item[$key] . '"
                    data-html="true"
                    data-original-title="' . $message . '"
                    >' . LengowMain::decodeLogMessage('order.screen.not_sent') . ' <i class="fa fa-refresh"></i></a>';
            } else {
                $message = LengowMain::decodeLogMessage('order.screen.order_not_imported')
                    . '<br/>' . join('<br/>', $errorMessages);
                $value = '<a href="#"
                    class="lengow_re_import lengow_link_tooltip lgw-btn lgw-btn-white"
                    data-href="' . $link->getAbsoluteAdminLink('AdminLengowOrder') . '"
                    data-action="re_import"
                    data-order="' . $item[LengowOrder::FIELD_ID] . '"
                    data-type="' . $item[$key] . '"
                    data-html="true"
                    data-original-title="' . $message . '">'
                    . LengowMain::decodeLogMessage('order.screen.not_imported') . ' <i class="fa fa-refresh"></i></a>';
            }
        } else {
            // check if order actions in progress
            if (($item[LengowOrder::FIELD_ORDER_ID] > 0
                    && (int) $item[LengowOrder::FIELD_ORDER_PROCESS_STATE]
                    === LengowOrder::PROCESS_STATE_IMPORT) || LengowOrder::PROCESS_STATE_FINISH
            ) {
                $lastActionType = LengowAction::getLastOrderActionType($item[LengowOrder::FIELD_ORDER_ID]);
                if ($lastActionType) {
                    $messageLastAction = LengowMain::decodeLogMessage(
                        'order.screen.action_sent',
                        null,
                        ['action_type' => $lastActionType]
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
     * Generate message array (new, update and errors)
     *
     * @param array $return
     *
     * @return array
     */
    public function loadMessage($return)
    {
        $messages = [];
        // if global error return this
        if (isset($return[LengowImport::ERRORS][0])) {
            $messages[] = LengowMain::decodeLogMessage($return[LengowImport::ERRORS][0]);

            return $messages;
        }
        if (isset($return[LengowImport::NUMBER_ORDERS_CREATED]) && $return[LengowImport::NUMBER_ORDERS_CREATED] > 0) {
            $messages[] = $this->locale->t(
                'lengow_log.error.nb_order_imported',
                ['nb_order' => (int) $return[LengowImport::NUMBER_ORDERS_CREATED]]
            );
        }
        if (isset($return[LengowImport::NUMBER_ORDERS_UPDATED]) && $return[LengowImport::NUMBER_ORDERS_UPDATED] > 0) {
            $messages[] = $this->locale->t(
                'lengow_log.error.nb_order_updated',
                ['nb_order' => (int) $return[LengowImport::NUMBER_ORDERS_UPDATED]]
            );
        }
        if (isset($return[LengowImport::NUMBER_ORDERS_FAILED]) && $return[LengowImport::NUMBER_ORDERS_FAILED] > 0) {
            $messages[] = $this->locale->t(
                'lengow_log.error.nb_order_with_error',
                ['nb_order' => (int) $return[LengowImport::NUMBER_ORDERS_FAILED]]
            );
        }
        if (empty($messages)) {
            $messages[] = $this->locale->t('lengow_log.error.no_notification');
        }
        if (isset($return[LengowImport::ERRORS])) {
            foreach ($return[LengowImport::ERRORS] as $shop => $values) {
                if ((int) $shop > 0) {
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

    /**
     * Generate order type icon
     *
     * @param string $iconLabel icon label for tooltip
     * @param string $iconColor icon background color
     * @param string $iconMod icon mod for image
     *
     * @return string
     */
    public static function generateOrderTypeIcon($iconLabel, $iconColor, $iconMod)
    {
        return '
            <div class="lgw-label ' . $iconColor . ' icon-solo lengow_link_tooltip"
                 data-original-title="' . $iconLabel . '">
                <span class="lgw-icon ' . $iconMod . '"></span>
            </div>
        ';
    }

    /**
     * Generate link for order admin page
     *
     * @param int $idOrder PrestaShop order id
     *
     * @return string
     */
    private static function getOrderAdminLink($idOrder)
    {
        $link = new LengowLink();
        try {
            if (version_compare(_PS_VERSION_, '1.7.7', '<')) {
                $href = $link->getAbsoluteAdminLink('AdminOrders')
                    . '&vieworder&id_order=' . $idOrder;
            } else {
                $sfParams = [
                    'orderId' => $idOrder,
                ];
                $href = Link::getUrlSmarty(
                    [
                        'entity' => 'sf',
                        'route' => 'admin_orders_view',
                        'sf-params' => $sfParams,
                    ]
                );
            }
        } catch (PrestaShopException $e) {
            $href = '#';
        }

        return $href;
    }
}
