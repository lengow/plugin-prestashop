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
 * Lengow Home Controller Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class LengowHomeController extends LengowController
{
    /**
     * Process Post Parameters
     *
     * @return void
     */
    public function postProcess(): void
    {
        $this->prepareDisplay();
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
        if ($action) {
            switch ($action) {
                case 'go_to_credentials':
                    $displayContent = $this->twig->render(
                        '@Modules/lengow/views/templates/admin/lengow_home/helpers/view/connection_cms.html.twig',
                        $this->getCommonVarsForSubTemplate()
                    );
                    $this->respondJson(
                        ['content' => preg_replace('/\r|\n/', '', $displayContent)]
                    );
                    break;
                case 'connect_cms':
                    $cmsConnected = false;
                    $hasCatalogToLink = false;
                    $accessToken = Tools::getIsset('accessToken') ? Tools::getValue('accessToken') : '';
                    $secret = Tools::getIsset('secret') ? Tools::getValue('secret') : '';
                    $credentialsValid = $this->checkApiCredentials($accessToken, $secret);
                    if ($credentialsValid) {
                        $cmsConnected = $this->connectCms();
                        if ($cmsConnected) {
                            $hasCatalogToLink = $this->hasCatalogToLink();
                        }
                    }
                    $displayContent = $this->twig->render(
                        '@Modules/lengow/views/templates/admin/lengow_home/helpers/view/connection_cms_result.html.twig',
                        array_merge($this->getCommonVarsForSubTemplate(), [
                            'credentialsValid' => $credentialsValid,
                            'cmsConnected' => $cmsConnected,
                            'hasCatalogToLink' => $hasCatalogToLink,
                        ])
                    );
                    $this->respondJson(
                        [
                            'success' => $cmsConnected,
                            'content' => preg_replace('/\r|\n/', '', $displayContent),
                        ]
                    );
                    break;
                case 'go_to_catalog':
                    $retry = Tools::getIsset('retry') ? Tools::getValue('retry') !== 'false' : false;
                    if ($retry) {
                        LengowConfiguration::resetCatalogIds();
                    }
                    $displayContent = $this->twig->render(
                        '@Modules/lengow/views/templates/admin/lengow_home/helpers/view/connection_catalog.html.twig',
                        array_merge($this->getCommonVarsForSubTemplate(), [
                            'shopCollection' => LengowShop::getActiveShops(),
                            'catalogList' => $this->getCatalogList(),
                        ])
                    );
                    $this->respondJson(
                        ['content' => preg_replace('/\r|\n/', '', $displayContent)]
                    );
                    break;
                case 'link_catalogs':
                    $catalogsLinked = true;
                    $catalogSelected = Tools::getIsset('catalogSelected')
                        ? Tools::getValue('catalogSelected')
                        : [];
                    if (!empty($catalogSelected)) {
                        $catalogsLinked = $this->saveCatalogsLinked($catalogSelected);
                    }
                    $displayConnectionResult = $this->twig->render(
                        '@Modules/lengow/views/templates/admin/lengow_home/helpers/view/connection_catalog_failed.html.twig',
                        $this->getCommonVarsForSubTemplate()
                    );
                    $this->respondJson(
                        [
                            'success' => $catalogsLinked,
                            'content' => preg_replace('/\r|\n/', '', $displayConnectionResult),
                        ]
                    );
                    break;
            }
            $this->finishPostProcess();
        }
    }

    /**
     * Display data page
     *
     * @return void
     */
    public function display(): void
    {
        if ($this->isNewMerchant) {
            $this->templateVars['lengow_ajax_link'] = $this->lengowLink->getAbsoluteAdminLink('AdminLengowHome');
            parent::display();
        } else {
            Tools::redirectAdmin($this->lengowLink->getAbsoluteAdminLink('AdminLengowDashboard'));
        }
    }

    /**
     * Get common variables for sub-template rendering (AJAX context)
     *
     * @return array<string, mixed>
     */
    private function getCommonVarsForSubTemplate(): array
    {
        return [
            'lengowPathUri' => $this->templateVars['lengowPathUri'] ?? $this->module->getPathUri(),
            'locale' => $this->locale,
            'lengow_link' => $this->lengowLink,
            'lengowUrl' => LengowConfiguration::getLengowUrl(),
            'helpCenterLink' => $this->templateVars['helpCenterLink'] ?? '',
            'supportLink' => $this->templateVars['supportLink'] ?? '',
        ];
    }

    /**
     * Check API credentials and save them in Database
     *
     * @param string $accessToken access token for api
     * @param string $secret secret for api
     *
     * @return bool
     */
    private function checkApiCredentials(string $accessToken, string $secret): bool
    {
        $accessIdsSaved = false;
        $accountId = LengowConnector::getAccountIdByCredentials($accessToken, $secret);
        if ($accountId) {
            $accessIdsSaved = LengowConfiguration::setAccessIds(
                [
                    LengowConfiguration::ACCOUNT_ID => $accountId,
                    LengowConfiguration::ACCESS_TOKEN => $accessToken,
                    LengowConfiguration::SECRET => $secret,
                ]
            );
        }

        return $accessIdsSaved;
    }

    /**
     * Connect cms with Lengow
     *
     * @return bool
     */
    private function connectCms(): bool
    {
        $cmsToken = LengowMain::getToken();
        $cmsConnected = LengowSync::syncCatalog(true);
        if (!$cmsConnected) {
            $syncData = json_encode(LengowSync::getSyncData());
            $result = LengowConnector::queryApi(LengowConnector::POST, LengowConnector::API_CMS, [], $syncData);
            if (isset($result->common_account)) {
                $cmsConnected = true;
                $messageKey = 'log.connection.cms_creation_success';
            } else {
                $messageKey = 'log.connection.cms_creation_failed';
            }
        } else {
            $messageKey = 'log.connection.cms_already_exist';
        }
        LengowMain::log(
            LengowLog::CODE_CONNECTION,
            LengowMain::setLogMessage(
                $messageKey,
                ['cms_token' => $cmsToken]
            )
        );
        // reset access ids if cms creation failed
        if (!$cmsConnected) {
            LengowConfiguration::resetAccessIds();
            LengowConfiguration::resetAuthorizationToken();
        }

        return $cmsConnected;
    }

    /**
     * Check if account has catalog to link
     *
     * @return bool
     */
    private function hasCatalogToLink(): bool
    {
        $activeShops = LengowShop::getActiveShops(true);
        if (empty($activeShops)) {
            return LengowCatalog::hasCatalogNotLinked();
        }

        return false;
    }

    /**
     * Get all catalogs available in Lengow
     *
     * @return array<int|string, mixed>
     */
    private function getCatalogList(): array
    {
        $activeShops = LengowShop::getActiveShops(true);
        if (empty($activeShops)) {
            return LengowCatalog::getCatalogList();
        }

        // if cms already has one or more linked catalogs, nothing is done
        return [];
    }

    /**
     * Save catalogs linked in database and send data to Lengow with call API
     *
     * @param array<string, mixed> $catalogSelected
     *
     * @return bool
     */
    private function saveCatalogsLinked(array $catalogSelected): bool
    {
        $catalogsLinked = true;
        $catalogsByShops = [];
        foreach ($catalogSelected as $catalog) {
            $catalogsByShops[$catalog['shopId']] = $catalog['catalogId'];
        }
        if (!empty($catalogsByShops)) {
            // save catalogs ids and active shop in lengow configuration
            foreach ($catalogsByShops as $idShop => $catalogIds) {
                LengowConfiguration::setCatalogIds($catalogIds, $idShop);
                LengowConfiguration::setActiveShop($idShop);
            }
            // save last update date for a specific settings (change synchronisation interval time)
            LengowConfiguration::updateGlobalValue(LengowConfiguration::LAST_UPDATE_SETTING, time());
            // link all catalogs selected by API
            $catalogsLinked = LengowCatalog::linkCatalogs($catalogsByShops);
            $messageKey = $catalogsLinked
                ? 'log.connection.link_catalog_success'
                : 'log.connection.link_catalog_failed';
            LengowMain::log(LengowLog::CODE_CONNECTION, LengowMain::setLogMessage($messageKey));
        }

        return $catalogsLinked;
    }
}
