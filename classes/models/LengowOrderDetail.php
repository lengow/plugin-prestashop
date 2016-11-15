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
 * Lengow Order Detail Class
 */
class LengowOrderDetail extends OrderDetail
{
    /**
     * Version
     */
    const VERSION = '1.0.0';

    /**
     * Set a new price of product
     *
     * @param float $newPrice The new price of product
     * @param float $tax      The tax apply
     */
    public function changePrice($newPrice, $tax)
    {
        $tax = 1 + (0.01 * $tax);
        $this->reduction_amount = 0.00;
        $this->reduction_percent = 0.00;
        $this->reduction_amount_tax_incl = 0.00;
        $this->reduction_amount_tax_excl = 0.00;
        $this->product_price = LengowMain::formatNumber($newPrice / $tax);
        if (_PS_VERSION_ >= '1.5') {
            $this->unit_price_tax_incl = LengowMain::formatNumber($newPrice);
            $this->unit_price_tax_excl = LengowMain::formatNumber($newPrice / $tax);
            $this->total_price_tax_incl = LengowMain::formatNumber($newPrice * $this->product_quantity);
            $this->total_price_tax_excl = LengowMain::formatNumber(($newPrice * $this->product_quantity) / $tax);
        }
        $this->product_quantity_discount = 0.00;
        $this->save();
    }

    /**
     * Get Order Lines
     *
     * @param integer $idOrder   Prestashop order id
     * @param integer $idProduct Prestashop product id
     *
     * @return array list of order line
     */
    public static function findByOrderIdProductId($idOrder, $idProduct)
    {
        $sql = 'SELECT id_order_detail FROM `'._DB_PREFIX_.'order_detail`
        WHERE product_id = '.(int)$idProduct.' AND id_order='.(int)$idOrder;
        $row = Db::getInstance()->getRow($sql);
        return $row['id_order_detail'];
    }
}
