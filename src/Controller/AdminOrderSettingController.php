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
 * Lengow Order Settings Controller for PrestaShop 9
 */
class AdminOrderSettingController extends FrameworkBundleAdminController
{
    /**
     * Order Settings page
     *
     * @AdminSecurity("is_granted('read', 'AdminLengowOrderSetting')")
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        $locale = new \LengowTranslation();
        $lengowLink = new \LengowLink();
        $module = \Module::getInstanceByName('lengow');
        $currentController = 'LengowOrderSettingController';
        
        // Process form submission
        if ($request->isMethod('POST')) {
            $lengowController = new \LengowOrderSettingController();
            $lengowController->postProcess();
        }
        
        // Prepare display data using legacy controller
        $lengowController = new \LengowOrderSettingController();
        $lengowController->prepareDisplay();
        
        return $this->render('@Modules/lengow/views/templates/admin/order_setting/index.html.twig', [
            'locale' => $locale,
            'lengowPathUri' => $module->getPathUri(),
            'lengowUrl' => \LengowConfiguration::getLengowUrl(),
            'lengow_link' => $lengowLink,
            'displayToolbar' => 1,
            'current_controller' => $currentController,
            'total_pending_order' => \LengowOrder::countOrderToBeSent(),
            'merchantStatus' => \LengowSync::getStatusAccount(),
            'pluginData' => \LengowSync::getPluginData(),
            'pluginIsUpToDate' => \LengowSync::isPluginUpToDate(),
        ]);
    }
    
    /**
     * Handle AJAX actions for order settings page
     *
     * @AdminSecurity("is_granted('update', 'AdminLengowOrderSetting')")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxAction(Request $request): JsonResponse
    {
        // Delegate to legacy controller for AJAX handling
        $lengowController = new \LengowOrderSettingController();
        
        ob_start();
        $lengowController->postProcess();
        $output = ob_get_clean();
        
        if (!empty($output)) {
            $data = json_decode($output, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return new JsonResponse($data);
            }
            return new JsonResponse(['output' => $output]);
        }
        
        return new JsonResponse(['success' => true]);
    }
}
