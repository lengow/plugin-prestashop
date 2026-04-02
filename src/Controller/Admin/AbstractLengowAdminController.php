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

use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

if (
    !class_exists('PrestaShopBundle\Controller\Admin\PrestaShopAdminController')
    && class_exists('PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController')
) {
    class_alias(
        'PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController',
        'PrestaShopBundle\Controller\Admin\PrestaShopAdminController'
    );
}

abstract class AbstractLengowAdminController extends PrestaShopAdminController
{
    abstract protected function getPageTitle(): string;
    protected \Context $legacyContext;

    public function __construct(
        LegacyContext $legacyContext,
        protected readonly Environment $twig,
    ) {
        $this->legacyContext = $legacyContext->getContext();
        \LengowContext::setContext($this->legacyContext);
    }

    protected function handleLegacyPostAction(Request $request, object $legacyController): ?Response
    {
        $action = (string) $request->get('action', '');
        if ($action === '') {
            return null;
        }

        $legacyControllerName = (string) $request->attributes->get('_legacy_controller', '');
        if ($request->isMethod('POST') && $legacyControllerName !== '' && !$this->isGranted('update', $legacyControllerName)) {
            return new JsonResponse(
                ['success' => false, 'message' => $this->trans('You do not have permission to edit this.', [], 'Admin.Notifications.Error')],
                Response::HTTP_FORBIDDEN
            );
        }

        $legacyController->postProcess();
        if (!method_exists($legacyController, 'consumeJsonResponse')) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $payload = $legacyController->consumeJsonResponse();
        if ($payload === null) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(
            $payload,
            Response::HTTP_OK,
            [],
            false
        );
    }

    protected function renderLegacyPage(string $template, object $legacyController): Response
    {
        $legacyController->display();

        return $this->render(
            $template,
            array_merge(
                $legacyController->getTemplateVars(),
                [
                    'base_layout' => '@Modules/lengow/views/templates/admin/twig/ps9_base.html.twig',
                    'layoutTitle' => $this->getPageTitle(),
                ]
            )
        );
    }
}
