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
 * Lengow Link Class
 */
class LengowLink extends LinkCore
{
    /**
     * @var boolean use in toolbox to get specific link
     */
    protected static $forceLink;

    /**
     * Set force link for toolbox
     *
     * @param boolean $forceLink use in toolbox to get specific link
     */
    public static function forceLink($forceLink)
    {
        self::$forceLink = $forceLink;
    }

    /**
     * Get absolute admin link
     *
     * @param string $controller name of the controller
     * @param boolean $ajax if link use ajax
     * @param boolean $adminPrestashop if link is a prestashop controller
     *
     * @return string
     */
    public function getAbsoluteAdminLink($controller, $ajax = false, $adminPrestashop = false)
    {
        // use in toolbox to get specific link
        if (self::$forceLink) {
            return self::$forceLink;
        }
        if (_PS_VERSION_ < '1.5' && !$adminPrestashop) {
            $controller .= '14';
        }
        $adminPath = Tools::getShopDomainSsl(true, true) .
            __PS_BASE_URI__ . Tools::substr(_PS_ADMIN_DIR_, strrpos(_PS_ADMIN_DIR_, '/') + 1);
        try {
            if (_PS_VERSION_ < '1.6') {
                if (_PS_VERSION_ < '1.5' && $ajax) {
                    $adminPath .= '/ajax-tab.php?tab=' . $controller
                        . '&token=' . Tools::getAdminTokenLite($controller);
                } else {
                    $adminPath .= '/index.php?tab=' . $controller
                        . '&token=' . Tools::getAdminTokenLite($controller);
                }
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
