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

/**
 * The Lengow Payment Class.
 *
 */
class LengowPaymentModule extends PaymentModule
{

    /**
     * @var string Lengow Payment name
     */
    public $name = 'lengow_payment';

    /**
     * Overrides PaymentModule::validateOrder()
     *
     * @param integer   $id_cart
     * @param integer   $id_order_state
     * @param string    $payment_method
     * @param string    $message
     * @param array     $lengow_products
     * @param float     $lengow_shipping_costs
     * @param float     $processing_fees
     * @param string    $lengow_tracking_number
     *
     * @return array
     */
    public function makeOrder(
        $id_cart,
        $id_order_state,
        $payment_method,
        $message,
        $lengow_products,
        $lengow_shipping_costs,
        $processing_fees = null,
        $lengow_tracking_number = null
    ) {
        if (!isset($this->context)) {
            $this->context = Context::getContext();
        }
        $this->context->cart = new Cart($id_cart);
        $this->context->customer = new Customer($this->context->cart->id_customer);
        // The tax cart is loaded before the customer so re-cache the tax calculation method
        if (method_exists($this->context->cart, 'setTaxCalculationMethod')) {
            $this->context->cart->setTaxCalculationMethod();
        }
        if (method_exists(new ShopUrl(), 'resetMainDomainCache')) {
            ShopUrl::resetMainDomainCache();
        }

        $id_currency = (int)$this->context->cart->id_currency;
        $this->context->currency = new Currency($id_currency, null, $this->context->shop->id);

        $order_status = new OrderState((int)$id_order_state, (int)$this->context->language->id);
        if (!Validate::isLoadedObject($order_status)) {
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
        $delivery_option_list = $this->context->cart->getDeliveryOptionList(null, true);
        $package_list = $this->context->cart->getPackageList(true);
        $cart_delivery_option = $this->context->cart->getDeliveryOption(null, true, false);

        foreach ($delivery_option_list as $id_address => $package) {
            if (!isset($cart_delivery_option[$id_address])
                || !array_key_exists($cart_delivery_option[$id_address], $package)
            ) {
                foreach ($package as $key => $val) {
                    // This line is useless, but Prestashop validator require it
                    $val = $val;
                    // force carrier to be the one chosen in Lengow config
                    $carrier_options = explode(',', $key);
                    $carrier_assigned = false;
                    foreach ($carrier_options as $c) {
                        if ($c === $this->context->cart->id_carrier) {
                            $cart_delivery_option[$id_address] = $key;
                            $carrier_assigned = true;
                            break;
                        }
                    }
                    if ($carrier_assigned) {
                        break;
                    }
                }
                // else take 1st valid option
                if (!$carrier_assigned) {
                    foreach ($package as $key => $val) {
                        $cart_delivery_option[$id_address] = $key;
                        break;
                    }
                }
            }
        }

        $order_list = array();
        $order_detail_list = array();

        $stop = false;
        do {
            $reference = Order::generateReference();
            $stop = Order::getByReference($reference)->count() == 0;
        } while (!$stop);

        $this->currentOrderReference = $reference;

        $order_creation_failed = false;
        foreach ($cart_delivery_option as $id_address => $key_carriers) {
            foreach ($delivery_option_list[$id_address][$key_carriers]['carrier_list'] as $id_carrier => $data) {
                foreach ($data['package_list'] as $id_package) {
                    if ($id_carrier != $this->context->cart->id_carrier) {
                        $id_carrier =  $this->context->cart->id_carrier;
                    }
                    // Rewrite the id_warehouse
                    if (method_exists($this->context->cart, 'getPackageIdWarehouse')) {
                        $id_warehouse = (int)$this->context->cart->getPackageIdWarehouse(
                            $package_list[$id_address][$id_package],
                            (int)$id_carrier
                        );
                        $package_list[$id_address][$id_package]['id_warehouse'] = $id_warehouse;
                    }
                    $package_list[$id_address][$id_package]['id_carrier'] = $id_carrier;
                }
            }
        }

        CartRule::cleanCache();

        foreach ($package_list as $id_address => $packageByAddress) {
            $nb_package = count($packageByAddress);
        }
        foreach ($packageByAddress as $id_package => $package) {
            $order = new Order();

            if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery') {
                $address = new Address($id_address);
                $this->context->country = new Country($address->id_country, $this->context->cart->id_lang);
                if (!$this->context->country->active) {
                    throw new LengowException(
                        LengowMain::setLogMessage('lengow_log.exception.delivery_country_not_active')
                    );
                }
            }

            $carrier = new Carrier($package['id_carrier'], $this->context->cart->id_lang);
            $order->id_carrier = (int)$carrier->id;
            $id_carrier = (int)$carrier->id;

            $order->id_customer = (int)$this->context->cart->id_customer;
            $order->id_address_invoice = (int)$this->context->cart->id_address_invoice;
            $order->id_address_delivery = (int)$id_address;
            $order->id_currency = $this->context->currency->id;
            $order->id_lang = (int)$this->context->cart->id_lang;
            $order->id_cart = (int)$this->context->cart->id;
            $order->reference = $reference;
            $order->id_shop = (int)$this->context->shop->id;
            $order->id_shop_group = (int)$this->context->shop->id_shop_group;

            $order->secure_key = pSQL($this->context->customer->secure_key);
            $order->payment = $payment_method;
            if (isset($this->name)) {
                $order->module = $this->name;
            }
            $order->recyclable = $this->context->cart->recyclable;
            $order->gift = (int)$this->context->cart->gift;
            $order->gift_message = $this->context->cart->gift_message;
            $order->mobile_theme = false;
            $order->conversion_rate = $this->context->currency->conversion_rate;

            $total_products = 0;
            $total_products_wt = 0;
            $product_list = array();
            foreach ($package['product_list'] as &$product) {
                $sku = $product['id_product'];
                $sku .= empty($product['id_product_attribute']) ? '' : '_'.$product['id_product_attribute'];
                if (isset($lengow_products[$sku])) {
                    $product['price_wt'] = $lengow_products[$sku]['price_unit'];
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
                    $total_products += $product['total'];
                    // total with taxes
                    $total_products_wt += $product['total_wt'];
                } else {
                    throw new LengowException(
                        LengowMain::setLogMessage('lengow_log.exception.product_is_not_listed', array(
                            'product_id' => $sku
                        ))
                    );
                }
            }

            $order->product_list = $package['product_list'];
            $order->total_products = (float)Tools::ps_round($total_products, 2);
            $order->total_products_wt = (float)Tools::ps_round($total_products_wt, 2);
            $order->total_paid_real = 0;

            // calculate shipping tax free
            $order->carrier_tax_rate = $carrier->getTaxesRate(
                new Address($this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')})
            );
            $total_shipping_tax_excl = $lengow_shipping_costs / (1 + ($order->carrier_tax_rate / 100));

            $order->total_shipping_tax_excl = (float)Tools::ps_round($total_shipping_tax_excl / (int)$nb_package, 2);
            $order->total_shipping_tax_incl = (float)Tools::ps_round($lengow_shipping_costs / (int)$nb_package, 2);
            $order->total_shipping = $order->total_shipping_tax_incl;

            if (!is_null($lengow_tracking_number)) {
                $order->shipping_number = (string)$lengow_tracking_number;
            }

            // add processing fees to wrapping fees
            $tax_manager = TaxManagerFactory::getManager(
                new LengowAddress($id_address),
                (int)Configuration::get('PS_GIFT_WRAPPING_TAX_RULES_GROUP')
            );
            $tax_calculator = $tax_manager->getTaxCalculator();
            $order->total_wrapping_tax_excl = $tax_calculator->removeTaxes((float)$processing_fees);
            $order->total_wrapping_tax_incl = (float)$processing_fees;
            $order->total_wrapping = $order->total_wrapping_tax_incl;

            $precision = 2;
            if (defined('_PS_PRICE_COMPUTE_PRECISION_')) {
                $precision = _PS_PRICE_COMPUTE_PRECISION_;
            }

            $order->total_paid_tax_excl = (float)Tools::ps_round(
                (float)$total_products + (float)$total_shipping_tax_excl + (float)$order->total_wrapping_tax_excl,
                $precision
            );
            $order->total_paid_tax_incl = (float)Tools::ps_round(
                (float)$total_products_wt + (float)$order->total_shipping_tax_incl + (float)$processing_fees,
                $precision
            );
            $order->total_paid = $order->total_paid_tax_incl;
            $order->round_mode = Configuration::get('PS_PRICE_ROUND_MODE');
            $order->invoice_date = '0000-00-00 00:00:00';
            $order->delivery_date = '0000-00-00 00:00:00';

            // Creating order
            $result = $order->add();

            if (!$result) {
                PrestaShopLogger::addLog(
                    'PaymentModule::validateOrder - Order cannot be created',
                    3,
                    null,
                    'Cart',
                    (int)$id_cart,
                    true
                );
                throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.unable_to_save_order'), array(
                    'error' => Db::getInstance()->getMsgError()
                ));
            }

            $order_list[] = $order;

            // Insert new Order detail list using cart for the current order
            $order_detail = new LengowOrderDetail(null, null, $this->context);
            if ($package_list[$id_address][$id_package]['id_warehouse'] != '') {
                $order_detail->createList(
                    $order,
                    $this->context->cart,
                    $id_order_state,
                    $order->product_list,
                    0,
                    true,
                    $package_list[$id_address][$id_package]['id_warehouse']
                );
            } else {
                $order_detail->createList(
                    $order,
                    $this->context->cart,
                    $id_order_state,
                    $order->product_list,
                    0,
                    true
                );
            }
            $order_detail_list[] = $order_detail;

            // Adding an entry in order_carrier table
            if (!is_null($carrier)) {
                $order_carrier = new OrderCarrier();
                $order_carrier->id_order = (int)$order->id;
                $order_carrier->id_carrier = (int)$id_carrier;
                $order_carrier->weight = (float)$order->getTotalWeight();
                $order_carrier->shipping_cost_tax_excl = (float)$order->total_shipping_tax_excl;
                $order_carrier->shipping_cost_tax_incl = (float)$order->total_shipping_tax_incl;
                if (!is_null($lengow_tracking_number)) {
                    $order_carrier->tracking_number = (string)$lengow_tracking_number;
                }
                $order_carrier->validateFields();
                $order_carrier->add();
            }
        }

        // Register Payment only if the order status validate the order
        if ($order_status->logable) {
            $transaction_id = null;

            if (!$order->addOrderPayment($order->total_paid_tax_incl, null, $transaction_id)) {
                PrestaShopLogger::addLog(
                    'PaymentModule::validateOrder - Cannot save Order Payment',
                    3,
                    null,
                    'Cart',
                    (int)$id_cart,
                    true
                );
                throw new LengowException(
                    LengowMain::setLogMessage('lengow_log.exception.unable_to_save_order_payment')
                );
            }
        }

        foreach ($order_detail_list as $key => $order_detail) {
            $order = $order_list[$key];
            if (!$order_creation_failed && isset($order->id)) {
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
                $old_message = Message::getMessageByCartId((int)$this->context->cart->id);
                if ($old_message) {
                    $update_message = new Message((int)$old_message['id_message']);
                    $update_message->id_order = (int)$order->id;
                    $update_message->update();

                    // Add this message in the customer thread
                    $customer_thread = new CustomerThread();
                    $customer_thread->id_contact = 0;
                    $customer_thread->id_customer = (int)$order->id_customer;
                    $customer_thread->id_shop = (int)$this->context->shop->id;
                    $customer_thread->id_order = (int)$order->id;
                    $customer_thread->id_lang = (int)$this->context->language->id;
                    $customer_thread->email = $this->context->customer->email;
                    $customer_thread->status = 'open';
                    $customer_thread->token = Tools::passwdGen(12);
                    $customer_thread->add();

                    $customer_message = new CustomerMessage();
                    $customer_message->id_customer_thread = $customer_thread->id;
                    $customer_message->id_employee = 0;
                    $customer_message->message = $update_message->message;
                    $customer_message->private = 0;

                    if (!$customer_message->add()) {
                        $this->errors[] = Tools::displayError('An error occurred while saving message');
                    }
                }

                // Hook validate order
                Hook::exec('actionValidateOrder', array(
                    'cart' => $this->context->cart,
                    'order' => $order,
                    'customer' => $this->context->customer,
                    'currency' => $this->context->currency,
                    'orderStatus' => $order_status
                ));

                foreach ($this->context->cart->getProducts() as $product) {
                    if ($order_status->logable) {
                        ProductSale::addProductSale((int)$product['id_product'], (int)$product['cart_quantity']);
                    }
                }

                // Set the order status
                $new_history = new OrderHistory();
                $new_history->id_order = (int)$order->id;
                $new_history->changeIdOrderState((int)$id_order_state, $order, true);
                $new_history->addWithemail(true, null);


                // Switch to back order if needed
                if (Configuration::get('PS_STOCK_MANAGEMENT') && $order_detail->getStockState()) {
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

                unset($order_detail);

                // Order is reloaded because the status just changed
                $order = new Order($order->id);

                // updates stock in shops
                if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                    $product_list = $order->getProducts();
                    foreach ($product_list as $product) {
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
        } // End foreach $order_detail_list

        // Update Order Details Tax in case cart rules have free shipping
        foreach ($order->getOrderDetailList() as $detail) {
            $order_detail = new OrderDetail($detail['id_order_detail']);
            $order_detail->updateTaxAmount($order);
        }

        // Use the last order as currentOrder
        if (isset($order) && $order->id) {
            $this->currentOrder = (int)$order->id;
        }

        return $order_list;
    }
  
    /**
     * Overrides PaymentModule::validateOrder() for 1.4 version
     *
     * @param integer   $id_cart
     * @param integer   $id_order_state
     * @param float     $amount_paid
     * @param string    $payment_method
     * @param string    $message
     * @param array     $lengow_products
     * @param float     $lengow_shipping_costs
     * @param float     $processing_fees
     * @param string    $lengow_tracking_number
     *
     * @return array
     */
    public function makeOrder14(
        $id_cart,
        $id_order_state,
        $amount_paid,
        $payment_method,
        $message,
        $lengow_products,
        $lengow_shipping_costs,
        $processing_fees = null,
        $lengow_tracking_number = null
    ) {
        if (!isset($this->context)) {
            $this->context = Context::getContext();
        }
        $this->context->cart = new Cart($id_cart);
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
            $order->payment = $payment_method;
            if (isset($this->name)) {
                $order->module = $this->name;
            }
            $order->recyclable = $this->context->cart->recyclable;
            $order->gift = (int)$this->context->cart->gift;
            $order->gift_message = $this->context->cart->gift_message;
            $currency = new Currency($order->id_currency);
            $order->conversion_rate = $currency->conversion_rate;

            $total_products = 0;
            $total_products_wt = 0;
            $products = $this->context->cart->getProducts();
            $product_list = array();
            foreach ($products as &$product) {
                $sku = $product['id_product'];
                $sku .= empty($product['id_product_attribute']) ? '' : '_'.$product['id_product_attribute'];
                if (isset($lengow_products[$sku])) {
                    $product['price_wt'] = $lengow_products[$sku]['price_unit'];
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
                    $total_products += (float)$product['total'];
                    // total with taxes
                    $total_products_wt += $product['total_wt'];

                    $product_list[] = $product;
                } else {
                    throw new LengowException(
                        LengowMain::setLogMessage('lengow_log.exception.product_is_not_listed', array(
                            'product_id' => $sku
                        ))
                    );
                }
            }
            $order->total_products = (float)Tools::ps_round($total_products, 2);
            $order->total_products_wt = (float)Tools::ps_round($total_products_wt, 2);
            $order->total_paid_real = (float)$amount_paid;

            // put marketplace processing fees into wrapping
            $order->total_wrapping = (float)$processing_fees;

            $order->total_shipping = (float)$lengow_shipping_costs;
            if (!is_null($lengow_tracking_number)) {
                $order->shipping_number = (string)$lengow_tracking_number;
            }
            $order->carrier_tax_rate = (float)Tax::getCarrierTaxRate(
                $this->context->cart->id_carrier,
                (int)$this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}
            );
            $order->total_paid = (float)$amount_paid;
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
                foreach ($product_list as $product) {
                    $productQuantity = (int)(Product::getQuantity(
                        (int)($product['id_product']),
                        ($product['id_product_attribute'] ? (int)($product['id_product_attribute']) : null)
                    ));
                    $quantityInStock = ($productQuantity - (int)($product['cart_quantity']) < 0)
                        ? $productQuantity
                        : (int)($product['cart_quantity']);
                    if ($id_order_state != _PS_OS_CANCELED_ && $id_order_state != _PS_OS_ERROR_) {
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
                    $download_hash = null;
                    if ($id_product_download = ProductDownload::getIdFromIdProduct((int)($product['id_product']))) {
                        $productDownload = new ProductDownload((int)($id_product_download));
                        $deadline = $productDownload->getDeadLine();
                        $download_hash = $productDownload->getHash();
                    }
                    // Exclude VAT
                    if (Tax::excludeTaxeOption()) {
                        $product['tax'] = 0;
                        $product['rate'] = 0;
                        $tax_rate = 0;
                    } else {
                        $tax_rate = Tax::getProductTaxRate(
                            (int)($product['id_product']),
                            $this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}
                        );
                    }

                    $ecotaxTaxRate = 0;
                    if (!empty($product['ecotax'])) {
                        $ecotaxTaxRate = Tax::getProductEcotaxRate($order->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
                    }
                    $query .= '('.(int)($order->id).',
						'.(int)($product['id_product']).',
						'.(isset($product['id_product_attribute'])
                            ? (int)($product['id_product_attribute'])
                            : 'null'
                        ).',
						\''.pSQL($product['name'].((isset($product['attributes']) && $product['attributes'] != null)
                            ? ' - '.$product['attributes']
                            : ''
                        )).'\',
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
						\''.(empty($tax_rate) ? '' : pSQL($product['tax'])).'\',
						'.(float)($tax_rate).',
						'.(float)Tools::convertPrice((float)$product['ecotax'], (int)$order->id_currency).',
						'.(float)$ecotaxTaxRate.',
						'.pSQL('0').',
						\''.pSQL($deadline).'\',
						\''.pSQL($download_hash).'\'),';
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

                // Hook new order
                $orderStatus = new OrderState((int)$id_order_state, (int)$order->id_lang);
                if (Validate::isLoadedObject($orderStatus)) {
                    Hook::newOrder($this->context->cart, $order, $this->context->customer, $currency, $orderStatus);
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
                $new_history = new OrderHistory();
                $new_history->id_order = (int)$order->id;
                $new_history->changeIdOrderState((int)$id_order_state, (int)$order->id);
                $new_history->save();
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
     * @param Context $context context for import
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }
}
