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
/*
 * Lengow Cart Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class LengowCart extends Cart
{
    /**
     * @var bool add inactive & out of stock products to cart
     */
    public $forceProduct = true;

    /**
     * Add product to cart
     *
     * @param array $products list of products to be added
     *
     * @return bool
     *
     * @throws Exception|LengowException Cannot add product to cart / No quantity for product
     */
    public function addProducts($products = [])
    {
        if (!$products) {
            throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.no_product_to_cart'));
        }
        foreach ($products as $id => $product) {
            $ids = explode('_', $id);
            if (count($ids) > 2) {
                throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.cannot_add_product_to_cart', ['product_id' => $id]));
            }
            $idProduct = (int) $ids[0];
            $idProductAttribute = isset($ids[1]) ? (int) $ids[1] : null;
            if (!$this->updateQty($product['quantity'], $idProduct, $idProductAttribute)) {
                throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.no_quantity_for_product', ['product_id' => $id]));
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
    public function cleanCart($products = [])
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
     * @param int $quantity quantity to add (or subtract)
     * @param int $idProduct PrestaShop product id
     * @param int|null $idProductAttribute attribute id if needed
     * @param mixed $idCustomization PrestaShop customization id
     * @param string $operator indicate if quantity must be increased or decreased
     * @param int $idAddressDelivery PrestaShop address delivery id
     * @param Shop|null $shop Shop instance
     * @param bool $autoAddCartRule add auto cart rule
     * @param bool $skipAvailabilityCheckOutOfStock skip availability
     * @param bool $preserveGiftRemoval preserve gift removal
     *
     * @return bool
     *
     * @throws Exception|PrestaShopDatabaseException
     */
    public function updateQty($quantity, $idProduct, $idProductAttribute = null, $idCustomization = false, $operator = 'up', $idAddressDelivery = 0, ?Shop $shop = null, $autoAddCartRule = true, $skipAvailabilityCheckOutOfStock = false, $preserveGiftRemoval = true, $useOrderPrices = false)
    {
        if (!$shop) {
            $shop = Context::getContext()->shop;
        }
        // this line are useless, but PrestaShop validator require it
        $autoAddCartRule = $autoAddCartRule;
        $useOrderPrices = $useOrderPrices;
        $skipAvailabilityCheckOutOfStock = $skipAvailabilityCheckOutOfStock;
        $preserveGiftRemoval = $preserveGiftRemoval;
        $quantity = (int) $quantity;
        $idProduct = (int) $idProduct;
        $idProductAttribute = (int) $idProductAttribute;
        $product = new Product($idProduct, false, Configuration::get('PS_LANG_DEFAULT'), $shop->id);
        if ($idProductAttribute) {
            $combination = new Combination((int) $idProductAttribute);
            if ((int) $combination->id_product !== $idProduct) {
                return false;
            }
        }
        // if we have a product combination, the minimal quantity is set with the one of this combination
        if (!empty($idProductAttribute)) {
            $version = defined('_PS_VERSION_') ? _PS_VERSION_ : '';
            // for PrestaShop 8.0 and higher
            if (version_compare($version, '8.0.0.0', '>=')) {
                $minimalQuantity = (int) ProductAttribute::getAttributeMinimalQty($idProductAttribute);
            } else {
                $minimalQuantity = (int) Attribute::getAttributeMinimalQty($idProductAttribute);
            }
        } else {
            $minimalQuantity = (int) $product->minimal_quantity;
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
        if ((!$product->available_for_order && !$this->forceProduct)
            || (Configuration::get('PS_CATALOG_MODE') && !defined('_PS_ADMIN_DIR_'))
        ) {
            return false;
        }
        // check if the product is already in the cart
        $result = $this->containsProduct(
            $idProduct,
            $idProductAttribute,
            (int) $idCustomization,
            (int) $idAddressDelivery
        );
        // update quantity if product already exist
        if ($result) {
            // always add product to cart in import
            $sql = 'SELECT stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity
                    FROM ' . _DB_PREFIX_ . 'product p
                    ' . Product::sqlStock('p', (int) $idProductAttribute, true, $shop) . '
                    WHERE p.id_product = ' . (int) $idProduct;
            $result2 = Db::getInstance()->getRow($sql);
            $productQty = (int) $result2['quantity'];
            // quantity for product pack
            if (Pack::isPack($idProduct)) {
                $productQty = Product::getQuantity($idProduct, $idProductAttribute);
            }
            $newQty = (int) $result['quantity'] + (int) $quantity;
            $qty = '+ ' . (int) $quantity;
            // force here
            if ($newQty > $productQty
                && !Product::isAvailableWhenOutOfStock(!$this->forceProduct && (int) $result2['out_of_stock'])
            ) {
                return false;
            }
            // delete product from cart
            if ($newQty <= 0) {
                return $this->deleteProduct($idProduct, $idProductAttribute, (int) $idCustomization);
            }
            if ($newQty < $minimalQuantity && !$this->forceProduct) {
                return false;
            }
            Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'cart_product`
                SET `quantity` = `quantity` ' . $qty . ', `date_add` = NOW()
                WHERE `id_product` = ' . (int) $idProduct .
                (!empty($idProductAttribute) ?
                    ' AND `id_product_attribute` = ' . (int) $idProductAttribute : '') . '
                AND `id_cart` = ' . (int) $this->id .
                (Configuration::get('PS_ALLOW_MULTISHIPPING') && $this->isMultiAddressDelivery()
                    ? ' AND `id_address_delivery` = ' . (int) $idAddressDelivery : '') . '
                LIMIT 1'
            );
        } else {
            $sql = 'SELECT stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity
                FROM ' . _DB_PREFIX_ . 'product p
                ' . Product::sqlStock('p', (int) $idProductAttribute, true, $shop) . '
                WHERE p.id_product = ' . (int) $idProduct;
            $result2 = Db::getInstance()->getRow($sql);
            // quantity for product pack
            $result2['quantity'] = Product::getQuantity($idProduct, $idProductAttribute);
            if ((int) $quantity > $result2['quantity']
                && !$this->forceProduct
                && !Product::isAvailableWhenOutOfStock((int) $result2['out_of_stock'])
            ) {
                return false;
            }
            $newQty = (int) $result2['quantity'] - $quantity;
            if ($newQty < $minimalQuantity && !$this->forceProduct) {
                return false;
            }
            $values = [
                'id_product' => (int) $idProduct,
                'id_product_attribute' => (int) $idProductAttribute,
                'id_cart' => (int) $this->id,
                'id_address_delivery' => (int) $idAddressDelivery,
                'id_shop' => (int) $shop->id,
                'quantity' => (int) $quantity,
                'date_add' => date(LengowMain::DATE_FULL),
            ];
            $resultAdd = Db::getInstance()->insert('cart_product', $values);
            if (!$resultAdd) {
                return false;
            }
        }
        // refresh cache of self::_products
        $this->_products = $this->getProducts(true);
        $this->update();
        $context = Context::getContext()->cloneContext();
        $context->cart = $this;
        Cache::clean('getContextualValue_*');
        if ($product->customizable) {
            return $this->_updateCustomizationQuantity(
                $quantity,
                (int) $idCustomization,
                $idProduct,
                $idProductAttribute,
                (int) $idAddressDelivery,
                $operator
            );
        }

        return true;
    }

    /**
     * Get definition array
     *
     * @return array
     */
    public static function getFieldDefinition()
    {
        return self::$definition['fields'];
    }

    /**
     * Assign API data
     *
     * @param array $data API data
     */
    public function assign($data = [])
    {
        foreach ($data as $field => $value) {
            $this->{$field} = $value;
        }
    }

    /**
     * Validate Lengow
     *
     * @return bool
     *
     * @throws LengowException invalid object
     * @throws Exception
     */
    public function validateLengow()
    {
        $definition = self::getFieldDefinition();
        foreach ($definition as $fieldName => $constraints) {
            if (isset($constraints['required']) && $constraints['required'] && !$this->{$fieldName}) {
                $this->validateFieldLengow($fieldName, LengowAddress::LENGOW_EMPTY_ERROR);
            }
            if (isset($constraints['size']) && Tools::strlen($this->{$fieldName}) > $constraints['size']) {
                $this->validateFieldLengow($fieldName, LengowAddress::LENGOW_SIZE_ERROR);
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
        if ($errorType === LengowAddress::LENGOW_EMPTY_ERROR && $fieldName === 'id_lang') {
            $this->{$fieldName} = Context::getContext()->language->id;
        }
    }
}
