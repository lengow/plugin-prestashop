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
 * The Lengow Order Class.
 *
 */
class LengowOrderDetail extends OrderDetail
{

    /**
     * Version.
     */
    const VERSION = '1.0.0';

    /**
     * Set a new price of product
     *
     * @param float $new_price The new price of product
     * @param float $tax The tax apply
     */
    public function changePrice($new_price, $tax)
    {
        $tax = 1 + (0.01 * $tax);
        $this->reduction_amount = 0.00;
        $this->reduction_percent = 0.00;
        $this->reduction_amount_tax_incl = 0.00;
        $this->reduction_amount_tax_excl = 0.00;
        $this->product_price = LengowMain::formatNumber($new_price / $tax);
        if (_PS_VERSION_ >= '1.5') {
            $this->unit_price_tax_incl = LengowMain::formatNumber($new_price);
            $this->unit_price_tax_excl = LengowMain::formatNumber($new_price / $tax);
            $this->total_price_tax_incl = LengowMain::formatNumber($new_price * $this->product_quantity);
            $this->total_price_tax_excl = LengowMain::formatNumber(($new_price * $this->product_quantity) / $tax);
        }
        $this->product_quantity_discount = 0.00;
        $this->save();
    }

    /**
     * v3
     * Get Order Lines
     * @param integer $product_id Product_id
     * @return array list of order line
     */
    public static function findByOrderIdProductId($order_id, $product_id)
    {
        $sql = 'SELECT id_order_detail FROM `'._DB_PREFIX_.'order_detail`
        WHERE product_id = '.(int)$product_id.' AND id_order='.(int)$order_id;
        $row = Db::getInstance()->getRow($sql);
        return $row['id_order_detail'];
    }
}
