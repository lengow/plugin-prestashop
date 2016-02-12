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

class LengowCart extends Cart implements LengowObject
{
    /**
     * @var boolean add inactive & out of stock products to cart
     */
    public $force_product = true;

    public static $definition_lengow = array(
        'id_currency' => array('required' => true),
        'id_lang' => array('required' => true),
    );

    /**
     * Add product to cart
     *
     * @param array $products list of products to be added
     *
     * @return boolean
     * @throws Exception
     */
    public function addProducts($products = array())
    {
        $this->lengow_products = $products;
        if (!$products) {
            throw new Exception('no product to be added to cart');
        }
        foreach ($products as $id => $product) {
            $ids = explode('_', $id);
            if (count($ids) > 2) {
                throw new Exception('cannot add product ' . $id . ' to cart (invalid ID format)');
            }

            $id_product = $ids[0];
            $id_product_attribute = isset($ids[1]) ? $ids[1] : null;
            if (!$this->updateQty($product['quantity'], $id_product, $id_product_attribute)) {
                throw new Exception(
                    'product '.$id.' could not be added to cart.'
                    .' Make sure it is available for order or has enough quantity.'
                );
            }
        }
        return true;
    }

    /**
     * @see Cart::updateQty()
     *
     * @param integer $quantity Quantity to add (or substract)
     * @param integer $id_product Product ID
     * @param integer $id_product_attribute Attribute ID if needed
     * @param string $operator Indicate if quantity must be increased or decreased
     *
     * @return boolean
     */
    public function updateQty(
        $quantity,
        $id_product,
        $id_product_attribute = null,
        $id_customization = false,
        $operator = 'up',
        $id_address_delivery = 0,
        Shop $shop = null,
        $auto_add_cart_rule = true
    ) {
        if (!$shop) {
            $shop = Context::getContext()->shop;
        }

        $quantity = (int)$quantity;
        $id_product = (int)$id_product;
        $id_product_attribute = (int)$id_product_attribute;
        $product = new Product($id_product, false, Configuration::get('PS_LANG_DEFAULT'), $shop->id);
        if ($id_product_attribute) {
            $combination = new Combination((int)$id_product_attribute);
            if ($combination->id_product != $id_product) {
                return false;
            }
        }

        /* If we have a product combination, the minimal quantity is set with the one of this combination */
        if (!empty($id_product_attribute)) {
            $minimal_quantity = (int)Attribute::getAttributeMinimalQty($id_product_attribute);
        } else {
            $minimal_quantity = (int)$product->minimal_quantity;
        }
        if (!Validate::isLoadedObject($product)) {
            die(Tools::displayError());
        }

        if (isset(self::$_nbProducts[$this->id])) {
            unset(self::$_nbProducts[$this->id]);
        }

        if (isset(self::$_totalWeight[$this->id])) {
            unset(self::$_totalWeight[$this->id]);
        }
        // if ((int)$quantity <= 0)
        // 	return $this->deleteProduct($id_product, $id_product_attribute, (int)$id_customization);
        // else
        if ((!$product->available_for_order && !$this->force_product) ||
            (Configuration::get('PS_CATALOG_MODE') && !defined('_PS_ADMIN_DIR_'))) {
            return false;
        } else {
            /* Check if the product is already in the cart */
            $result = $this->containsProduct(
                $id_product,
                $id_product_attribute,
                (int)$id_customization,
                (int)$id_address_delivery
            );

            /* Update quantity if product already exist */
            if ($result) {
                // always add product to cart in import
                if (_PS_VERSION_ < '1.5') {
                    $sql = 'SELECT '.(!empty($id_product_attribute) ? 'pa' : 'p').'.`quantity`, p.`out_of_stock`
							FROM `'._DB_PREFIX_.'product` p
							'.(!empty($id_product_attribute) ? 'LEFT JOIN `'._DB_PREFIX_.
                            'product_attribute` pa ON p.`id_product` = pa.`id_product`' : '').'
							WHERE p.`id_product` = '.(int)($id_product).
                        (!empty($id_product_attribute) ?
                            ' AND `id_product_attribute` = ' . (int)$id_product_attribute : '');
                } else {
                    $sql = 'SELECT stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity
							FROM '._DB_PREFIX_.'product p
							'.Product::sqlStock('p', (int)$id_product_attribute, true, $shop).'
							WHERE p.id_product = '.(int)$id_product;
                }

                $result2 = Db::getInstance()->getRow($sql);

                $product_qty = (int)$result2['quantity'];
                // Quantity for product pack
                if (Pack::isPack($id_product)) {
                    $product_qty = Pack::getQuantity($id_product, $id_product_attribute);
                }
                $new_qty = (int)$result['quantity'] + (int)$quantity;
                $qty = '+ '.(int)$quantity;

                // force here
                if (!Product::isAvailableWhenOutOfStock((int)$result2['out_of_stock']) && !$this->force_product) {
                    if ($new_qty > $product_qty) {
                        return false;
                    }
                }

                /* Delete product from cart */
                if ($new_qty <= 0) {
                    return $this->deleteProduct((int)$id_product, (int)$id_product_attribute, (int)$id_customization);
                } elseif ((int)$new_qty < $minimal_quantity && !$this->force_product) {
                    return false;
                } else {
                    if (_PS_VERSION_ < '1.5') {
                        Db::getInstance()->Execute(
                            'UPDATE `'._DB_PREFIX_.'cart_product`
                            SET `quantity` = `quantity` '.$qty.'
                            WHERE `id_product` = '.(int)$id_product.
                            (!empty($id_product_attribute) ?
                                ' AND `id_product_attribute` = '.(int)$id_product_attribute : '').'
                            AND `id_cart` = '.(int)$this->id.'
                            LIMIT 1'
                        );
                    } else {
                        Db::getInstance()->execute(
                            'UPDATE `'._DB_PREFIX_.'cart_product`
                            SET `quantity` = `quantity` '.$qty.', `date_add` = NOW()
                            WHERE `id_product` = '.(int)$id_product.
                            (!empty($id_product_attribute) ?
                                ' AND `id_product_attribute` = '.(int)$id_product_attribute : '').'
                            AND `id_cart` = '.(int)$this->id.
                            (Configuration::get('PS_ALLOW_MULTISHIPPING') && $this->isMultiAddressDelivery()
                                ? ' AND `id_address_delivery` = '. (int)$id_address_delivery : '').'
                            LIMIT 1'
                        );
                    }
                }
            } else {
                if (_PS_VERSION_ < '1.5') {
                    $sql = 'SELECT '.(!empty($id_product_attribute) ? 'pa' : 'p').'.`quantity`, p.`out_of_stock`
							FROM `'._DB_PREFIX_.'product` p
							'.(!empty($id_product_attribute) ?
                            'LEFT JOIN `'._DB_PREFIX_.
                            'product_attribute` pa ON p.`id_product` = pa.`id_product`' : '').'
							WHERE p.`id_product` = '.(int)$id_product .
                        (!empty($id_product_attribute) ?
                            ' AND `id_product_attribute` = '.(int)$id_product_attribute : '');
                } else {
                    $sql = 'SELECT stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity
							FROM '._DB_PREFIX_.'product p
							'.Product::sqlStock('p', (int)$id_product_attribute, true, $shop).'
							WHERE p.id_product = '.(int)$id_product;
                }
                $result2 = Db::getInstance()->getRow($sql);
                // Quantity for product pack
                if (_PS_VERSION_ > '1.4' && Pack::isPack($id_product)) {
                    $result2['quantity'] = Pack::getQuantity($id_product, $id_product_attribute);
                }

                if (!Product::isAvailableWhenOutOfStock((int)$result2['out_of_stock']) && !$this->force_product) {
                    if ((int)$quantity > $result2['quantity']) {
                        return false;
                    }
                }
                $new_qty = (int)$result2['quantity'] - $quantity;
                if ($new_qty < $minimal_quantity && !$this->force_product) {
                    return false;
                }

                if (_PS_VERSION_ < '1.5') {
                    $values = array(
                        'id_product' => (int)$id_product,
                        'id_product_attribute' => (int)$id_product_attribute,
                        'id_cart' => (int)$this->id,
                        'quantity' => (int)$quantity,
                        'date_add' => date('Y-m-d H:i:s'),
                    );
                    $result_add = DB::getInstance()->autoExecute(_DB_PREFIX_ . 'cart_product', $values, 'insert');
                } else {
                    $values = array(
                        'id_product' => (int)$id_product,
                        'id_product_attribute' => (int)$id_product_attribute,
                        'id_cart' => (int)$this->id,
                        'id_address_delivery' => (int)$id_address_delivery,
                        'id_shop' => (int)$shop->id,
                        'quantity' => (int)$quantity,
                        'date_add' => date('Y-m-d H:i:s'),
                    );
                    $result_add = Db::getInstance()->insert('cart_product', $values);
                }
                if (!$result_add) {
                    return false;
                }
            }
        }

        // refresh cache of self::_products
        $this->_products = $this->getProducts(true);
        $this->update();
        $context = Context::getContext()->cloneContext();
        $context->cart = $this;
        if (_PS_VERSION_ >= '1.5') {
            Cache::clean('getContextualValue_*');
        }
        // Generates errors when creating the cart
        // if (_PS_VERSION_ >= '1.5' && $auto_add_cart_rule) {
        //     CartRule::autoAddToCart($context);
        // }
        if ($product->customizable) {
            return $this->_updateCustomizationQuantity(
                (int)$quantity,
                (int)$id_customization,
                (int)$id_product,
                (int)$id_product_attribute,
                (int)$id_address_delivery,
                $operator
            );
        } else {
            return true;
        }
    }

    /* LengowObject interface methods */

    /**
     * @see LengowObject::getFieldDefinition()
     */
    public static function getFieldDefinition()
    {
        if (_PS_VERSION_ < 1.5) {
            return LengowCart::$definition_lengow;
        }

        return LengowCart::$definition['fields'];
    }

    /**
     * @see LengowObject::assign()
     */
    public function assign($data = array())
    {
        foreach ($data as $field => $value) {
            $this->{$field} = $value;
        }
    }

    /**
     * @see LengowObject::validateLengow()
     */
    public function validateLengow()
    {
        $definition = LengowCart::getFieldDefinition();

        foreach ($definition as $field_name => $constraints) {
            if (isset($constraints['required']) && $constraints['required']) {
                if (!$this->{$field_name}) {
                    $this->validateFieldLengow($field_name, LengowObject::LENGOW_EMPTY_ERROR);
                }
            }

            if (isset($constraints['size'])) {
                if (Tools::strlen($this->{$field_name}) > $constraints['size']) {
                    $this->validateFieldLengow($field_name, LengowObject::LENGOW_SIZE_ERROR);
                }
            }
        }
        // validateFields
        $return = $this->validateFields(false, true);
        if (is_string($return)) {
            throw new InvalidLengowObjectException($return);
        }
        $this->add();
        return true;
    }

    /**
     * @see LengowObject::validateFieldLengow()
     */
    public function validateFieldLengow($field, $error_type)
    {
        switch ($error_type) {
            case LengowObject::LENGOW_EMPTY_ERROR:
                $this->validateEmptyLengow($field);
                break;
            case LengowObject::LENGOW_SIZE_ERROR:
                $this->validateSizeLengow($field);
                break;
            default:
                # code...
                break;
        }
    }

    /**
     * @see LengowObject::validateEmptyLengow()
     */
    public function validateEmptyLengow($field)
    {
        switch ($field) {
            case 'id_lang':
                $this->{$field} = Context::getContext()->language->id;
                break;
            default:
                break;
        }
    }

    /**
     * @see LengowObject::validateSizeLengow()
     */
    public function validateSizeLengow($field)
    {
        // no size limitation for cart object
        return $field;
    }
}
