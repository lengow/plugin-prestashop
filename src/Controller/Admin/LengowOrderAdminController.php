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

declare(strict_types=1);

namespace PrestaShop\Module\Lengow\Controller\Admin;

use PrestaShopBundle\Security\Attribute\AdminSecurity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit;
}

class LengowOrderAdminController extends AbstractLengowAdminController
{
    #[AdminSecurity("is_granted('read', request.get('_legacy_controller'))")]
    public function indexAction(Request $request): Response
    {
        $lengowController = new \LengowOrderController();

        $action = $request->get('action', false);
        if ($action) {
            ob_start();
            $lengowController->postProcess();
            $output = ob_get_clean();

            if ($output !== '') {
                return new JsonResponse(
                    json_decode($output, true) ?? [],
                    Response::HTTP_OK,
                    [],
                    false
                );
            }

            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $lengowController->postProcess();

        ob_start();
        $lengowController->forceDisplay();
        $pageContent = ob_get_clean();

        return $this->render(
            '@Modules/lengow/views/templates/admin/symfony/layout.html.twig',
            ['page_content' => $pageContent]
        );
    }
}
