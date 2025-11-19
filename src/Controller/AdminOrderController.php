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

use PrestaShop\PrestaShop\Core\Domain\CartRule\Exception\InvalidCartRuleDiscountValueException;
use PrestaShop\PrestaShop\Core\Domain\Order\Command\UpdateOrderShippingDetailsCommand;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\CannotEditDeliveredOrderProductException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\CannotFindProductInOrderException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\DuplicateProductInOrderException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\DuplicateProductInOrderInvoiceException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\InvalidAmountException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\InvalidCancelProductException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\InvalidOrderStateException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\InvalidProductQuantityException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\NegativePaymentAmountException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderConstraintException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderEmailSendException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\TransistEmailSendingException;
use PrestaShop\PrestaShop\Core\Domain\Order\Query\GetOrderForViewing;
use PrestaShop\PrestaShop\Core\Domain\Order\QueryResult\OrderForViewing;
use PrestaShop\PrestaShop\Core\Domain\Product\Exception\ProductOutOfStockException;
use PrestaShop\PrestaShop\Core\Domain\ValueObject\QuerySorting;
use PrestaShop\PrestaShop\Core\Order\OrderSiblingProviderInterface;
use PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButtonsCollection;
use PrestaShopBundle\Controller\Admin\Sell\Order\OrderController;
use PrestaShopBundle\Exception\InvalidModuleException;
use PrestaShopBundle\Form\Admin\Sell\Customer\PrivateNoteType;
use PrestaShopBundle\Form\Admin\Sell\Order\AddOrderCartRuleType;
use PrestaShopBundle\Form\Admin\Sell\Order\AddProductRowType;
use PrestaShopBundle\Form\Admin\Sell\Order\ChangeOrderAddressType;
use PrestaShopBundle\Form\Admin\Sell\Order\ChangeOrderCurrencyType;
use PrestaShopBundle\Form\Admin\Sell\Order\EditProductRowType;
use PrestaShopBundle\Form\Admin\Sell\Order\InternalNoteType;
use PrestaShopBundle\Form\Admin\Sell\Order\OrderMessageType;
use PrestaShopBundle\Form\Admin\Sell\Order\OrderPaymentType;
use PrestaShopBundle\Form\Admin\Sell\Order\UpdateOrderShippingType;
use PrestaShopBundle\Form\Admin\Sell\Order\UpdateOrderStatusType;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminOrderController extends OrderController
{
    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     *
     * @param int $orderId
     * @param Request $request
     *
     * @return Response
     */
    public function viewAction(int $orderId, Request $request): Response
    {
        try {
            if (!$this->isFromLengow($orderId)) {
                return parent::viewAction($orderId, $request);
            }
            /** @var OrderForViewing $orderForViewing */
            $orderForViewing = $this->getQueryBus()->handle(new GetOrderForViewing($orderId, QuerySorting::DESC));
        } catch (OrderException $e) {
            $this->addFlash('error', $this->getErrorMessageForException($e, $this->getErrorMessages($e)));

            return $this->redirectToRoute('admin_orders_index');
        }
        $locale = new \LengowTranslation();
        $formFactory = $this->get('form.factory');
        $updateOrderStatusForm = $formFactory->createNamed(
            'update_order_status',
            UpdateOrderStatusType::class,
            [
                'new_order_status_id' => $orderForViewing->getHistory()->getCurrentOrderStatusId(),
            ]
        );
        $updateOrderStatusActionBarForm = $formFactory->createNamed(
            'update_order_status_action_bar',
            UpdateOrderStatusType::class,
            [
                'new_order_status_id' => $orderForViewing->getHistory()->getCurrentOrderStatusId(),
            ]
        );

        $addOrderCartRuleForm = $this->createForm(AddOrderCartRuleType::class, [], [
            'order_id' => $orderId,
        ]);
        $addOrderPaymentForm = $this->createForm(OrderPaymentType::class, [
            'id_currency' => $orderForViewing->getCurrencyId(),
        ], [
            'id_order' => $orderId,
        ]);

        $orderMessageForm = $this->createForm(OrderMessageType::class, [
            'lang_id' => $orderForViewing->getCustomer()->getLanguageId(),
        ], [
            'action' => $this->generateUrl('admin_orders_send_message', ['orderId' => $orderId]),
        ]);
        $orderMessageForm->handleRequest($request);

        $changeOrderCurrencyForm = $this->createForm(ChangeOrderCurrencyType::class, [], [
            'current_currency_id' => $orderForViewing->getCurrencyId(),
        ]);

        $changeOrderAddressForm = null;
        $privateNoteForm = null;

        if (null !== $orderForViewing->getCustomer() && $orderForViewing->getCustomer()->getId() !== 0) {
            $changeOrderAddressForm = $this->createForm(ChangeOrderAddressType::class, [], [
                'customer_id' => $orderForViewing->getCustomer()->getId(),
            ]);

            $privateNoteForm = $this->createForm(PrivateNoteType::class, [
                'note' => $orderForViewing->getCustomer()->getPrivateNote(),
            ]);
        }

        $updateOrderShippingForm = $this->createForm(UpdateOrderShippingType::class, [
            'new_carrier_id' => $orderForViewing->getCarrierId(),
        ], [
            'order_id' => $orderId,
        ]);
        $isActiveReturnCarrier = false;
        $isActiveReturnTrackingNumber = false;
        if ($this->isFromLengow($orderId)) {
            $isActiveReturnTrackingNumber = $this->isActiveReturnTrackingNumber($orderId);
            $isActiveReturnCarrier = $this->isActiveReturnTrackingCarrier($orderId);
        }

        if ($isActiveReturnTrackingNumber) {
            $returnTrackingNumber = $this->getReturnTrackingNumber($orderId);
            $updateOrderShippingForm->add(\LengowAction::ARG_RETURN_TRACKING_NUMBER, TextType::class, [
                'required' => false,
                'data' => $returnTrackingNumber,
            ]);
        }

        if ($isActiveReturnCarrier) {
            $returnCarrier = $this->getReturnCarrier($orderId);
            $updateOrderShippingForm->add(\LengowAction::ARG_RETURN_CARRIER, ChoiceType::class, [
                'required' => false,
                'data' => $returnCarrier,
                'choices' => \LengowCarrier::getCarriersChoices(
                    $orderForViewing->getCustomer()->getLanguageId()
                ),
            ]);
        }
        $currencyDataProvider = $this->container->get('prestashop.adapter.data_provider.currency');
        $orderCurrency = $currencyDataProvider->getCurrencyById($orderForViewing->getCurrencyId());

        $addProductRowForm = $this->createForm(AddProductRowType::class, [], [
            'order_id' => $orderId,
            'currency_id' => $orderForViewing->getCurrencyId(),
            'symbol' => $orderCurrency->symbol,
        ]);
        $editProductRowForm = $this->createForm(EditProductRowType::class, [], [
            'order_id' => $orderId,
            'symbol' => $orderCurrency->symbol,
        ]);

        $internalNoteForm = $this->createForm(InternalNoteType::class, [
            'note' => $orderForViewing->getNote(),
        ]);

        $formBuilder = $this->get('prestashop.core.form.identifiable_object.builder.cancel_product_form_builder');
        $backOfficeOrderButtons = new ActionsBarButtonsCollection();

        try {
            $this->dispatchHook(
                'actionGetAdminOrderButtons',
                [
                    'controller' => $this,
                    'id_order' => $orderId,
                    'actions_bar_buttons_collection' => $backOfficeOrderButtons,
                ]
            );

            $cancelProductForm = $formBuilder->getFormFor($orderId);
            if ($this->isFromLengow($orderId)) {
                $lengowOrder = new \LengowOrder($orderId);
                $marketplace = $lengowOrder->getMarketplace();
                $refundReasons = $marketplace->getRefundReasons();
                $refundMode = $marketplace->getRefundModes();
                $refundSelectedDatas = $lengowOrder->getRefundDataFromLengowOrder($orderId, $marketplace->name);
            }
        } catch (\Exception $e) {
            $this->addFlash('error', $this->getErrorMessageForException($e, $this->getErrorMessages($e)));

            return $this->redirectToRoute('admin_orders_index');
        }

        $this->handleOutOfStockProduct($orderForViewing);

        $merchandiseReturnEnabled = (bool) $this->configuration->get('PS_ORDER_RETURN');

        /** @var OrderSiblingProviderInterface $orderSiblingProvider */
        $orderSiblingProvider = $this->get('prestashop.adapter.order.order_sibling_provider');

        $paginationNum = (int) $this->configuration->get('PS_ORDER_PRODUCTS_NB_PER_PAGE', self::DEFAULT_PRODUCTS_NUMBER);
        $paginationNumOptions = self::PRODUCTS_PAGINATION_OPTIONS;
        if (!in_array($paginationNum, $paginationNumOptions)) {
            $paginationNumOptions[] = $paginationNum;
        }
        sort($paginationNumOptions);
        $metatitle = sprintf(
            '%s %s %s',
            $this->trans('Orders', 'Admin.Orderscustomers.Feature'),
            $this->configuration->get('PS_NAVIGATION_PIPE', '>'),
            $this->trans(
                'Order %reference% from %firstname% %lastname%',
                'Admin.Orderscustomers.Feature',
                [
                    '%reference%' => $orderForViewing->getReference(),
                    '%firstname%' => $orderForViewing->getCustomer()->getFirstName(),
                    '%lastname%' => $orderForViewing->getCustomer()->getLastName(),
                ]
            )
        );

        return $this->render('@PrestaShop/Admin/Sell/Order/Order/view.html.twig', [
            'showContentHeader' => true,
            'enableSidebar' => true,
            'orderCurrency' => $orderCurrency,
            'meta_title' => $metatitle,
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
            'orderForViewing' => $orderForViewing,
            'addOrderCartRuleForm' => $addOrderCartRuleForm->createView(),
            'updateOrderStatusForm' => $updateOrderStatusForm->createView(),
            'updateOrderStatusActionBarForm' => $updateOrderStatusActionBarForm->createView(),
            'addOrderPaymentForm' => $addOrderPaymentForm->createView(),
            'changeOrderCurrencyForm' => $changeOrderCurrencyForm->createView(),
            'privateNoteForm' => $privateNoteForm ? $privateNoteForm->createView() : null,
            'updateOrderShippingForm' => $updateOrderShippingForm->createView(),
            'cancelProductForm' => $cancelProductForm->createView(),
            'invoiceManagementIsEnabled' => $orderForViewing->isInvoiceManagementIsEnabled(),
            'changeOrderAddressForm' => $changeOrderAddressForm ? $changeOrderAddressForm->createView() : null,
            'orderMessageForm' => $orderMessageForm->createView(),
            'addProductRowForm' => $addProductRowForm->createView(),
            'editProductRowForm' => $editProductRowForm->createView(),
            'backOfficeOrderButtons' => $backOfficeOrderButtons,
            'merchandiseReturnEnabled' => $merchandiseReturnEnabled,
            'priceSpecification' => $this->getContextLocale()->getPriceSpecification($orderCurrency->iso_code)->toArray(),
            'previousOrderId' => $orderSiblingProvider->getPreviousOrderId($orderId),
            'nextOrderId' => $orderSiblingProvider->getNextOrderId($orderId),
            'paginationNum' => $paginationNum,
            'paginationNumOptions' => $paginationNumOptions,
            'isAvailableQuantityDisplayed' => $this->configuration->getBoolean('PS_STOCK_MANAGEMENT'),
            'internalNoteForm' => $internalNoteForm->createView(),
            'returnTrackingNumber' => $this->getReturnTrackingNumber($orderId),
            'returnCarrier' => $this->getReturnCarrier($orderId),
            'isActiveReturnTrackingNumber' => $isActiveReturnTrackingNumber,
            'isActiveReturnCarrier' => $isActiveReturnCarrier,
            'returnTrackingNumberLabel' => $locale->t('order.screen.return_tracking_number_label'),
            'returnCarrierLabel' => $locale->t('order.screen.return_carrier_label'),
            'returnCarrierName' => $this->getReturnCarrierName($orderId),
            'refundReasons' => $refundReasons ?? [],
            'refundModes' => $refundMode ?? [],
            'refundReasonSelected' => $refundSelectedDatas['refund_reason'] ?? '',
            'refundModeSelected' => $refundSelectedDatas['refund_mode'] ?? '',
        ]);
    }

    /**
     * @AdminSecurity(
     *     "is_granted('update', request.get('_legacy_controller'))",
     *     redirectRoute="admin_orders_view",
     *     redirectQueryParamsToKeep={"orderId"},
     *     message="You do not have permission to edit this."
     * )
     *
     * @param int $orderId
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function updateShippingAction(int $orderId, Request $request): RedirectResponse
    {
        $form = $this->createForm(UpdateOrderShippingType::class, [], [
            'order_id' => $orderId,
        ]);

        if ($this->isFromLengow($orderId)) {
            if ($this->isActiveReturnTrackingNumber($orderId)) {
                $form->add(\LengowAction::ARG_RETURN_TRACKING_NUMBER, TextType::class, [
                    'required' => false,
                ]);
            }
            if ($this->isActiveReturnTrackingCarrier($orderId)) {
                $order = new \LengowOrder($orderId);
                $form->add(\LengowAction::ARG_RETURN_CARRIER, ChoiceType::class, [
                    'required' => false,
                    'choices' => \LengowCarrier::getCarriersChoices(
                        $order->id_lang
                    ),
                ]);
            }
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                if (!empty($data[\LengowAction::ARG_RETURN_TRACKING_NUMBER])) {
                    \LengowOrderDetail::updateOrderReturnTrackingNumber(
                        $data[\LengowAction::ARG_RETURN_TRACKING_NUMBER],
                        $orderId
                    );
                }
                if (!empty($data[\LengowAction::ARG_RETURN_CARRIER])) {
                    \LengowOrderDetail::updateOrderReturnCarrier(
                        (int) $data[\LengowAction::ARG_RETURN_CARRIER],
                        $orderId
                    );
                }
                $this->getCommandBus()->handle(
                    new UpdateOrderShippingDetailsCommand(
                        $orderId,
                        (int) $data['current_order_carrier_id'],
                        (int) $data['new_carrier_id'],
                        $data['tracking_number']
                    )
                );

                $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));
            } catch (TransistEmailSendingException $e) {
                $this->addFlash(
                    'error',
                    $this->trans(
                        'An error occurred while sending an email to the customer.',
                        'Admin.Orderscustomers.Notification'
                    )
                );
            } catch (\Exception $e) {
                $this->addFlash('error', $this->getErrorMessageForException($e, $this->getErrorMessages($e)));
            }
        } else {
            // exit ('form not valid');
        }

        return $this->redirectToRoute('admin_orders_view', [
            'orderId' => $orderId,
        ]);
    }

    /**
     * @param OrderForViewing $orderForViewing
     */
    private function handleOutOfStockProduct(OrderForViewing $orderForViewing)
    {
        $isStockManagementEnabled = $this->configuration->getBoolean('PS_STOCK_MANAGEMENT');
        if (!$isStockManagementEnabled || $orderForViewing->isDelivered() || $orderForViewing->isShipped()) {
            return;
        }

        foreach ($orderForViewing->getProducts()->getProducts() as $product) {
            if ($product->getAvailableQuantity() <= 0) {
                $this->addFlash(
                    'warning',
                    $this->trans('This product is out of stock:', 'Admin.Orderscustomers.Notification') . ' ' . $product->getName()
                );
            }
        }
    }

    /**
     * @param \Exception $e
     *
     * @return array
     */
    private function getErrorMessages(\Exception $e)
    {
        $refundableQuantity = 0;
        if ($e instanceof InvalidCancelProductException) {
            $refundableQuantity = $e->getRefundableQuantity();
        }
        $orderInvoiceNumber = '#unknown';
        if ($e instanceof DuplicateProductInOrderInvoiceException) {
            $orderInvoiceNumber = $e->getOrderInvoiceNumber();
        }

        return [
            CannotEditDeliveredOrderProductException::class => $this->trans('You cannot edit the cart once the order delivered.', 'Admin.Orderscustomers.Notification'),
            OrderNotFoundException::class => $e instanceof OrderNotFoundException ?
                $this->trans(
                    'Order #%d cannot be loaded.',
                    'Admin.Orderscustomers.Notification',
                    ['#%d' => $e->getOrderId()->getValue()]
                ) : '',
            OrderEmailSendException::class => $this->trans(
                'An error occurred while sending the e-mail to the customer.',
                'Admin.Orderscustomers.Notification'
            ),
            OrderException::class => $this->trans(
                $e->getMessage(),
                'Admin.Orderscustomers.Notification'
            ),
            InvalidAmountException::class => $this->trans(
                'Only numbers and decimal points (".") are allowed in the amount fields, e.g. 10.50 or 1050.',
                'Admin.Orderscustomers.Notification'
            ),
            InvalidCartRuleDiscountValueException::class => [
                InvalidCartRuleDiscountValueException::INVALID_MIN_PERCENT => $this->trans(
                    'Percent value must be greater than 0.',
                    'Admin.Orderscustomers.Notification'
                ),
                InvalidCartRuleDiscountValueException::INVALID_MAX_PERCENT => $this->trans(
                    'Percent value cannot exceed 100.',
                    'Admin.Orderscustomers.Notification'
                ),
                InvalidCartRuleDiscountValueException::INVALID_MIN_AMOUNT => $this->trans(
                    'Amount value must be greater than 0.',
                    'Admin.Orderscustomers.Notification'
                ),
                InvalidCartRuleDiscountValueException::INVALID_MAX_AMOUNT => $this->trans(
                    'Discount value cannot exceed the total price of this order.',
                    'Admin.Orderscustomers.Notification'
                ),
                InvalidCartRuleDiscountValueException::INVALID_FREE_SHIPPING => $this->trans(
                    'Shipping discount value cannot exceed the total price of this order.',
                    'Admin.Orderscustomers.Notification'
                ),
            ],
            InvalidCancelProductException::class => [
                InvalidCancelProductException::INVALID_QUANTITY => $this->trans(
                    'Positive product quantity is required.',
                    'Admin.Notifications.Error'
                ),
                InvalidCancelProductException::QUANTITY_TOO_HIGH => $this->trans(
                    'Please enter a maximum quantity of [1].',
                    'Admin.Orderscustomers.Notification',
                    ['[1]' => $refundableQuantity]
                ),
                InvalidCancelProductException::NO_REFUNDS => $this->trans(
                    'Please select at least one product.',
                    'Admin.Orderscustomers.Notification'
                ),
                InvalidCancelProductException::INVALID_AMOUNT => $this->trans(
                    'Please enter a positive amount.',
                    'Admin.Orderscustomers.Notification'
                ),
                InvalidCancelProductException::NO_GENERATION => $this->trans(
                    'Please generate at least one credit slip or voucher.',
                    'Admin.Orderscustomers.Notification'
                ),
            ],
            InvalidModuleException::class => $this->trans(
                'You must choose a payment module to create the order.',
                'Admin.Orderscustomers.Notification'
            ),
            ProductOutOfStockException::class => $this->trans(
                'There are not enough products in stock.',
                'Admin.Catalog.Notification'
            ),
            NegativePaymentAmountException::class => $this->trans(
                'Invalid value: the payment must be a positive amount.',
                'Admin.Notifications.Error'
            ),
            InvalidOrderStateException::class => [
                InvalidOrderStateException::ALREADY_PAID => $this->trans(
                    'Invalid action: this order has already been paid.',
                    'Admin.Notifications.Error'
                ),
                InvalidOrderStateException::DELIVERY_NOT_FOUND => $this->trans(
                    'Invalid action: this order has not been delivered.',
                    'Admin.Notifications.Error'
                ),
                InvalidOrderStateException::UNEXPECTED_DELIVERY => $this->trans(
                    'Invalid action: this order has already been delivered.',
                    'Admin.Notifications.Error'
                ),
                InvalidOrderStateException::NOT_PAID => $this->trans(
                    'Invalid action: this order has not been paid.',
                    'Admin.Notifications.Error'
                ),
                InvalidOrderStateException::INVALID_ID => $this->trans(
                    'You must choose an order status to create the order.',
                    'Admin.Orderscustomers.Notification'
                ),
            ],

            OrderConstraintException::class => [
                OrderConstraintException::INVALID_CUSTOMER_MESSAGE => $this->trans(
                    'The order message given is invalid.',
                    'Admin.Orderscustomers.Notification'
                ),
            ],
            InvalidProductQuantityException::class => $this->trans(
                'Positive product quantity is required.',
                'Admin.Notifications.Error'
            ),
            DuplicateProductInOrderException::class => $this->trans(
                'This product is already in your order, please edit the quantity instead.',
                'Admin.Notifications.Error'
            ),
            DuplicateProductInOrderInvoiceException::class => $this->trans(
                'This product is already in the invoice [1], please edit the quantity instead.',
                'Admin.Notifications.Error',
                ['[1]' => $orderInvoiceNumber]
            ),
            CannotFindProductInOrderException::class => $this->trans(
                'You cannot edit the price of a product that no longer exists in your catalog.',
                'Admin.Notifications.Error'
            ),
        ];
    }

    /**
     * @return bool
     */
    private function isActiveReturnTrackingNumber(int $orderId): bool
    {
        $lengowOrder = new \LengowOrder($orderId);
        if ($lengowOrder->getMarketplace()) {
            return $lengowOrder->getMarketplace()->hasReturnTrackingNumber();
        }

        return false;
    }

    /**
     * @return bool
     */
    private function isActiveReturnTrackingCarrier(int $orderId): bool
    {
        $lengowOrder = new \LengowOrder($orderId);
        if ($lengowOrder->getMarketplace()) {
            return $lengowOrder->getMarketplace()->hasReturnTrackingCarrier();
        }

        return false;
    }

    /**
     * @param int $orderId
     *
     * @return string
     */
    private function getReturnTrackingNumber(int $orderId): string
    {
        return \LengowOrderDetail::getOrderReturnTrackingNumber($orderId);
    }

    /**
     * @param int $orderId
     *
     * @return string
     */
    private function getReturnCarrier(int $orderId): string
    {
        return \LengowOrderDetail::getOrderReturnCarrier($orderId);
    }

    /**
     * @param int $orderId
     *
     * @return string
     */
    private function getReturnCarrierName(int $orderId): string
    {
        return \LengowOrderDetail::getOrderReturnCarrierName($orderId);
    }

    /**
     * @param int $orderId
     */
    private function isFromLengow(int $orderId): bool
    {
        return \LengowOrder::isFromLengow($orderId);
    }

    public function saveRefundReason(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $orderId = (int) $data['orderId'];
        $reason = $data['reason'] ?? '';

        if (empty($orderId) || empty($reason)) {
            return new JsonResponse(['success' => false, 'message' => 'Données manquantes']);
        }

        \Db::getInstance()->update('lengow_orders', [
            'refund_reason' => pSQL($reason),
        ], 'id_order = ' . (int) $orderId);

        return new JsonResponse(['success' => true]);
    }

    public function saveRefundMode(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $orderId = (int) $data['orderId'];
        $reason = $data['mode'] ?? '';

        if (empty($orderId) || empty($reason)) {
            return new JsonResponse(['success' => false, 'message' => 'Données manquantes']);
        }

        \Db::getInstance()->update('lengow_orders', [
            'refund_mode' => pSQL($reason),
        ], 'id_order = ' . (int) $orderId);

        return new JsonResponse(['success' => true]);
    }
}
