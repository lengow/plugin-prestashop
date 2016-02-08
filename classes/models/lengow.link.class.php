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

class LengowLink extends LinkCore
{
    //use in toolbox to get specific link
    protected static $force_link;

    public static function forceLink($force_link)
    {
        self::$force_link = $force_link;
    }

    public function getAbsoluteAdminLink($controller, $ajax = false)
    {
        //use in toolbox to get specific link
        if (self::$force_link) {
            return self::$force_link;
        }

        if (_PS_VERSION_ < '1.5') {
            $controller.= "14";
        }

        $admin_path = Tools::getShopDomainSsl(true, true).
            __PS_BASE_URI__.Tools::substr(_PS_ADMIN_DIR_, strrpos(_PS_ADMIN_DIR_, '/') + 1);
        if (_PS_VERSION_ < '1.6') {
            if (_PS_VERSION_ < '1.5' && $ajax) {
                $admin_path.= '/ajax-tab.php?tab='.$controller.'&token='.Tools::getAdminTokenLite($controller);
            } else {
                $admin_path.= '/index.php?tab='.$controller.'&token='.Tools::getAdminTokenLite($controller);
            }
        } else {
            $admin_path.= '/'.$this->getAdminLink($controller);
        }
        return $admin_path;
    }

    public function getExportLink($store)
    {



    }
}
