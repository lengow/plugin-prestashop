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

namespace PrestaShop\Module\Lengow\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Shop;
use Tools;
use Module;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Lengow Orders List Controller for PrestaShop 8+/9
 *
 * This controller handles the Lengow orders list page with complete
 * migration from Smarty to Twig templates for PrestaShop 8+/9 compatibility.
 */
class AdminOrdersController extends FrameworkBundleAdminController
{
    /**
     * Display orders list page
     *
     * @AdminSecurity("is_granted('read', 'AdminLengowOrder')")
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        $locale = new \LengowTranslation();
        $lengowLink = new \LengowLink();
        $module = Module::getInstanceByName('lengow');
        
        // Get order controller instance
        $lengowOrderController = new \LengowOrderController();
        
        // Get last import information
        $lastImport = \LengowMain::getLastImport();
        $orderCollection = [
            'last_import_date' => $lastImport['timestamp'] !== 'none'
                ? \LengowMain::getDateInCorrectFormat($lastImport['timestamp'])
                : '',
            'last_import_type' => $lastImport['type'],
            'link' => \LengowMain::getCronUrl(),
        ];
        
        // Get report mail settings
        $reportMailEnabled = (bool) \LengowConfiguration::getGlobalValue(\LengowConfiguration::REPORT_MAIL_ENABLED);
        $reportMailAddress = \LengowConfiguration::getReportEmailAddress();
        
        // Get number of imported orders
        $sql = 'SELECT COUNT(*) as `total` FROM `' . _DB_PREFIX_ . 'lengow_orders`';
        try {
            $totalOrders = \Db::getInstance()->executeS($sql);
            $nbOrderImported = (int) $totalOrders[0]['total'];
        } catch (\PrestaShopDatabaseException $e) {
            $nbOrderImported = 0;
        }
        
        // Get warning messages
        $warningMessages = [];
        if (\LengowConfiguration::debugModeIsActive()) {
            $warningMessages[] = $locale->t(
                'order.screen.debug_warning_message',
                ['url' => $lengowLink->getAbsoluteAdminLink('AdminLengowMainSetting')]
            );
        }
        if (\LengowCarrier::hasDefaultCarrierNotMatched()) {
            $warningMessages[] = $locale->t(
                'order.screen.no_carrier_warning_message',
                ['url' => $lengowLink->getAbsoluteAdminLink('AdminLengowOrderSetting')]
            );
        }
        $warningMessage = !empty($warningMessages) ? join('<br/>', $warningMessages) : false;
        
        // Build orders table
        $lengowTable = $lengowOrderController->buildTable();
        
        // Get plugin information
        $pluginData = \LengowSync::getPluginData();
        $pluginIsUpToDate = true;
        if ($pluginData && version_compare($pluginData['version'], $module->version, '>')) {
            $pluginIsUpToDate = false;
        }
        
        // Get system information
        $multiShop = Shop::isFeatureActive();
        $debugMode = \LengowConfiguration::debugModeIsActive();
        
        return $this->render('@Modules/lengow/views/templates/twig/admin/orders/index.html.twig', [
            // Translation and links
            'locale' => $locale,
            'lengowPathUri' => $module->getPathUri(),
            'lengowUrl' => \LengowConfiguration::getLengowUrl(),
            'lengow_link' => $lengowLink,
            
            // Order data
            'orderCollection' => $orderCollection,
            'reportMailEnabled' => $reportMailEnabled,
            'report_mail_address' => $reportMailAddress,
            'nb_order_imported' => $nbOrderImported,
            'warning_message' => $warningMessage,
            'lengow_table' => $lengowTable,
            
            // Plugin information
            'pluginData' => $pluginData,
            'pluginIsUpToDate' => $pluginIsUpToDate,
            
            // System information
            'multiShop' => $multiShop,
            'debugMode' => $debugMode,
            'showCarrierNotification' => \LengowCarrier::hasDefaultCarrierNotMatched(),
            
            // Toolbar
            'displayToolbar' => 1,
            'current_controller' => 'LengowOrderController',
            'total_pending_order' => \LengowOrder::countOrderToBeSent(),
            'merchantStatus' => \LengowSync::getStatusAccount(),
        ]);
    }
    
    /**
     * Load orders table via AJAX
     *
     * @AdminSecurity("is_granted('read', 'AdminLengowOrder')")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function loadTableAction(Request $request): JsonResponse
    {
        $lengowOrderController = new \LengowOrderController();
        $orderTable = $lengowOrderController->buildTable();
        
        $data = [];
        $data['order_table'] = preg_replace('/\r|\n/', '', $orderTable);
        
        return new JsonResponse($data);
    }
    
    /**
     * Re-import order via AJAX
     *
     * @AdminSecurity("is_granted('update', 'AdminLengowOrder')")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reImportAction(Request $request): JsonResponse
    {
        $idOrderLengow = (int) $request->get('id', 0);
        
        if (!$idOrderLengow) {
            return new JsonResponse(['error' => 'Invalid order ID'], 400);
        }
        
        \LengowOrder::reImportOrder($idOrderLengow);
        
        $lengowOrderController = new \LengowOrderController();
        $list = $lengowOrderController->loadTable();
        $row = $list->getRow(' id = ' . $idOrderLengow);
        $html = $list->displayRow($row);
        $html = preg_replace('/\r|\n/', '', $html);
        
        $data = [
            'id_order_lengow' => $idOrderLengow,
            'html' => $html,
        ];
        
        return new JsonResponse($data);
    }
    
    /**
     * Re-send order via AJAX
     *
     * @AdminSecurity("is_granted('update', 'AdminLengowOrder')")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reSendAction(Request $request): JsonResponse
    {
        $idOrderLengow = (int) $request->get('id', 0);
        
        if (!$idOrderLengow) {
            return new JsonResponse(['error' => 'Invalid order ID'], 400);
        }
        
        \LengowOrder::reSendOrder($idOrderLengow);
        
        $lengowOrderController = new \LengowOrderController();
        $list = $lengowOrderController->loadTable();
        $row = $list->getRow(' id = ' . $idOrderLengow);
        $html = $list->displayRow($row);
        $html = preg_replace('/\r|\n/', '', $html);
        
        $data = [
            'id_order_lengow' => $idOrderLengow,
            'html' => $html,
        ];
        
        return new JsonResponse($data);
    }
    
    /**
     * Import all orders via AJAX
     *
     * @AdminSecurity("is_granted('update', 'AdminLengowOrder')")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function importAllAction(Request $request): JsonResponse
    {
        $locale = new \LengowTranslation();
        $module = Module::getInstanceByName('lengow');
        
        // Run import
        if (Shop::getContextShopID()) {
            $import = new \LengowImport([
                \LengowImport::PARAM_SHOP_ID => Shop::getContextShopID(),
                \LengowImport::PARAM_LOG_OUTPUT => false,
            ]);
        } else {
            $import = new \LengowImport([\LengowImport::PARAM_LOG_OUTPUT => false]);
        }
        $return = $import->exec();
        
        // Get import messages
        $lengowOrderController = new \LengowOrderController();
        $message = $lengowOrderController->loadMessage($return);
        
        // Get updated data
        $lastImport = \LengowMain::getLastImport();
        $orderCollection = [
            'last_import_date' => $lastImport['timestamp'] !== 'none'
                ? \LengowMain::getDateInCorrectFormat($lastImport['timestamp'])
                : '',
            'last_import_type' => $lastImport['type'],
            'link' => \LengowMain::getCronUrl(),
        ];
        
        // Get warning messages
        $warningMessages = [];
        if (\LengowConfiguration::debugModeIsActive()) {
            $lengowLink = new \LengowLink();
            $warningMessages[] = $locale->t(
                'order.screen.debug_warning_message',
                ['url' => $lengowLink->getAbsoluteAdminLink('AdminLengowMainSetting')]
            );
        }
        if (\LengowCarrier::hasDefaultCarrierNotMatched()) {
            $lengowLink = new \LengowLink();
            $warningMessages[] = $locale->t(
                'order.screen.no_carrier_warning_message',
                ['url' => $lengowLink->getAbsoluteAdminLink('AdminLengowOrderSetting')]
            );
        }
        $warningMessage = !empty($warningMessages) ? join('<br/>', $warningMessages) : false;
        
        // Render partials
        $this->get('twig')->addGlobal('orderCollection', $orderCollection);
        $this->get('twig')->addGlobal('locale', $locale);
        $this->get('twig')->addGlobal('reportMailEnabled', (bool) \LengowConfiguration::getGlobalValue(\LengowConfiguration::REPORT_MAIL_ENABLED));
        $this->get('twig')->addGlobal('report_mail_address', \LengowConfiguration::getReportEmailAddress());
        $this->get('twig')->addGlobal('warning_message', $warningMessage);
        $this->get('twig')->addGlobal('lengow_link', new \LengowLink());
        
        $displayWarningMessage = $this->get('twig')->render('@Modules/lengow/views/templates/twig/admin/orders/_partials/warning_message.html.twig');
        $displayLastImportation = $this->get('twig')->render('@Modules/lengow/views/templates/twig/admin/orders/_partials/last_importation.html.twig');
        
        // Get order table
        $orderTable = $lengowOrderController->buildTable();
        $list = $lengowOrderController->loadTable();
        
        if ($list->getTotal() > 0) {
            $displayListOrder = $orderTable;
        } else {
            $this->get('twig')->addGlobal('nb_order_imported', 0);
            $displayListOrder = $this->get('twig')->render('@Modules/lengow/views/templates/twig/admin/orders/_partials/no_order.html.twig');
        }
        
        $data = [
            'message' => '<div class="lengow_alert">' . join('<br/>', $message) . '</div>',
            'warning_message' => preg_replace('/\r|\n/', '', $displayWarningMessage),
            'last_importation' => preg_replace('/\r|\n/', '', $displayLastImportation),
            'import_orders' => $locale->t('order.screen.button_update_orders'),
            'list_order' => preg_replace('/\r|\n/', '', $displayListOrder),
            'show_carrier_notification' => \LengowCarrier::hasDefaultCarrierNotMatched(),
        ];
        
        return new JsonResponse($data);
    }
    
    /**
     * Synchronize order with Lengow
     *
     * @AdminSecurity("is_granted('update', 'AdminLengowOrder')")
     *
     * @param Request $request
     * @return Response
     */
    public function synchronizeAction(Request $request): Response
    {
        $idOrder = (int) $request->get('id_order', 0);
        
        if (!$idOrder) {
            $this->addFlash('error', 'Invalid order ID');
            return $this->redirectToRoute('lengow_admin_order_index');
        }
        
        $lengowOrder = new \LengowOrder($idOrder);
        $synchro = $lengowOrder->synchronizeOrder();
        
        if ($synchro) {
            $synchroMessage = \LengowMain::setLogMessage(
                'log.import.order_synchronized_with_lengow',
                ['order_id' => $idOrder]
            );
        } else {
            $synchroMessage = \LengowMain::setLogMessage(
                'log.import.order_not_synchronized_with_lengow',
                ['order_id' => $idOrder]
            );
        }
        
        \LengowMain::log(\LengowLog::CODE_IMPORT, $synchroMessage, false, $lengowOrder->lengowMarketplaceSku);
        
        // Redirect to PrestaShop order view
        return $this->redirect($this->getOrderAdminLink($idOrder));
    }
    
    /**
     * Cancel and re-import order
     *
     * @AdminSecurity("is_granted('update', 'AdminLengowOrder')")
     *
     * @param Request $request
     * @return Response
     */
    public function cancelReImportAction(Request $request): Response
    {
        $idOrder = (int) $request->get('id_order', 0);
        
        if (!$idOrder) {
            $this->addFlash('error', 'Invalid order ID');
            return $this->redirectToRoute('lengow_admin_order_index');
        }
        
        $lengowOrder = new \LengowOrder($idOrder);
        $newIdOrder = $lengowOrder->cancelAndreImportOrder();
        
        if (!$newIdOrder) {
            $newIdOrder = $idOrder;
        }
        
        return $this->redirect($this->getOrderAdminLink($newIdOrder));
    }
    
    /**
     * Save shipping method via AJAX
     *
     * @AdminSecurity("is_granted('update', 'AdminLengowOrder')")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function saveShippingMethodAction(Request $request): JsonResponse
    {
        $idOrder = (int) $request->get('id_order', 0);
        $shippingMethod = $request->get('method', '');
        
        $response = ['success' => false, 'message' => ''];
        
        if (!$idOrder || !$shippingMethod) {
            $response['message'] = 'Missing parameters';
            return new JsonResponse($response, 400);
        }
        
        try {
            $result = \Db::getInstance()->update(
                'lengow_orders',
                ['method' => pSQL($shippingMethod)],
                'id_order = ' . $idOrder
            );
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Delivery method successfully updated',
                ];
                \LengowMain::log(
                    \LengowLog::CODE_IMPORT,
                    'Updated shipping method : ' . $shippingMethod,
                    false,
                    $idOrder
                );
            } else {
                $response['message'] = 'No changes or errors';
            }
        } catch (\Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
            return new JsonResponse($response, 500);
        }
        
        return new JsonResponse($response);
    }
    
    /**
     * Force resend action
     *
     * @AdminSecurity("is_granted('update', 'AdminLengowOrder')")
     *
     * @param Request $request
     * @return Response
     */
    public function forceResendAction(Request $request): Response
    {
        $idOrder = (int) $request->get('id_order', 0);
        $actionType = $request->get('action_type', \LengowAction::TYPE_SHIP);
        
        if (!$idOrder) {
            $this->addFlash('error', 'Invalid order ID');
            return $this->redirectToRoute('lengow_admin_order_index');
        }
        
        $lengowOrder = new \LengowOrder($idOrder);
        $lengowOrder->callAction($actionType);
        
        return $this->redirect($this->getOrderAdminLink($idOrder));
    }
    
    /**
     * Get order admin link (PrestaShop order view)
     *
     * @param int $idOrder
     * @return string
     */
    private function getOrderAdminLink(int $idOrder): string
    {
        $link = new \LengowLink();
        try {
            if (version_compare(_PS_VERSION_, '1.7.7', '<')) {
                $href = $link->getAbsoluteAdminLink('AdminOrders')
                    . '&vieworder&id_order=' . $idOrder;
            } else {
                $sfParams = ['orderId' => $idOrder];
                $href = \Link::getUrlSmarty([
                    'entity' => 'sf',
                    'route' => 'admin_orders_view',
                    'sf-params' => $sfParams,
                ]);
            }
        } catch (\PrestaShopException $e) {
            $href = '#';
        }
        
        return $href;
    }
}
