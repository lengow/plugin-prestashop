<?php

/**
 * Copyright 2017 Lengow SAS.
 * Licensed under the Apache License, Version 2.0
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
class LengowMainSettingAdminController extends AbstractLengowAdminController
{
    #[AdminSecurity("is_granted('read', request.get('_legacy_controller'))")]
    public function indexAction(Request $request): Response
    {
        $lengowController = new \LengowMainSettingController();
        $action = $request->get('action', false);
        if ($action) {
            ob_start();
            $lengowController->postProcess();
            $output = ob_get_clean();
            if ($output !== '') {
                return new JsonResponse(json_decode($output, true) ?? [], Response::HTTP_OK, [], false);
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
