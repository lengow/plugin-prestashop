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
/**
 * Lengow Link Class
 */
class LengowLink extends LinkCore
{
    /**
     * Get absolute admin link
     *
     * @param string $controller name of the controller
     *
     * @return string
     */
    public function getAbsoluteAdminLink($controller)
    {
        $adminPath = Tools::getShopDomainSsl(true, true) .
            __PS_BASE_URI__ . Tools::substr(_PS_ADMIN_DIR_, strrpos(_PS_ADMIN_DIR_, '/') + 1);
        try {
            if (_PS_VERSION_ < '1.6') {
                $adminPath .= '/index.php?tab=' . $controller . '&token=' . Tools::getAdminTokenLite($controller);
            } elseif (_PS_VERSION_ < '1.7') {
                $adminPath .= '/' . $this->getAdminLink($controller);
            } else {
                $adminPath = $this->getAdminLink($controller);
            }
        } catch (Exception $e) {
            return '';
        }

        return $adminPath;
    }
}
