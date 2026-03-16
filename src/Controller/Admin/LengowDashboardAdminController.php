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

declare(strict_types=1);

namespace PrestaShop\Module\Lengow\Controller\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

use \LengowDashboardController;
use PrestaShopBundle\Security\Attribute\AdminSecurity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LengowDashboardAdminController extends AbstractLengowAdminController
{
    #[AdminSecurity("is_granted('read', request.get('_legacy_controller'))")]
    public function indexAction(Request $request): Response
    {
        $lengowController = new LengowDashboardController($this->legacyContext, $this->twig, true);

        $response = $this->handleLegacyPostAction($request, $lengowController);
        if ($response instanceof Response) {
            return $response;
        }

        return $this->renderLegacyPage('@Modules/lengow/views/templates/admin/lengow_dashboard/view.html.twig', $lengowController);
    }
}
