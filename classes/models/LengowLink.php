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
 * Lengow Link Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class LengowLink extends LinkCore
{
    /**
     * Get absolute admin link
     *
     * @param string $controller name of the controller
     *
     * @return string
     */
    public function getAbsoluteAdminLink(string $controller): string
    {
        try {
            $adminPath = $this->getAdminLink($controller);
        } catch (Exception $e) {
            return '';
        }

        return $adminPath;
    }
}
