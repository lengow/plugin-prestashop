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

declare(strict_types=1);

namespace PrestaShop\Module\Lengow\Controller\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\Lengow\Service\OrderRefundDataUpdater;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShopBundle\Security\Attribute\AdminSecurity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class LengowOrderAdminController extends AbstractLengowAdminController
{
    public function __construct(
        LegacyContext $legacyContext,
        Environment $twig,
        private readonly OrderRefundDataUpdater $orderRefundDataUpdater,
    ) {
        parent::__construct($legacyContext, $twig);
        if (!defined('_PS_MODULE_LENGOW_DIR_')) {
            define('_PS_MODULE_LENGOW_DIR_', _PS_MODULE_DIR_ . 'lengow' . \DIRECTORY_SEPARATOR);
        }
    }

    protected function getPageTitle(): string
    {
        return (new \LengowTranslation($this->legacyContext))->t('tab.order');
    }

    #[AdminSecurity("is_granted('read', request.get('_legacy_controller'))")]
    public function indexAction(Request $request): Response
    {
        $lengowController = new \LengowOrderController($this->legacyContext, $this->twig, true);

        $response = $this->handleLegacyPostAction($request, $lengowController);
        if ($response instanceof Response) {
            return $response;
        }

        return $this->renderLegacyPage('@Modules/lengow/views/templates/admin/lengow_order/view.html.twig', $lengowController);
    }

    /**
     * Returns cancel/refund reasons and state IDs for a given order as JSON.
     * Used by the inline selector in the PS order view Twig override.
     */
    #[AdminSecurity("is_granted('read', 'AdminOrders')")]
    public function getOrderReasonsAction(int $orderId): JsonResponse
    {
        if (!$orderId || !\LengowOrder::isFromLengow($orderId)) {
            return new JsonResponse(['isFromLengow' => false]);
        }

        try {
            $lengowOrder = new \LengowOrder($orderId);
            $marketplace = $lengowOrder->getMarketplace();

            if (!$marketplace) {
                return new JsonResponse(['isFromLengow' => false]);
            }

            $refundSelectedData = $lengowOrder->getRefundDataFromLengowOrder($orderId, $marketplace->name);

            return new JsonResponse([
                'isFromLengow' => true,
                'cancelReasons' => $marketplace->getCancelReasons(),
                'cancelReasonSelected' => $refundSelectedData['cancel_reason'] ?? '',
                'cancelStateId' => (int) \LengowMain::getOrderState(\LengowOrder::STATE_CANCELED),
                'refundReasons' => $marketplace->getRefundReasons(),
                'refundReasonSelected' => $refundSelectedData['refund_reason'] ?? '',
                'refundModes' => $marketplace->getRefundModes(),
                'refundModeSelected' => $refundSelectedData['refund_mode'] ?? '',
                'refundStateId' => (int) \LengowMain::getOrderState(\LengowOrder::STATE_REFUNDED),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['isFromLengow' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Saves the refund or cancel reason for an order.
     */
    #[AdminSecurity("is_granted('update', 'AdminOrders')")]
    public function saveRefundReasonAction(Request $request): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true) ?? [];
        $orderId = (int) ($data['orderId'] ?? 0);
        $reason = (string) ($data['reason'] ?? '');

        if (!$orderId) {
            return new JsonResponse(['success' => false, 'message' => 'Missing order id']);
        }

        $success = $this->orderRefundDataUpdater->updateRefundReason($orderId, $reason);

        return new JsonResponse(['success' => $success]);
    }

    /**
     * Saves the refund mode for an order.
     */
    #[AdminSecurity("is_granted('update', 'AdminOrders')")]
    public function saveRefundModeAction(Request $request): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true) ?? [];
        $orderId = (int) ($data['orderId'] ?? 0);
        $mode = (string) ($data['mode'] ?? '');

        if (!$orderId) {
            return new JsonResponse(['success' => false, 'message' => 'Missing order id']);
        }

        $success = $this->orderRefundDataUpdater->updateRefundMode($orderId, $mode);

        return new JsonResponse(['success' => $success]);
    }
}
