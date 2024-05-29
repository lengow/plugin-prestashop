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
if (!defined('_PS_VERSION_')) {
    exit;
}
/**
 * class LengowOrderCarrier
 */
class LengowOrderCarrier extends OrderCarrier
{
    /** @var string */
    public $return_tracking_number;

    /** @var string */
    public $return_carrier;

    /**
     * @param type $id
     * @param type $id_lang
     * @param type $id_shop
     * @param type $translator
     */
    public function __construct($id = null, $id_lang = null, $id_shop = null, $translator = null)
    {
        parent::__construct($id, $id_lang, $id_shop, $translator);

        self::$definition['fields']['return_tracking_number'] = ['type' => self::TYPE_STRING, 'validate' => 'isTrackingNumber'];
        self::$definition['fields']['return_carrier'] = ['type' => self::TYPE_STRING, 'validate' => 'isString'];
    }
}
