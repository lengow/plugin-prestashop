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
 * Lengow Cart Class
 */
class LengowCart extends Cart
{
    /**
     * @var boolean add inactive & out of stock products to cart
     */
    public $forceProduct = true;

    /**
     * @var array definition array for prestashop 1.4
     */
    public static $definitionLengow = array(
        'id_currency' => array('required' => true),
        'id_lang' => array('required' => true),
    );

    /**
     * Add product to cart
     *
     * @param array $products list of products to be added
     *
     * @throws Exception|LengowException Cannot add product to cart / No quantity for product
     *
     * @return boolean
     */
    public function addProducts($products = array())
    {
        if (!$products) {
            throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.no_product_to_cart'));
        }
        foreach ($products as $id => $product) {
            $ids = explode('_', $id);
            if (count($ids) > 2) {
                throw new LengowException(
                    LengowMain::setLogMessage(
                        'lengow_log.exception.cannot_add_product_to_cart',
                        array('product_id' => $id)
                    )
                );
            }
            $idProduct = (int)$ids[0];
            $idProductAttribute = isset($ids[1]) ? (int)$ids[1] : null;
            if (!$this->updateQty($product['quantity'], $idProduct, $idProductAttribute)) {
                throw new LengowException(
                    LengowMain::setLogMessage(
                        'lengow_log.exception.no_quantity_for_product',
                        array('product_id' => $id)
                    )
                );
            }
        }
        return true;
    }

    /**
     * Removes non-Lengow products from cart
     *
     * @param array $products list of products to be added
     *
     * @throws Exception Cannot add product to cart
     */
    public function cleanCart($products = array())
    {
        $cartProducts = $this->getProducts();
        if (empty($cartProducts)) {
            throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.no_product_to_cart'));
        }
        foreach ($cartProducts as $cartProduct) {
            $idProduct = $cartProduct['id_product'];
            $idProduct .= empty($cartProduct['id_product_attribute']) ? '' : '_' . $cartProduct['id_product_attribute'];
            if (!array_key_exists($idProduct, $products)) {
                if (empty($cartProduct['id_product_attribute'])) {
                    $this->deleteProduct($cartProduct['id_product'], $cartProduct['id_product_attribute']);
                } else {
                    $this->deleteProduct($cartProduct['id_product']);
                }
            }
        }
    }

    /**
     * @see Cart::updateQty()
     *
     * @param integer $quantity quantity to add (or subtract)
     * @param integer $idProduct Prestashop product id
     * @param integer|null $idProductAttribute attribute id if needed
     * @param mixed $idCustomization Prestashop customization id
     * @param string $operator indicate if quantity must be increased or decreased
     * @param integer $idAddressDelivery Prestashop address delivery id
     * @param Shop|null $shop Shop instance
     * @param boolean $autoAddCartRule add auto cart rule
     * @param boolean $skipAvailabilityCheckOutOfStock skip availability
     *
     * @throws Exception|PrestaShopDatabaseException
     *
     * @return boolean
     */
    public function updateQty(
        $quantity,
        $idProduct,
        $idProductAttribute = null,
        $idCustomization = false,
        $operator = 'up',
        $idAddressDelivery = 0,
        Shop $shop = null,
        $autoAddCartRule = true,
        $skipAvailabilityCheckOutOfStock = false
    ) {
        if (!$shop) {
            $shop = Context::getContext()->shop;
        }
        // this line is useless, but Prestashop validator require it
        $autoAddCartRule = $autoAddCartRule;
        $skipAvailabilityCheckOutOfStock = $skipAvailabilityCheckOutOfStock;
        $quantity = (int)$quantity;
        $idProduct = (int)$idProduct;
        $idProductAttribute = (int)$idProductAttribute;
        $product = new Product($idProduct, false, Configuration::get('PS_LANG_DEFAULT'), $shop->id);
        if ($idProductAttribute) {
            $combination = new Combination((int)$idProductAttribute);
            if ((int)$combination->id_product !== $idProduct) {
                return false;
            }
        }
        // if we have a product combination, the minimal quantity is set with the one of this combination
        if (!empty($idProductAttribute)) {
            $minimalQuantity = (int)Attribute::getAttributeMinimalQty($idProductAttribute);
        } else {
            $minimalQuantity = (int)$product->minimal_quantity;
        }
        if (!Validate::isLoadedObject($product)) {
            return false;
        }
        if (isset(self::$_nbProducts[$this->id])) {
            unset(self::$_nbProducts[$this->id]);
        }
        if (isset(self::$_totalWeight[$this->id])) {
            unset(self::$_totalWeight[$this->id]);
        }
        if ((!$product->available_for_order && !$this->forceProduct) ||
            (Configuration::get('PS_CATALOG_MODE') && !defined('_PS_ADMIN_DIR_'))
        ) {
            return false;
        } else {
            // check if the product is already in the cart
            $result = $this->containsProduct(
                $idProduct,
                $idProductAttribute,
                (int)$idCustomization,
                (int)$idAddressDelivery
            );
            // update quantity if product already exist
            if ($result) {
                // always add product to cart in import
                if (_PS_VERSION_ < '1.5') {
                    $sql = 'SELECT ' . (!empty($idProductAttribute) ? 'pa' : 'p') . '.`quantity`, p.`out_of_stock`
							FROM `' . _DB_PREFIX_ . 'product` p
							' . (!empty($idProductAttribute) ? 'LEFT JOIN `' . _DB_PREFIX_ .
                            'product_attribute` pa ON p.`id_product` = pa.`id_product`' : '') . '
							WHERE p.`id_product` = ' . (int)$idProduct .
                        (!empty($idProductAttribute) ?
                            ' AND `id_product_attribute` = ' . (int)$idProductAttribute : '');
                } else {
                    $sql = 'SELECT stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity
							FROM ' . _DB_PREFIX_ . 'product p
							' . Product::sqlStock('p', (int)$idProductAttribute, true, $shop) . '
							WHERE p.id_product = ' . (int)$idProduct;
                }
                $result2 = Db::getInstance()->getRow($sql);
                $productQty = (int)$result2['quantity'];
                // quantity for product pack
                if (Pack::isPack($idProduct)) {
                    $productQty = Product::getQuantity($idProduct, $idProductAttribute);
                }
                $newQty = (int)$result['quantity'] + (int)$quantity;
                $qty = '+ ' . (int)$quantity;
                // force here
                if (!Product::isAvailableWhenOutOfStock((int)$result2['out_of_stock']) && !$this->forceProduct) {
                    if ($newQty > $productQty) {
                        return false;
                    }
                }
                // delete product from cart
                if ($newQty <= 0) {
                    return $this->deleteProduct($idProduct, $idProductAttribute, (int)$idCustomization);
                } elseif ((int)$newQty < $minimalQuantity && !$this->forceProduct) {
                    return false;
                } else {
                    if (_PS_VERSION_ < '1.5') {
                        Db::getInstance()->Execute(
                            'UPDATE `' . _DB_PREFIX_ . 'cart_product`
                            SET `quantity` = `quantity` ' . $qty . '
                            WHERE `id_product` = ' . (int)$idProduct .
                            (!empty($idProductAttribute) ?
                                ' AND `id_product_attribute` = ' . (int)$idProductAttribute : '') . '
                            AND `id_cart` = ' . (int)$this->id . '
                            LIMIT 1'
                        );
                    } else {
                        Db::getInstance()->execute(
                            'UPDATE `' . _DB_PREFIX_ . 'cart_product`
                            SET `quantity` = `quantity` ' . $qty . ', `date_add` = NOW()
                            WHERE `id_product` = ' . (int)$idProduct .
                            (!empty($idProductAttribute) ?
                                ' AND `id_product_attribute` = ' . (int)$idProductAttribute : '') . '
                            AND `id_cart` = ' . (int)$this->id .
                            (Configuration::get('PS_ALLOW_MULTISHIPPING') && $this->isMultiAddressDelivery()
                                ? ' AND `id_address_delivery` = ' . (int)$idAddressDelivery : '') . '
                            LIMIT 1'
                        );
                    }
                }
            } else {
                if (_PS_VERSION_ < '1.5') {
                    $sql = 'SELECT ' . (!empty($idProductAttribute) ? 'pa' : 'p') . '.`quantity`, p.`out_of_stock`
							FROM `' . _DB_PREFIX_ . 'product` p
							' . (!empty($idProductAttribute) ?
                            'LEFT JOIN `' . _DB_PREFIX_ .
                            'product_attribute` pa ON p.`id_product` = pa.`id_product`' : '') . '
							WHERE p.`id_product` = ' . (int)$idProduct .
                        (!empty($idProductAttribute) ?
                            ' AND `id_product_attribute` = ' . (int)$idProductAttribute : '');
                } else {
                    $sql = 'SELECT stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity
						FROM ' . _DB_PREFIX_ . 'product p
						' . Product::sqlStock('p', (int)$idProductAttribute, true, $shop) . '
						WHERE p.id_product = ' . (int)$idProduct;
                }
                $result2 = Db::getInstance()->getRow($sql);
                // quantity for product pack
                if (_PS_VERSION_ > '1.4' && Pack::isPack($idProduct)) {
                    $result2['quantity'] = Product::getQuantity($idProduct, $idProductAttribute);
                }
                if (!Product::isAvailableWhenOutOfStock((int)$result2['out_of_stock']) && !$this->forceProduct) {
                    if ((int)$quantity > $result2['quantity']) {
                        return false;
                    }
                }
                $newQty = (int)$result2['quantity'] - $quantity;
                if ($newQty < $minimalQuantity && !$this->forceProduct) {
                    return false;
                }
                if (_PS_VERSION_ < '1.5') {
                    $values = array(
                        'id_product' => (int)$idProduct,
                        'id_product_attribute' => (int)$idProductAttribute,
                        'id_cart' => (int)$this->id,
                        'quantity' => (int)$quantity,
                        'date_add' => date('Y-m-d H:i:s'),
                    );

                    if (_PS_VERSION_ < '1.5') {
                        $resultAdd = DB::getInstance()->autoExecute(_DB_PREFIX_ . 'cart_product', $values, 'insert');
                    } else {
                        $resultAdd = DB::getInstance()->insert('cart_product', $values);
                    }
                } else {
                    $values = array(
                        'id_product' => (int)$idProduct,
                        'id_product_attribute' => (int)$idProductAttribute,
                        'id_cart' => (int)$this->id,
                        'id_address_delivery' => (int)$idAddressDelivery,
                        'id_shop' => (int)$shop->id,
                        'quantity' => (int)$quantity,
                        'date_add' => date('Y-m-d H:i:s'),
                    );
                    $resultAdd = Db::getInstance()->insert('cart_product', $values);
                }
                if (!$resultAdd) {
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
        if ($product->customizable) {
            return $this->_updateCustomizationQuantity(
                $quantity,
                (int)$idCustomization,
                $idProduct,
                $idProductAttribute,
                (int)$idAddressDelivery,
                $operator
            );
        } else {
            return true;
        }
    }

    /**
     * Get definition array
     *
     * @return array
     */
    public static function getFieldDefinition()
    {
        if (_PS_VERSION_ < 1.5) {
            return self::$definitionLengow;
        }
        return self::$definition['fields'];
    }

    /**
     * Assign API data
     *
     * @param array $data API data
     */
    public function assign($data = array())
    {
        foreach ($data as $field => $value) {
            $this->{$field} = $value;
        }
    }

    /**
     * Validate Lengow
     *
     * @throws LengowException invalid object
     *
     * @throws Exception
     *
     * @return boolean
     */
    public function validateLengow()
    {
        $definition = self::getFieldDefinition();
        foreach ($definition as $fieldName => $constraints) {
            if (isset($constraints['required']) && $constraints['required']) {
                if (!$this->{$fieldName}) {
                    $this->validateFieldLengow($fieldName, LengowAddress::LENGOW_EMPTY_ERROR);
                }
            }
            if (isset($constraints['size'])) {
                if (Tools::strlen($this->{$fieldName}) > $constraints['size']) {
                    $this->validateFieldLengow($fieldName, LengowAddress::LENGOW_SIZE_ERROR);
                }
            }
        }
        // validateFields
        $return = $this->validateFields(false, true);
        if (is_string($return)) {
            throw new LengowException($return);
        }
        $this->add();
        return true;
    }

    /**
     * Modify a field according to the type of error
     *
     * @param string $fieldName incorrect field
     * @param string $errorType type of error
     */
    public function validateFieldLengow($fieldName, $errorType)
    {
        switch ($errorType) {
            case LengowAddress::LENGOW_EMPTY_ERROR:
                $this->validateEmptyLengow($fieldName);
                break;
            case LengowAddress::LENGOW_SIZE_ERROR:
                $this->validateSizeLengow($fieldName);
                break;
            default:
                # code...
                break;
        }
    }

    /**
     * Modify an empty field
     *
     * @param string $fieldName field name
     */
    public function validateEmptyLengow($fieldName)
    {
        switch ($fieldName) {
            case 'id_lang':
                $this->{$fieldName} = Context::getContext()->language->id;
                break;
            default:
                break;
        }
    }

    /**
     * Modify a field to fit its size
     *
     * @param string $fieldName field name
     *
     * @return string
     */
    public function validateSizeLengow($fieldName)
    {
        // no size limitation for cart object
        return $fieldName;
    }
}
