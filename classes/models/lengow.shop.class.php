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
class LengowShop extends Shop
{

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
        if (_PS_VERSION_ < '1.5') {
            $this->id = 1;
            $this->name = Configuration::get('PS_SHOP_NAME');
            $this->domain = Configuration::get('PS_SHOP_DOMAIN');
        }
    }

    public static function isFeatureActive()
    {
        if (_PS_VERSION_ < '1.5') {
            return false;
        } else {
            parent::isFeatureActive();
        }
    }
}
