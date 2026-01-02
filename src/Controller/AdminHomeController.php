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

namespace PrestaShop\Module\Lengow\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use PrestaShopBundle\Security\Annotation\AdminSecurity;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Lengow Home/Connection Controller for PrestaShop 9
 */
class AdminHomeController extends FrameworkBundleAdminController
{
    /**
     * Home/Connection page
     *
     * @AdminSecurity("is_granted('read', 'AdminLengowHome')")
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        $locale = new \LengowTranslation();
        $lengowLink = new \LengowLink();
        $module = \Module::getInstanceByName('lengow');
        $isNewMerchant = \LengowConfiguration::isNewMerchant();
        
        // If not a new merchant, redirect to dashboard
        if (!$isNewMerchant) {
            return $this->redirectToRoute('lengow_admin_dashboard');
        }
        
        $lengowAjaxLink = $this->generateUrl('lengow_admin_home_ajax');
        
        return $this->render('@Modules/lengow/views/templates/admin/home/index.html.twig', [
            'locale' => $locale,
            'lengowPathUri' => $module->getPathUri(),
            'lengow_ajax_link' => $lengowAjaxLink,
            'displayToolbar' => 0,
        ]);
    }
    
    /**
     * Handle AJAX actions for home/connection page
     *
     * @AdminSecurity("is_granted('update', 'AdminLengowHome')")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxAction(Request $request): JsonResponse
    {
        $action = $request->request->get('action', $request->query->get('action'));
        $module = \Module::getInstanceByName('lengow');
        
        switch ($action) {
            case 'go_to_credentials':
                $displayContent = $module->display(
                    _PS_MODULE_LENGOW_DIR_,
                    'views/templates/admin/home/connection_cms.html.twig'
                );
                return new JsonResponse([
                    'content' => preg_replace('/\r|\n/', '', $displayContent)
                ]);
                
            case 'connect_cms':
                $accessToken = $request->request->get('accessToken', '');
                $secret = $request->request->get('secret', '');
                
                $credentialsValid = $this->checkApiCredentials($accessToken, $secret);
                $cmsConnected = false;
                $hasCatalogToLink = false;
                
                if ($credentialsValid) {
                    $cmsConnected = $this->connectCms();
                    if ($cmsConnected) {
                        $hasCatalogToLink = $this->hasCatalogToLink();
                    }
                }
                
                return $this->render('@Modules/lengow/views/templates/admin/home/connection_cms_result.html.twig', [
                    'credentialsValid' => $credentialsValid,
                    'cmsConnected' => $cmsConnected,
                    'hasCatalogToLink' => $hasCatalogToLink,
                ]);
                
            case 'go_to_catalog':
                $retry = $request->request->get('retry', 'false') !== 'false';
                
                if ($retry) {
                    \LengowConfiguration::resetCatalogIds();
                }
                
                return $this->render('@Modules/lengow/views/templates/admin/home/connection_catalog.html.twig', [
                    'shopCollection' => \LengowShop::getActiveShops(),
                    'catalogList' => $this->getCatalogList(),
                ]);
                
            case 'link_catalogs':
                $catalogSelected = $request->request->get('catalogSelected', []);
                $catalogsLinked = true;
                
                if (!empty($catalogSelected)) {
                    $catalogsLinked = $this->saveCatalogsLinked($catalogSelected);
                }
                
                $displayConnectionResult = $module->display(
                    _PS_MODULE_LENGOW_DIR_,
                    'views/templates/admin/home/connection_catalog_failed.html.twig'
                );
                
                return new JsonResponse([
                    'success' => $catalogsLinked,
                    'content' => preg_replace('/\r|\n/', '', $displayConnectionResult),
                ]);
                
            default:
                return new JsonResponse(['error' => 'Unknown action'], 400);
        }
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
        $accountId = \LengowConnector::getAccountIdByCredentials($accessToken, $secret);
        if ($accountId) {
            return \LengowConfiguration::setAccessIds([
                \LengowConfiguration::ACCOUNT_ID => $accountId,
                \LengowConfiguration::ACCESS_TOKEN => $accessToken,
                \LengowConfiguration::SECRET => $secret,
            ]);
        }
        
        return false;
    }
    
    /**
     * Connect cms with Lengow
     *
     * @return bool
     */
    private function connectCms(): bool
    {
        $cmsToken = \LengowMain::getToken();
        $cmsConnected = \LengowSync::syncCatalog(true);
        
        if (!$cmsConnected) {
            $syncData = json_encode(\LengowSync::getSyncData());
            $result = \LengowConnector::queryApi(\LengowConnector::POST, \LengowConnector::API_CMS, [], $syncData);
            
            if (isset($result->common_account)) {
                $cmsConnected = true;
                $messageKey = 'log.connection.cms_creation_success';
            } else {
                $messageKey = 'log.connection.cms_creation_failed';
            }
        } else {
            $messageKey = 'log.connection.cms_already_exist';
        }
        
        \LengowMain::log(
            \LengowLog::CODE_CONNECTION,
            \LengowMain::setLogMessage($messageKey, ['cms_token' => $cmsToken])
        );
        
        if (!$cmsConnected) {
            \LengowConfiguration::resetAccessIds();
            \LengowConfiguration::resetAuthorizationToken();
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
        $activeShops = \LengowShop::getActiveShops(true);
        if (empty($activeShops)) {
            return \LengowCatalog::hasCatalogNotLinked();
        }
        
        return false;
    }
    
    /**
     * Get all catalogs available in Lengow
     *
     * @return array
     */
    private function getCatalogList(): array
    {
        $activeShops = \LengowShop::getActiveShops(true);
        if (empty($activeShops)) {
            return \LengowCatalog::getCatalogList();
        }
        
        return [];
    }
    
    /**
     * Save catalogs linked in database and send data to Lengow with call API
     *
     * @param array $catalogSelected
     *
     * @return bool
     */
    private function saveCatalogsLinked(array $catalogSelected): bool
    {
        $catalogsByShops = [];
        foreach ($catalogSelected as $catalog) {
            $catalogsByShops[$catalog['shopId']] = $catalog['catalogId'];
        }
        
        if (!empty($catalogsByShops)) {
            foreach ($catalogsByShops as $idShop => $catalogIds) {
                \LengowConfiguration::setCatalogIds($catalogIds, $idShop);
                \LengowConfiguration::setActiveShop($idShop);
            }
            
            \LengowConfiguration::updateGlobalValue(\LengowConfiguration::LAST_UPDATE_SETTING, time());
            
            $catalogsLinked = \LengowCatalog::linkCatalogs($catalogsByShops);
            $messageKey = $catalogsLinked
                ? 'log.connection.link_catalog_success'
                : 'log.connection.link_catalog_failed';
            
            \LengowMain::log(
                \LengowLog::CODE_CONNECTION,
                \LengowMain::setLogMessage($messageKey)
            );
            
            return $catalogsLinked;
        }
        
        return false;
    }
}
