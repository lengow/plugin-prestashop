<?php
/**
 * Copyright 2021 Lengow SAS.
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
 * @copyright 2021 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */
/**
 * Lengow Payment Class
 */
class LengowPaymentModule extends PaymentModule
{
    /**
     * @var string Lengow Payment name
     */
    public $name = 'lengow_payment';

    /**
     * Create PrestaShop order
     * Overrides PaymentModule::validateOrder()
     *
     * @param int $idCart PrestaShop cart id
     * @param int $idOrderState PrestaShop order state id
     * @param string $paymentMethod name of the payment method
     * @param string $message order message
     * @param array $lengowProducts list of Lengow products
     * @param float $lengowShippingCosts order shipping costs
     * @param float $processingFees order processing fees
     * @param string $lengowTrackingNumber Lengow carrier tracking number
     * @param int $idOrderLengow id of the record Lengow order table
     * @param string $orderStateLengow Lengow order state
     * @param string $marketplaceSku id lengow of current order
     * @param bool $logOutput display log messages
     *
     * @throws Exception|LengowException cannot load order status / payment module not active / cart cannot be loaded
     *                                   delivery country not active / product is not listed / unable to save order
     *                                   unable to save order payment / order creation failed
     *
     * @return array
     */
    public function makeOrder(
        $idCart,
        $idOrderState,
        $paymentMethod,
        $message,
        $lengowProducts,
        $lengowShippingCosts,
        $processingFees,
        $lengowTrackingNumber,
        $idOrderLengow,
        $orderStateLengow,
        $marketplaceSku,
        $logOutput
    ) {
        if (!isset($this->context)) {
            $this->context = Context::getContext();
        }
        $this->context->cart = new Cart($idCart);
        $this->context->customer = new Customer($this->context->cart->id_customer);
        // the tax cart is loaded before the customer so re-cache the tax calculation method
        if (method_exists($this->context->cart, 'setTaxCalculationMethod')) {
            $this->context->cart->setTaxCalculationMethod();
        }
        if (method_exists(new ShopUrl(), 'resetMainDomainCache')) {
            ShopUrl::resetMainDomainCache();
        }

        $idCurrency = (int) $this->context->cart->id_currency;
        $this->context->currency = new Currency($idCurrency, null, $this->context->shop->id);

        $orderStatus = new OrderState((int) $idOrderState, (int) $this->context->language->id);
        if (!Validate::isLoadedObject($orderStatus)) {
            throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.cannot_load_order_status'));
        }

        if (!$this->active) {
            throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.payment_module_not_active'));
        }

        // does order already exists ?
        if (!Validate::isLoadedObject($this->context->cart) || (bool) $this->context->cart->OrderExists() !== false) {
            throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.cart_cannot_be_loaded'));
        }

        // for each package, generate an order
        $deliveryOptionList = $this->context->cart->getDeliveryOptionList(null, true);
        $packageList = $this->context->cart->getPackageList(true);
        $cartDeliveryOption = $this->context->cart->getDeliveryOption(null, true, false);

        foreach ($deliveryOptionList as $idAddress => $package) {
            if (!isset($cartDeliveryOption[$idAddress])
                || !array_key_exists($cartDeliveryOption[$idAddress], $package)
            ) {
                $carrierAssigned = false;
                foreach (array_keys($package) as $key) {
                    // force carrier to be the one chosen in Lengow config
                    $carrierOptions = explode(',', $key);
                    foreach ($carrierOptions as $c) {
                        if ($c === $this->context->cart->id_carrier) {
                            $cartDeliveryOption[$idAddress] = $key;
                            $carrierAssigned = true;
                            break;
                        }
                    }
                    if ($carrierAssigned) {
                        break;
                    }
                }
                // else take 1st valid option
                if (!$carrierAssigned) {
                    reset($package);
                    $cartDeliveryOption[$idAddress] = key($package);
                }
            }
        }

        $orderList = [];
        $orderDetailList = [];

        do {
            $reference = Order::generateReference();
            $stop = (int) Order::getByReference($reference)->count() === 0;
        } while (!$stop);

        $this->currentOrderReference = $reference;

        $orderCreationFailed = false;
        if ($cartDeliveryOption) {
            foreach ($cartDeliveryOption as $idAddress => $keyCarriers) {
                foreach ($deliveryOptionList[$idAddress][$keyCarriers]['carrier_list'] as $idCarrier => $data) {
                    foreach ($data['package_list'] as $idPackage) {
                        // force id carrier when carrier is not found
                        if ($idCarrier !== (int) $this->context->cart->id_carrier) {
                            $idCarrier = $this->context->cart->id_carrier;
                        }
                        // rewrite the id_warehouse
                        if (method_exists($this->context->cart, 'getPackageIdWarehouse')) {
                            $idWarehouse = (int) $this->context->cart->getPackageIdWarehouse(
                                $packageList[$idAddress][$idPackage],
                                (int) $idCarrier
                            );
                            $packageList[$idAddress][$idPackage]['id_warehouse'] = $idWarehouse;
                        }
                        $packageList[$idAddress][$idPackage]['id_carrier'] = $idCarrier;
                    }
                }
            }
        }

        CartRule::cleanCache();

        foreach ($packageList as $idAddress => $packageByAddress) {
            foreach ($packageByAddress as $idPackage => $package) {
                $order = new Order();

                if (Configuration::get('PS_TAX_ADDRESS_TYPE') === 'id_address_delivery') {
                    $address = new Address($idAddress);
                    $this->context->country = new Country($address->id_country, $this->context->cart->id_lang);
                    if (!$this->context->country->active) {
                        throw new LengowException(
                            LengowMain::setLogMessage(
                                'lengow_log.exception.delivery_country_not_active',
                                ['country_name' => $this->context->country->name]
                            )
                        );
                    }
                }

                if (isset($package['id_carrier'])) {
                    $carrier = new Carrier($package['id_carrier'], $this->context->cart->id_lang);
                } else {
                    $carrier = new Carrier($this->context->cart->id_carrier, $this->context->cart->id_lang);
                }
                $order->id_carrier = (int) $carrier->id;
                $idCarrier = (int) $carrier->id;

                $order->id_customer = (int) $this->context->cart->id_customer;
                $order->id_address_invoice = (int) $this->context->cart->id_address_invoice;
                $order->id_address_delivery = (int) $idAddress;
                $order->id_currency = $this->context->currency->id;
                $order->id_lang = (int) $this->context->cart->id_lang;
                $order->id_cart = (int) $this->context->cart->id;
                $order->reference = $reference;
                $order->id_shop = (int) $this->context->shop->id;
                $order->id_shop_group = (int) $this->context->shop->id_shop_group;

                $order->secure_key = pSQL($this->context->customer->secure_key);
                $order->payment = $paymentMethod;
                if (isset($this->name)) {
                    $order->module = $this->name;
                }
                $order->recyclable = $this->context->cart->recyclable;
                $order->gift = (int) $this->context->cart->gift;
                $order->gift_message = $this->context->cart->gift_message;
                $order->mobile_theme = false;
                $order->conversion_rate = $this->context->currency->conversion_rate;

                // get precision for round
                $precision = 2;
                if (defined('_PS_PRICE_COMPUTE_PRECISION_')) {
                    $precision = _PS_PRICE_COMPUTE_PRECISION_;
                }
                $totalProducts = 0;
                $totalProductsWt = 0;

                foreach ($package['product_list'] as &$product) {
                    $sku = $product['id_product'];
                    $sku .= empty($product['id_product_attribute']) ? '' : '_' . $product['id_product_attribute'];
                    if (isset($lengowProducts[$sku])) {
                        $product['price_wt'] = $lengowProducts[$sku]['price_unit'];
                        $product['price'] = Tools::ps_round(
                            LengowProduct::calculatePriceWithoutTax(
                                $product,
                                $order->{Configuration::get('PS_TAX_ADDRESS_TYPE')},
                                $this->context
                            ),
                            $precision
                        );
                        $product['total'] = (float) $product['price'] * (int) $product['quantity'];
                        $product['total_wt'] = Tools::ps_round(
                            (float) $product['price_wt'] * (int) $product['quantity'],
                            $precision
                        );
                        // total tax free
                        $totalProducts += $product['total'];
                        // total with taxes
                        $totalProductsWt += $product['total_wt'];
                    } else {
                        throw new LengowException(
                            LengowMain::setLogMessage(
                                'lengow_log.exception.product_is_not_listed',
                                ['product_id' => $sku]
                            )
                        );
                    }
                }

                $order->product_list = $package['product_list'];
                $order->total_products = (float) Tools::ps_round($totalProducts, $precision);
                $order->total_products_wt = (float) Tools::ps_round($totalProductsWt, $precision);
                $order->total_paid_real = 0;
                $order->total_discounts_tax_excl = 0;
                $order->total_discounts_tax_incl = 0;
                $order->total_discounts = $order->total_discounts_tax_incl;

                // calculate shipping tax free
                $order->carrier_tax_rate = $carrier->getTaxesRate(
                    new Address($this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')})
                );
                $totalShippingTaxExcl = $lengowShippingCosts / (1 + ($order->carrier_tax_rate / 100));

                $order->total_shipping_tax_excl = (float) Tools::ps_round($totalShippingTaxExcl, $precision);
                $order->total_shipping_tax_incl = (float) Tools::ps_round($lengowShippingCosts, $precision);
                $order->total_shipping = $order->total_shipping_tax_incl;
                if ($lengowTrackingNumber !== null) {
                    $order->shipping_number = (string) $lengowTrackingNumber;
                }
                // add processing fees to wrapping fees
                $taxManager = TaxManagerFactory::getManager(
                    new LengowAddress($idAddress),
                    (int) Configuration::get('PS_GIFT_WRAPPING_TAX_RULES_GROUP')
                );
                $taxCalculator = $taxManager->getTaxCalculator();
                $order->total_wrapping_tax_excl = (float) Tools::ps_round(
                    $taxCalculator->removeTaxes((float) $processingFees),
                    $precision
                );
                $order->total_wrapping_tax_incl = (float) $processingFees;
                $order->total_wrapping = $order->total_wrapping_tax_incl;
                $order->total_paid_tax_excl = (float) Tools::ps_round(
                    (float) $totalProducts + (float) $totalShippingTaxExcl + (float) $order->total_wrapping_tax_excl,
                    $precision
                );
                $order->total_paid_tax_incl = (float) Tools::ps_round(
                    (float) $totalProductsWt + (float) $order->total_shipping_tax_incl + (float) $processingFees,
                    $precision
                );
                $order->total_paid = $order->total_paid_tax_incl;
                $order->round_mode = Configuration::get('PS_PRICE_ROUND_MODE');
                $order->invoice_date = '0000-00-00 00:00:00';
                $order->delivery_date = '0000-00-00 00:00:00';

                // creating order in PrestaShop
                $result = $order->add();
                if (!$result) {
                    throw new LengowException(
                        LengowMain::setLogMessage(
                            'lengow_log.exception.unable_to_save_order',
                            ['error' => Db::getInstance()->getMsgError()]
                        )
                    );
                }

                // update lengow_order table directly after creating the PrestaShop order
                $success = LengowOrder::updateOrderLengow(
                    $idOrderLengow,
                    [
                        LengowOrder::FIELD_ORDER_ID => (int) $order->id,
                        LengowOrder::FIELD_ORDER_PROCESS_STATE => LengowOrder::getOrderProcessState($orderStateLengow),
                        LengowOrder::FIELD_ORDER_LENGOW_STATE => pSQL($orderStateLengow),
                        LengowOrder::FIELD_IS_REIMPORTED => 0,
                    ]
                );
                if (!$success) {
                    LengowMain::log(
                        LengowLog::CODE_IMPORT,
                        LengowMain::setLogMessage('log.import.lengow_order_not_updated'),
                        $logOutput,
                        $marketplaceSku
                    );
                } else {
                    LengowMain::log(
                        LengowLog::CODE_IMPORT,
                        LengowMain::setLogMessage('log.import.lengow_order_updated'),
                        $logOutput,
                        $marketplaceSku
                    );
                }

                $orderList[] = $order;

                // insert new Order detail list using cart for the current order
                $orderDetail = new LengowOrderDetail(null, null, $this->context);
                if ($package['id_warehouse']) {
                    $orderDetail->createList(
                        $order,
                        $this->context->cart,
                        $idOrderState,
                        $order->product_list,
                        0,
                        true,
                        $package['id_warehouse']
                    );
                } else {
                    $orderDetail->createList(
                        $order,
                        $this->context->cart,
                        $idOrderState,
                        $order->product_list,
                        0,
                        true
                    );
                }
                $orderDetailList[] = $orderDetail;

                // adding an entry in order_carrier table
                if ($carrier !== null) {
                    $orderCarrier = new OrderCarrier();
                    $orderCarrier->id_order = (int) $order->id;
                    $orderCarrier->id_carrier = (int) $idCarrier;
                    $orderCarrier->weight = (float) $order->getTotalWeight();
                    $orderCarrier->shipping_cost_tax_excl = (float) $order->total_shipping_tax_excl;
                    $orderCarrier->shipping_cost_tax_incl = (float) $order->total_shipping_tax_incl;
                    if ($lengowTrackingNumber !== null) {
                        $orderCarrier->tracking_number = (string) $lengowTrackingNumber;
                    }
                    $orderCarrier->validateFields();
                    $orderCarrier->add();
                }
            }
        }

        // register payment only if the order status validate the order
        if ($orderStatus->logable && isset($order)) {
            $idTransaction = null;
            if (!$order->addOrderPayment($order->total_paid_tax_incl, null, $idTransaction)) {
                throw new LengowException(
                    LengowMain::setLogMessage('lengow_log.exception.unable_to_save_order_payment')
                );
            }
        }

        foreach ($orderDetailList as $key => $orderDetail) {
            $order = $orderList[$key];
            if (!$orderCreationFailed && isset($order->id)) {
                if (isset($message) & !empty($message)) {
                    $msg = new Message();
                    $message = strip_tags($message, '<br>');
                    if (Validate::isCleanHtml($message)) {
                        $msg->message = $message;
                        $msg->id_order = (int) $order->id;
                        $msg->private = 1;
                        $msg->add();
                    }
                }
                // specify order id for message
                $oldMessage = Message::getMessageByCartId((int) $this->context->cart->id);
                if ($oldMessage) {
                    $updateMessage = new Message((int) $oldMessage['id_message']);
                    $updateMessage->id_order = (int) $order->id;
                    $updateMessage->update();

                    // add this message in the customer thread
                    $customerThread = new CustomerThread();
                    $customerThread->id_contact = 0;
                    $customerThread->id_customer = (int) $order->id_customer;
                    $customerThread->id_shop = (int) $this->context->shop->id;
                    $customerThread->id_order = (int) $order->id;
                    $customerThread->id_lang = (int) $this->context->language->id;
                    $customerThread->email = $this->context->customer->email;
                    $customerThread->status = 'open';
                    $customerThread->token = Tools::passwdGen(12);
                    $customerThread->add();

                    $customerMessage = new CustomerMessage();
                    $customerMessage->id_customer_thread = $customerThread->id;
                    $customerMessage->id_employee = 0;
                    $customerMessage->message = $updateMessage->message;
                    $customerMessage->private = 0;
                    $customerMessage->add();
                }

                foreach ($this->context->cart->getProducts() as $product) {
                    if ($orderStatus->logable) {
                        ProductSale::addProductSale((int) $product['id_product'], (int) $product['cart_quantity']);
                    }
                }

                // set the order status
                $newHistory = new OrderHistory();
                $newHistory->id_order = (int) $order->id;
                $newHistory->changeIdOrderState((int) $idOrderState, $order, true);
                $newHistory->addWithemail(true, null);

                // switch to back order if needed
                if (Configuration::get('PS_STOCK_MANAGEMENT') && $orderDetail->getStockState()) {
                    $history = new OrderHistory();
                    $history->id_order = (int) $order->id;
                    if (version_compare(_PS_VERSION_, '1.6.0.11', '<')) {
                        $history->changeIdOrderState(Configuration::get('PS_OS_OUTOFSTOCK'), $order, true);
                    }

                    $history->changeIdOrderState(
                        Configuration::get($order->valid ? 'PS_OS_OUTOFSTOCK_PAID' : 'PS_OS_OUTOFSTOCK_UNPAID'),
                        $order,
                        true
                    );
                    $history->addWithemail();
                }

                unset($orderDetail);

                // order is reloaded because the status just changed
                $order = new Order($order->id);

                // updates stock in shops
                if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                    $productList = $order->getProducts();
                    foreach ($productList as $product) {
                        // if the available quantities depends on the physical stock
                        if (StockAvailable::dependsOnStock($product['product_id'])) {
                            // synchronizes
                            StockAvailable::synchronize($product['product_id'], $order->id_shop);
                        }
                    }
                }
            } else {
                throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.order_creation_failed'));
            }
        }

        // update Order Details Tax in case cart rules have free shipping
        foreach ($order->getOrderDetailList() as $detail) {
            $orderDetail = new OrderDetail($detail['id_order_detail']);
            $orderDetail->updateTaxAmount($order);
            
        }

        // use the last order as currentOrder
        if (isset($order) && $order->id) {
            $this->currentOrder = (int) $order->id;
        }

        return $orderList;
    }

    /**
     * Set context for payment module
     *
     * @param Context $context PrestaShop context instance
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }
}
