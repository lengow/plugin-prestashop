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
 * @category  Model
 * @package   LengowPaymentModule
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2017 Lengow SAS
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
     * Create Prestashop order
     * Overrides PaymentModule::validateOrder()
     *
     * @param integer $idCart               Prestashop cart id
     * @param integer $idOrderState         Prestashop order state id
     * @param string  $paymentMethod        name of the payment method
     * @param string  $message              order message
     * @param array   $lengowProducts       list of Lengow products
     * @param float   $lengowShippingCosts  order shipping costs
     * @param float   $processingFees       order processing fees
     * @param string  $lengowTrackingNumber Lengow carrier tracking number
     *
     * @throws Exception cannot load order status / payment module not active / cart cannot be loaded
     *                   delivery country not active / product is not listed / unable to save order
     *                   unable to save order payment / order creation failed
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
        $processingFees = null,
        $lengowTrackingNumber = null
    ) {
        if (!isset($this->context)) {
            $this->context = Context::getContext();
        }
        $this->context->cart = new Cart($idCart);
        $this->context->customer = new Customer($this->context->cart->id_customer);
        // The tax cart is loaded before the customer so re-cache the tax calculation method
        if (method_exists($this->context->cart, 'setTaxCalculationMethod')) {
            $this->context->cart->setTaxCalculationMethod();
        }
        if (method_exists(new ShopUrl(), 'resetMainDomainCache')) {
            ShopUrl::resetMainDomainCache();
        }

        $idCurrency = (int)$this->context->cart->id_currency;
        $this->context->currency = new Currency($idCurrency, null, $this->context->shop->id);

        $orderStatus = new OrderState((int)$idOrderState, (int)$this->context->language->id);
        if (!Validate::isLoadedObject($orderStatus)) {
            throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.cannot_load_order_status'));
        }

        if (!$this->active) {
            throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.payment_module_not_active'));
        }

        // Does order already exists ?
        if (!Validate::isLoadedObject($this->context->cart) || $this->context->cart->OrderExists() != false) {
            throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.cart_cannot_be_loaded'));
        }

        // For each package, generate an order
        $deliveryOptionList = $this->context->cart->getDeliveryOptionList(null, true);
        $packageList = $this->context->cart->getPackageList(true);
        $cartDeliveryOption = $this->context->cart->getDeliveryOption(null, true, false);

        foreach ($deliveryOptionList as $idAddress => $package) {
            if (!isset($cartDeliveryOption[$idAddress])
                || !array_key_exists($cartDeliveryOption[$idAddress], $package)
            ) {
                foreach ($package as $key => $val) {
                    // This line is useless, but Prestashop validator require it
                    $val = $val;
                    // force carrier to be the one chosen in Lengow config
                    $carrierOptions = explode(',', $key);
                    $carrierAssigned = false;
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
                    foreach ($package as $key => $val) {
                        $cartDeliveryOption[$idAddress] = $key;
                        break;
                    }
                }
            }
        }

        $orderList = array();
        $orderDetailList = array();

        $stop = false;
        do {
            $reference = Order::generateReference();
            $stop = Order::getByReference($reference)->count() == 0;
        } while (!$stop);

        $this->currentOrderReference = $reference;

        $orderCreationFailed = false;
        if ($cartDeliveryOption) {
            foreach ($cartDeliveryOption as $idAddress => $keyCarriers) {
                foreach ($deliveryOptionList[$idAddress][$keyCarriers]['carrier_list'] as $idCarrier => $data) {
                    foreach ($data['package_list'] as $idPackage) {
                        if ($idCarrier != $this->context->cart->id_carrier) {
                            $idCarrier =  $this->context->cart->id_carrier;
                        }
                        // Rewrite the id_warehouse
                        if (method_exists($this->context->cart, 'getPackageIdWarehouse')) {
                            $idWarehouse = (int)$this->context->cart->getPackageIdWarehouse(
                                $packageList[$idAddress][$idPackage],
                                (int)$idCarrier
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
            $nbPackage = count($packageByAddress);
        }
        foreach ($packageByAddress as $idPackage => $package) {
            $order = new Order();

            if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery') {
                $address = new Address($idAddress);
                $this->context->country = new Country($address->id_country, $this->context->cart->id_lang);
                if (!$this->context->country->active) {
                    throw new LengowException(
                        LengowMain::setLogMessage('lengow_log.exception.delivery_country_not_active')
                    );
                }
            }

            if (isset($package['id_carrier'])) {
                $carrier = new Carrier($package['id_carrier'], $this->context->cart->id_lang);
            } else {
                $carrier = new Carrier($this->context->cart->id_carrier, $this->context->cart->id_lang);
            }
            $order->id_carrier = (int)$carrier->id;
            $idCarrier = (int)$carrier->id;

            $order->id_customer = (int)$this->context->cart->id_customer;
            $order->id_address_invoice = (int)$this->context->cart->id_address_invoice;
            $order->id_address_delivery = (int)$idAddress;
            $order->id_currency = $this->context->currency->id;
            $order->id_lang = (int)$this->context->cart->id_lang;
            $order->id_cart = (int)$this->context->cart->id;
            $order->reference = $reference;
            $order->id_shop = (int)$this->context->shop->id;
            $order->id_shop_group = (int)$this->context->shop->id_shop_group;

            $order->secure_key = pSQL($this->context->customer->secure_key);
            $order->payment = $paymentMethod;
            if (isset($this->name)) {
                $order->module = $this->name;
            }
            $order->recyclable = $this->context->cart->recyclable;
            $order->gift = (int)$this->context->cart->gift;
            $order->gift_message = $this->context->cart->gift_message;
            $order->mobile_theme = false;
            $order->conversion_rate = $this->context->currency->conversion_rate;

            $totalProducts = 0;
            $totalProductsWt = 0;
            $productList = array();
            foreach ($package['product_list'] as &$product) {
                $sku = $product['id_product'];
                $sku .= empty($product['id_product_attribute']) ? '' : '_'.$product['id_product_attribute'];
                if (isset($lengowProducts[$sku])) {
                    $product['price_wt'] = $lengowProducts[$sku]['price_unit'];
                    $product['price'] = Tools::ps_round(
                        LengowProduct::calculatePriceWithoutTax(
                            $product,
                            $order->{Configuration::get('PS_TAX_ADDRESS_TYPE')},
                            $this->context
                        ),
                        2
                    );
                    $product['total'] = (float)$product['price'] * (int)$product['quantity'];
                    $product['total_wt'] = Tools::ps_round((float)$product['price_wt'] * (int)$product['quantity'], 2);
                    // total tax free
                    $totalProducts += $product['total'];
                    // total with taxes
                    $totalProductsWt += $product['total_wt'];
                } else {
                    throw new LengowException(
                        LengowMain::setLogMessage(
                            'lengow_log.exception.product_is_not_listed',
                            array('product_id' => $sku)
                        )
                    );
                }
            }

            $order->product_list = $package['product_list'];
            $order->total_products = (float)Tools::ps_round($totalProducts, 2);
            $order->total_products_wt = (float)Tools::ps_round($totalProductsWt, 2);
            $order->total_paid_real = 0;
            $order->total_discounts_tax_excl = 0;
            $order->total_discounts_tax_incl = 0;
            $order->total_discounts = $order->total_discounts_tax_incl;

            // calculate shipping tax free
            $order->carrier_tax_rate = $carrier->getTaxesRate(
                new Address($this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')})
            );
            $totalShippingTaxExcl = $lengowShippingCosts / (1 + ($order->carrier_tax_rate / 100));

            $order->total_shipping_tax_excl = (float)Tools::ps_round($totalShippingTaxExcl / (int)$nbPackage, 2);
            $order->total_shipping_tax_incl = (float)Tools::ps_round($lengowShippingCosts / (int)$nbPackage, 2);
            $order->total_shipping = $order->total_shipping_tax_incl;

            if (!is_null($lengowTrackingNumber)) {
                $order->shipping_number = (string)$lengowTrackingNumber;
            }

            // add processing fees to wrapping fees
            $taxManager = TaxManagerFactory::getManager(
                new LengowAddress($idAddress),
                (int)Configuration::get('PS_GIFT_WRAPPING_TAX_RULES_GROUP')
            );
            $taxCalculator = $taxManager->getTaxCalculator();
            $order->total_wrapping_tax_excl = (float)Tools::ps_round(
                $taxCalculator->removeTaxes((float)$processingFees),
                2
            );
            $order->total_wrapping_tax_incl = (float)$processingFees;
            $order->total_wrapping = $order->total_wrapping_tax_incl;

            $precision = 2;
            if (defined('_PS_PRICE_COMPUTE_PRECISION_')) {
                $precision = _PS_PRICE_COMPUTE_PRECISION_;
            }

            $order->total_paid_tax_excl = (float)Tools::ps_round(
                (float)$totalProducts + (float)$totalShippingTaxExcl + (float)$order->total_wrapping_tax_excl,
                $precision
            );
            $order->total_paid_tax_incl = (float)Tools::ps_round(
                (float)$totalProductsWt + (float)$order->total_shipping_tax_incl + (float)$processingFees,
                $precision
            );
            $order->total_paid = $order->total_paid_tax_incl;
            $order->round_mode = Configuration::get('PS_PRICE_ROUND_MODE');
            $order->invoice_date = '0000-00-00 00:00:00';
            $order->delivery_date = '0000-00-00 00:00:00';

            // Creating order
            $result = $order->add();

            if (!$result) {
                if (_PS_VERSION_ >= '1.6') {
                    PrestaShopLogger::addLog(
                        'PaymentModule::validateOrder - Order cannot be created',
                        3,
                        null,
                        'Cart',
                        (int)$idCart,
                        true
                    );
                }
                throw new LengowException(
                    LengowMain::setLogMessage(
                        'lengow_log.exception.unable_to_save_order',
                        array('error' => Db::getInstance()->getMsgError())
                    )
                );
            }

            $orderList[] = $order;

            // Insert new Order detail list using cart for the current order
            $orderDetail = new LengowOrderDetail(null, null, $this->context);
            if ($packageList[$idAddress][$idPackage]['id_warehouse'] != '') {
                $orderDetail->createList(
                    $order,
                    $this->context->cart,
                    $idOrderState,
                    $order->product_list,
                    0,
                    true,
                    $packageList[$idAddress][$idPackage]['id_warehouse']
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

            // Adding an entry in order_carrier table
            if (!is_null($carrier)) {
                $orderCarrier = new OrderCarrier();
                $orderCarrier->id_order = (int)$order->id;
                $orderCarrier->id_carrier = (int)$idCarrier;
                $orderCarrier->weight = (float)$order->getTotalWeight();
                $orderCarrier->shipping_cost_tax_excl = (float)$order->total_shipping_tax_excl;
                $orderCarrier->shipping_cost_tax_incl = (float)$order->total_shipping_tax_incl;
                if (!is_null($lengowTrackingNumber)) {
                    $orderCarrier->tracking_number = (string)$lengowTrackingNumber;
                }
                $orderCarrier->validateFields();
                $orderCarrier->add();
            }
        }

        // Register Payment only if the order status validate the order
        if ($orderStatus->logable) {
            $idTransaction = null;
            if (!$order->addOrderPayment($order->total_paid_tax_incl, null, $idTransaction)) {
                if (_PS_VERSION_ >= '1.6') {
                    PrestaShopLogger::addLog(
                        'PaymentModule::validateOrder - Cannot save Order Payment',
                        3,
                        null,
                        'Cart',
                        (int)$idCart,
                        true
                    );
                }
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
                        $msg->id_order = (int)$order->id;
                        $msg->private = 1;
                        $msg->add();
                    }
                }
                // Specify order id for message
                $oldMessage = Message::getMessageByCartId((int)$this->context->cart->id);
                if ($oldMessage) {
                    $updateMessage = new Message((int)$oldMessage['id_message']);
                    $updateMessage->id_order = (int)$order->id;
                    $updateMessage->update();

                    // Add this message in the customer thread
                    $customerThread = new CustomerThread();
                    $customerThread->id_contact = 0;
                    $customerThread->id_customer = (int)$order->id_customer;
                    $customerThread->id_shop = (int)$this->context->shop->id;
                    $customerThread->id_order = (int)$order->id;
                    $customerThread->id_lang = (int)$this->context->language->id;
                    $customerThread->email = $this->context->customer->email;
                    $customerThread->status = 'open';
                    $customerThread->token = Tools::passwdGen(12);
                    $customerThread->add();

                    $customerMessage = new CustomerMessage();
                    $customerMessage->id_customer_thread = $customerThread->id;
                    $customerMessage->id_employee = 0;
                    $customerMessage->message = $updateMessage->message;
                    $customerMessage->private = 0;

                    if (!$customerMessage->add()) {
                        $this->errors[] = Tools::displayError('An error occurred while saving message');
                    }
                }
                
                foreach ($this->context->cart->getProducts() as $product) {
                    if ($orderStatus->logable) {
                        ProductSale::addProductSale((int)$product['id_product'], (int)$product['cart_quantity']);
                    }
                }

                // Set the order status
                $newHistory = new OrderHistory();
                $newHistory->id_order = (int)$order->id;
                $newHistory->changeIdOrderState((int)$idOrderState, $order, true);
                $newHistory->addWithemail(true, null);


                // Switch to back order if needed
                if (Configuration::get('PS_STOCK_MANAGEMENT') && $orderDetail->getStockState()) {
                    $history = new OrderHistory();
                    $history->id_order = (int)$order->id;
                    if (version_compare(_PS_VERSION_, '1.6.0.11', '<')) {
                        $history->changeIdOrderState(Configuration::get('PS_OS_OUTOFSTOCK'), $order, true);
                    }

                    $history->changeIdOrderState(
                        configuration::get($order->valid ? 'PS_OS_OUTOFSTOCK_PAID' : 'PS_OS_OUTOFSTOCK_UNPAID'),
                        $order,
                        true
                    );
                    $history->addWithemail();
                }

                unset($orderDetail);

                // Order is reloaded because the status just changed
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
        } // End foreach $orderDetail

        // Update Order Details Tax in case cart rules have free shipping
        foreach ($order->getOrderDetailList() as $detail) {
            $orderDetail = new OrderDetail($detail['id_order_detail']);
            $orderDetail->updateTaxAmount($order);
        }

        // Use the last order as currentOrder
        if (isset($order) && $order->id) {
            $this->currentOrder = (int)$order->id;
        }

        return $orderList;
    }
  
    /**
     * Create Prestashop order for 1.4 version
     * Overrides PaymentModule::validateOrder() for 1.4 version
     *
     * @param integer $idCart               Prestashop cart id
     * @param integer $idOrderState         Prestashop order state id
     * @param float   $amountPaid           total amount paid
     * @param string  $paymentMethod        name of the payment method
     * @param string  $message              order message 
     * @param array   $lengowProducts       list of Lengow products
     * @param float   $lengowShippingCosts  order shipping costs
     * @param float   $processingFees       order processing fees
     * @param string  $lengowTrackingNumber Lengow carrier tracking number
     *
     * @throws Exception product is not listed / cannot load order status / cart cannot be loaded 
     *                   order creation failed
     * @return array
     */
    public function makeOrder14(
        $idCart,
        $idOrderState,
        $amountPaid,
        $paymentMethod,
        $message,
        $lengowProducts,
        $lengowShippingCosts,
        $processingFees = null,
        $lengowTrackingNumber = null
    ) {
        if (!isset($this->context)) {
            $this->context = Context::getContext();
        }
        $this->context->cart = new Cart($idCart);
        $this->context->customer = new Customer($this->context->cart->id_customer);
        $this->context->language = new Language($this->context->cart->id_lang);

        // Does order already exists ?
        if (Validate::isLoadedObject($this->context->cart) && $this->context->cart->OrderExists() == false) {
            // Copying data from cart
            $order = new Order();
            $order->id_carrier = (int)$this->context->cart->id_carrier;
            $order->id_customer = (int)$this->context->cart->id_customer;
            $order->id_address_invoice = (int)$this->context->cart->id_address_invoice;
            $order->id_address_delivery = (int)$this->context->cart->id_address_delivery;
            $order->id_currency = (int)$this->context->cart->id_currency;
            $order->id_lang = (int)$this->context->cart->id_lang;
            $order->id_cart = (int)$this->context->cart->id;
            $order->secure_key = pSQL($this->context->customer->secure_key);
            $order->payment = $paymentMethod;
            if (isset($this->name)) {
                $order->module = $this->name;
            }
            $order->recyclable = $this->context->cart->recyclable;
            $order->gift = (int)$this->context->cart->gift;
            $order->gift_message = $this->context->cart->gift_message;
            $currency = new Currency($order->id_currency);
            $order->conversion_rate = $currency->conversion_rate;

            $totalProducts = 0;
            $totalProductsWt = 0;
            $products = $this->context->cart->getProducts();
            $productList = array();
            foreach ($products as &$product) {
                $sku = $product['id_product'];
                $sku .= empty($product['id_product_attribute']) ? '' : '_'.$product['id_product_attribute'];
                if (isset($lengowProducts[$sku])) {
                    $product['price_wt'] = $lengowProducts[$sku]['price_unit'];
                    $product['price'] = Tools::ps_round(
                        LengowProduct::calculatePriceWithoutTax(
                            $product,
                            $order->{Configuration::get('PS_TAX_ADDRESS_TYPE')},
                            $this->context
                        ),
                        2
                    );
                    $product['total'] = (float)$product['price'] * (int)$product['quantity'];
                    $product['total_wt'] = Tools::ps_round((float)$product['price_wt'] * (int)$product['quantity'], 2);

                    // total tax free
                    $totalProducts += (float)$product['total'];
                    // total with taxes
                    $totalProductsWt += $product['total_wt'];

                    $productList[] = $product;
                } else {
                    throw new LengowException(
                        LengowMain::setLogMessage(
                            'lengow_log.exception.product_is_not_listed',
                            array('product_id' => $sku)
                        )
                    );
                }
            }
            $order->total_products = (float)Tools::ps_round($totalProducts, 2);
            $order->total_products_wt = (float)Tools::ps_round($totalProductsWt, 2);
            $order->total_paid_real = (float)$amountPaid;

            // put marketplace processing fees into wrapping
            $order->total_wrapping = (float)$processingFees;

            $order->total_shipping = (float)$lengowShippingCosts;
            if (!is_null($lengowTrackingNumber)) {
                $order->shipping_number = (string)$lengowTrackingNumber;
            }
            $order->carrier_tax_rate = (float)Tax::getCarrierTaxRate(
                $this->context->cart->id_carrier,
                (int)$this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}
            );
            $order->total_paid = (float)$amountPaid;
            $order->invoice_date = '0000-00-00 00:00:00';
            // Creating order
            if ($this->context->cart->OrderExists() == false) {
                $result = $order->add();
            } else {
                $errorMessage = Tools::displayError('An order has already been placed using this cart.');
                Logger::addLog($errorMessage, 4, '0000001', 'Cart', (int)$order->id_cart);
                die($errorMessage);
            }
            // Next !
            if ($result && isset($order->id)) {
                if (!$order->secure_key) {
                    $message .= $this->l(
                        'Warning : the secure key is empty, check your payment account before validation'
                    );
                }
                // Optional message to attach to this order
                if (isset($message) && !empty($message)) {
                    $msg = new Message();
                    $message = strip_tags($message, '<br>');
                    if (Validate::isCleanHtml($message)) {
                        $msg->message = $message;
                        $msg->id_order = (int)$order->id;
                        $msg->private = 1;
                        $msg->add();
                    }
                }
                // Insert products from cart into order_detail table
                $db = Db::getInstance();
                $query = 'INSERT INTO `'._DB_PREFIX_.'order_detail`
					(`id_order`,
                    `product_id`,
                    `product_attribute_id`,
                    `product_name`,
                    `product_quantity`,
                    `product_quantity_in_stock`,
                    `product_price`,
                    `reduction_percent`,
                    `reduction_amount`,
                    `group_reduction`,
                    `product_quantity_discount`,
                    `product_ean13`,
                    `product_upc`,
                    `product_reference`,
                    `product_supplier_reference`,
                    `product_weight`,
                    `tax_name`,
                    `tax_rate`,
                    `ecotax`,
                    `ecotax_tax_rate`,
                    `discount_quantity_applied`,
                    `download_deadline`,
                    `download_hash`)
				VALUES ';
                $outOfStock = false;
                foreach ($productList as $product) {
                    $productQuantity = (int)(Product::getQuantity(
                        (int)($product['id_product']),
                        ($product['id_product_attribute'] ? (int)($product['id_product_attribute']) : null)
                    ));
                    $quantityInStock = ($productQuantity - (int)($product['cart_quantity']) < 0)
                        ? $productQuantity
                        : (int)($product['cart_quantity']);
                    if ($idOrderState != _PS_OS_CANCELED_ && $idOrderState != _PS_OS_ERROR_) {
                        if (Product::updateQuantity($product, (int)$order->id)) {
                            $product['stock_quantity'] -= $product['cart_quantity'];
                        }
                        if ($product['stock_quantity'] < 0 && Configuration::get('PS_STOCK_MANAGEMENT')) {
                            $outOfStock = true;
                        }
                        Product::updateDefaultAttribute($product['id_product']);
                    }

                    // Add some informations for virtual products
                    $deadline = '0000-00-00 00:00:00';
                    $downloadHash = null;
                    if ($idProductDownload = ProductDownload::getIdFromIdProduct((int)($product['id_product']))) {
                        $productDownload = new ProductDownload((int)($idProductDownload));
                        $deadline = $productDownload->getDeadLine();
                        $downloadHash = $productDownload->getHash();
                    }
                    // Exclude VAT
                    if (Tax::excludeTaxeOption()) {
                        $product['tax'] = 0;
                        $product['rate'] = 0;
                        $taxRate = 0;
                    } else {
                        $taxRate = Tax::getProductTaxRate(
                            (int)($product['id_product']),
                            $this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}
                        );
                    }

                    $ecotaxTaxRate = 0;
                    if (!empty($product['ecotax'])) {
                        $ecotaxTaxRate = Tax::getProductEcotaxRate($order->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
                    }
                    $productName = (isset($product['attributes']) && $product['attributes'] != null)
                        ? ' - '.$product['attributes']
                        : '';
                    $query .= '('.(int)($order->id).',
						'.(int)($product['id_product']).',
						'.(isset($product['id_product_attribute'])
                            ? (int)($product['id_product_attribute'])
                            : 'null'
                        ).',
						\''.pSQL($product['name'].$productName).'\',
						'.(int)($product['cart_quantity']).',
						'.$quantityInStock.',
						'.(float)$product['price'].',
						'.pSQL('0.00').',
						'.(float)(Group::getReduction((int)($order->id_customer))).',
						'.pSQL('0.00').',
						'.pSQL('0').',
						'.(empty($product['ean13']) ? 'null' : '\''.pSQL($product['ean13']).'\'').',
						'.(empty($product['upc']) ? 'null' : '\''.pSQL($product['upc']).'\'').',
						'.(empty($product['reference']) ? 'null' : '\''.pSQL($product['reference']).'\'') . ',
						'.(empty($product['supplier_reference'])
                            ? 'null'
                            : '\''.pSQL($product['supplier_reference']).'\''
                        ).',
						'.(float)($product['id_product_attribute']
                            ? $product['weight_attribute']
                            : $product['weight']
                        ).',
						\''.(empty($taxRate) ? '' : pSQL($product['tax'])).'\',
						'.(float)($taxRate).',
						'.(float)Tools::convertPrice((float)$product['ecotax'], (int)$order->id_currency).',
						'.(float)$ecotaxTaxRate.',
						'.pSQL('0').',
						\''.pSQL($deadline).'\',
						\''.pSQL($downloadHash).'\'),';
                } // end foreach ($products)
                $query = rtrim($query, ',');
                $result = $db->Execute($query);
                // Specify order id for message
                $oldMessage = Message::getMessageByCartId((int)($this->context->cart->id));
                if ($oldMessage) {
                    $message = new Message((int)$oldMessage['id_message']);
                    $message->id_order = (int)$order->id;
                    $message->update();
                }
                
                $orderStatus = new OrderState((int)$idOrderState, (int)$order->id_lang);
                if (Validate::isLoadedObject($orderStatus)) {
                    $products = $this->context->cart->getProducts();
                    foreach ($products as $product) {
                        if ($orderStatus->logable) {
                            ProductSale::addProductSale((int)$product['id_product'], (int)$product['cart_quantity']);
                        }
                    }
                } else {
                    throw new LengowException(
                        LengowMain::setLogMessage('lengow_log.exception.cannot_load_order_status')
                    );
                }

                if (isset($outOfStock) && $outOfStock) {
                    $history = new OrderHistory();
                    $history->id_order = (int)$order->id;
                    $history->changeIdOrderState(_PS_OS_OUTOFSTOCK_, (int)$order->id);
                    $history->save();
                }

                // Set order state in order history ONLY even if the "out of stock" status has not been yet reached
                // So you migth have two order states
                $newHistory = new OrderHistory();
                $newHistory->id_order = (int)$order->id;
                $newHistory->changeIdOrderState((int)$idOrderState, (int)$order->id);
                $newHistory->save();
                // Order is reloaded because the status just changed
                $order = new Order($order->id);

                $this->currentOrder = (int)($order->id);

                return array($order);
            } else {
                throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.order_creation_failed'));
            }
        } else {
            throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.cart_cannot_be_loaded'));
        }
    }

    /**
     * Set context for payment module
     *
     * @param Context $context Prestashop context instance
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }
}
