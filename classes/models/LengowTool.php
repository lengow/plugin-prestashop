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
 * Lengow Tool Class
 */
class LengowTool
{
    /**
     * Is user log in ?
     *
     * @return boolean
     */
    public function isLogged()
    {
        return (bool)Context::getContext()->cookie->lengow_toolbox;
    }

    /**
     * Logoff user
     */
    public function logOff()
    {
        unset(Context::getContext()->cookie->lengow_toolbox);
        Tools::redirect(_PS_BASE_URL_ . __PS_BASE_URI__ . 'modules/lengow/toolbox/', '');
    }

    /**
     * Get current uri
     *
     * @return string
     */
    public function getCurrentUri()
    {
        return $_SERVER['SCRIPT_NAME'];
    }

    /**
     * Process Login Form to log User
     *
     * @param integer $accountId Lengow account id
     * @param string $secretToken Lengow secret token
     *
     * @return boolean
     */
    public function processLogin($accountId, $secretToken)
    {
        if (Tools::strlen($accountId) > 0 && Tools::strlen($secretToken) > 0) {
            if ($this->checkBlockedIp()) {
                self::redirect(_PS_BASE_URL_ . __PS_BASE_URI__ . 'modules/lengow/toolbox/login.php?blockedIP=1', '');
            }
        }
        $prestaAccountId = LengowConfiguration::get('LENGOW_ACCOUNT_ID');
        $prestaSecretToken = LengowConfiguration::get('LENGOW_SECRET_TOKEN');
        if (Tools::strlen($prestaAccountId) > 0 && Tools::strlen($prestaSecretToken) > 0) {
            if ($prestaAccountId === $accountId && $prestaSecretToken === $secretToken) {
                Context::getContext()->cookie->lengow_toolbox = true;
                $this->unblockIp();
                self::redirect(_PS_BASE_URL_ . __PS_BASE_URI__ . 'modules/lengow/toolbox/', '');
            }
        }
        if (Tools::strlen($accountId) > 0 && Tools::strlen($secretToken) > 0) {
            $this->checkIp();
        }
        return false;
    }

    /**
     * Check if Current IP is blocked
     *
     * @return boolean
     */
    public function checkBlockedIp()
    {
        $remoteIp = $_SERVER['REMOTE_ADDR'];
        $blockedIp = Tools::jsonDecode(LengowConfiguration::get('LENGOW_ACCESS_BLOCK_IP_3'));
        if (is_array($blockedIp) && in_array($remoteIp, $blockedIp)) {
            return true;
        }
        return false;
    }

    /**
     * Check IP with number tentative
     *
     * @param integer $counter check ip counter
     *
     * @return void
     */
    public function checkIp($counter = 1)
    {
        $remoteIp = $_SERVER['REMOTE_ADDR'];
        if ($counter > 3 || LengowMain::checkIP()) {
            return;
        }
        $blockedIp = Tools::jsonDecode(LengowConfiguration::getGlobalValue('LENGOW_ACCESS_BLOCK_IP_' . $counter));
        if (!is_array($blockedIp) || !in_array($remoteIp, $blockedIp)) {
            LengowConfiguration::updateGlobalValue(
                'LENGOW_ACCESS_BLOCK_IP_' . $counter,
                is_array($blockedIp)
                    ? Tools::jsonEncode(array_merge($blockedIp, array($remoteIp)))
                    : Tools::jsonEncode(array($remoteIp))
            );
        } else {
            $this->checkIp($counter + 1);
        }
    }

    /**
     * Unblock All IP tentative if success login
     */
    public function unblockIp()
    {
        $remoteIp = $_SERVER['REMOTE_ADDR'];
        for ($i = 1; $i <= 3; $i++) {
            $blockedIp = Tools::jsonDecode(LengowConfiguration::getGlobalValue('LENGOW_ACCESS_BLOCK_IP_' . $i));
            if (is_array($blockedIp)) {
                $blockedIp = array_diff($blockedIp, array($remoteIp));
                $blockedIp = reset($blockedIp);
                LengowConfiguration::updateGlobalValue(
                    'LENGOW_ACCESS_BLOCK_IP_' . $i,
                    empty($blockedIp) ? '' : Tools::jsonEncode($blockedIp)
                );
            }
        }
    }

    /**
     * Redirect toolbox
     *
     * @param string $url url toolbox
     * @param mixed $baseUri base uri
     */
    public static function redirect($url, $baseUri = __PS_BASE_URI__)
    {
        Tools::redirect($url, $baseUri);
    }
}
