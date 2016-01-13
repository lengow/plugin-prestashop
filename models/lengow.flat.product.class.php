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
 * The Lengow Product Class.
 *
 */
class LengowFlatProduct extends Product
{


    var $price_shipping;

    /**
     * Load a new product.
     *
     * @param integer $id_product The ID product to load
     * @param integer $id_lang The ID lang for product's content
     * @param object $context The context
     */
    public function __construct($product, $params = array())
    {



        $this->context = $params["context"];
        $this->carrier = $params["carrier"];
        $this->productTaxes = $params["productTaxes"];
        $this->currency = $params["currency"];

        //parent::__construct($id_product, false, $id_lang);
        //var_dump($id_product);

        //todo: parent ?
        $this->id_product = $product["id_product"];
        if ($product["id_product_attribute"] != '') {
            $this->id_product = $product["id_product"]."_".$product["id_product_attribute"];
            $this->id_parent = $product["id_product"];
            $this->product_type = 'child';
        } else {
            $this->product_type = 'simple';
        }

        $this->name_product = $product["name"];
        $this->description = $product["description"];
        $this->description_html = $product["description"];
        $this->reference = $product["reference"];
        $this->supplier_reference = $product["supplier_reference"];
        $this->manufacturer_name = $product["manufacturer_name"];
        $this->supplier = $product["supplier_name"];
        $this->description_short = $product["description_short"];
        $this->meta_keywords = $product["meta_keywords"];
        $this->meta_description = $product["meta_description"];

        $this->quantity = ($product["sa_quantity"] != '') ? $product["sa_quantity"] : $product["p_quantity"];

        //product active (SHOP > PRODUCT)
        $this->visibility = ($product["pshop_visibility"] != '') ? $product["pshop_visibility"] : $product["p_visibility"];
        //product active (SHOP > PRODUCT)
        $this->active = ($product["pshop_active"] != '') ? $product["pshop_active"] : $product["p_active"];
        //product weight (ATTRIBUTE > PRODUCT)
        $this->weight = ($product["pa_weight"] != 0) ? $product["p_weight"] + $product["pa_weight"] : $product["p_weight"];
        //product EAN (ATTRIBUTE > PRODUCT)
        $this->ean = ($product["pa_ean"] != '') ? $product["pa_ean"] : $product["p_ean"];
        if ($this->ean==0) {
            $this->ean = '';
        }
        //product UPC (ATTRIBUTE > PRODUCT)
        $this->upc = ($product["pa_upc"] != '') ? $product["pa_upc"] : $product["p_upc"];
        if ($this->upc==0) {
            $this->upc = '';
        }
        //product ecotax (ATTRIBUTE SHOP > SHOP > ATTRIBUTE > PRODUCT)
        if ($product["pas_ecotax"] >0) {
            $this->ecotax = $product["pas_ecotax"];
        } elseif ($product["pshop_ecotax"] > 0) {
            $this->ecotax = $product["pshop_ecotax"];
        } elseif ($product["pa_ecotax"] > 0) {
            $this->ecotax = $product["pa_ecotax"];
        } else {
            $this->ecotax = $product["p_ecotax"];
        }
        //product minimal quantity (ATTRIBUTE SHOP > SHOP > ATTRIBUTE > PRODUCT)
        if ($product["pas_minimal_quantity"] >0) {
            $this->minimal_quantity = $product["pas_minimal_quantity"];
        } elseif ($product["pshop_minimal_quantity"] > 0) {
            $this->minimal_quantity = $product["pshop_minimal_quantity"];
        } elseif ($product["pa_minimal_quantity"] > 0) {
            $this->minimal_quantity = $product["pa_minimal_quantity"];
        } else {
            $this->minimal_quantity = $product["p_minimal_quantity"];
        }
        $this->url_rewrite = $product["link_rewrite"];
        $this->url_product = $this->context->link->getProductLink($this->id_product, $this->url_rewrite,  null, null, null, null, $this->id_parent);

        //$this->quantity = ($product["pa_quantity"] != '') ? $product["pa_quantity"] : $product["quantity"];
        $this->reference = ($product["pa_reference"] != '') ? $product["pa_reference"] : $product["reference"];
        $this->supplier_reference = ($product["pa_supplier_reference"] != '') ? $product["pa_supplier_reference"] : $product["supplier_reference"];

        $this->is_virtual = $product['is_virtual'];
        $this->price = 10;
        $this->getShippingPrice();

        //todo : load out of stock
        $this->availability = $this->quantity > 0 ? 1 : 0;
        if ($this->quantity < 0 && !Product::isAvailableWhenOutOfStock($this->out_of_stock)) {
            $this->availability = 0;
        }



//        $context = Context::getContext();
//
//        // Need to get price Product::getPriceStatic
//        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'employee WHERE active = 1 LIMIT 1';
//        $findEmployee = Db::getInstance()->ExecuteS($sql);
//        if ($findEmployee) {
//            $context->employee = $findEmployee;
//        } else {
//            throw new LengowExportException('No Active Employee Fund');
//        }
//
//
//
//        // The applicable tax may be BOTH the product one AND the state one (moreover this variable is some deadcode)
//        $this->tax_name = 'deprecated';
//
//        $this->manufacturer_name = Manufacturer::getNameById((int)$this->id_manufacturer);
//        $this->supplier_name = Supplier::getNameById((int)$this->id_supplier);
//        $address = null;
//        if (is_object($context->cart) && $context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')} != null) {
//            $address = $context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
//        }
//        if (LengowMain::compareVersion()) {
//            $this->tax_rate = $this->getTaxesRate(new Address($address));
//        } else {
//            $cart = Context::getContext()->cart;
//            if (is_object($cart) && $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')} != null) {
//                $this->tax_rate = Tax::getProductTaxRate($this->id, $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
//            } else {
//                $this->tax_rate = Tax::getProductTaxRate($this->id, null);
//            }
//        }
//        $this->new = $this->isNew();
//        $this->base_price = $this->price;
//        if ($this->id) {
//            $this->price = Product::getPriceStatic(
//                (int)$this->id,
//                false,
//                null,
//                2,
//                null,
//                false,
//                true,
//                1,
//                false,
//                null,
//                null,
//                null,
//                $this->specificPrice
//            );
//
//            $this->unit_price = ($this->unit_price_ratio != 0 ? $this->price / $this->unit_price_ratio : 0);
//        }
//        if (LengowMain::compareVersion()) {
//            $this->loadStockData();
//        }
//        if ($this->id_category_default && $this->id_category_default > 1) {
//            $this->category_default = new Category((int)$this->id_category_default, $id_lang);
//            $this->category_name = $this->category_default->name;
//        } else {
//            $categories = self::getProductCategories($this->id);
//            if (!empty($categories)) {
//                $this->category_default = new Category($categories[0], $id_lang);
//                $this->category_name = $this->category_default->name;
//            }
//        }
//        $images = $this->getImages($id_lang);
//        $array_images = array();
//        foreach ($images as $image) {
//            if ($image['cover']) {
//                $this->cover = $image;
//            } else {
//                $array_images[] = $image;
//            }
//        }
//        $this->images = $array_images;
//        $today = date('Y-m-d H:i:s');
//        if (isset($this->specificPrice) && is_array($this->specificPrice)) {
//            if (array_key_exists('from', $this->specificPrice) && array_key_exists('to', $this->specificPrice)) {
//                if ($this->specificPrice['from'] <= $today && $today <= $this->specificPrice['to']) {
//                    $this->is_sale = true;
//                }
//            }
//        }
//        $this->makeFeatures($context);
//        $this->makeAttributes($context);
    }

    private function getShippingPrice()
    {

        $context = Context::getContext();

        $id_zone = $context->country->id_zone;
        $id_currency = $context->cart->id_currency;
        $shipping_method = $this->carrier->getShippingMethod();
        $shipping_cost = 0;
        if (!defined('Carrier::SHIPPING_METHOD_FREE') || $shipping_method != Carrier::SHIPPING_METHOD_FREE) {
            if ($shipping_method == Carrier::SHIPPING_METHOD_WEIGHT) {
                $shipping_cost = LengowMain::formatNumber(
                    $this->carrier->getDeliveryPriceByWeight($this->weight, (int)$id_zone)
                );
            } else {
                $shipping_cost = LengowMain::formatNumber(
                    $this->carrier->getDeliveryPriceByPrice(
                        $this->price,
                        (int)$id_zone,
                        (int)$id_currency
                    )
                );
            }
            // Check if product have single shipping cost
            if ($this->additional_shipping_cost > 0) {
                $shipping_cost += $this->additional_shipping_cost;
            }
        }

        if ($this->productTaxes) {
            $tax_calculator = new TaxCalculator(array($this->productTaxes));
            $taxes = $tax_calculator->getTaxesAmount($shipping_cost);
            if (!empty($taxes)) {
                foreach ($taxes as $tax) {
                    $shipping_cost += $tax;
                }
            }
        }

        $this->price_shipping = $shipping_cost;
    }
}
