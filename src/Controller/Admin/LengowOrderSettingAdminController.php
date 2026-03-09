<?php

/**
 * Copyright 2017 Lengow SAS.
 * Licensed under the Apache License, Version 2.0
 */
declare(strict_types=1);

namespace PrestaShop\Module\Lengow\Controller\Admin;

use PrestaShopBundle\Security\Attribute\AdminSecurity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LengowOrderSettingAdminController extends AbstractLengowAdminController
{
    #[AdminSecurity("is_granted('read', request.get('_legacy_controller'))")]
    public function indexAction(Request $request): Response
    {
        $lengowController = new \LengowOrderSettingController($this->legacyContext, $this->twig, true);
        $response = $this->handleLegacyPostAction($request, $lengowController);
        if ($response instanceof Response) {
            return $response;
        }

        return $this->renderLegacyPage('@Modules/lengow/views/templates/admin/lengow_order_setting/view.html.twig', $lengowController);
    }
}
