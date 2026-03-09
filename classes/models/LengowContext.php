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
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Centralised context provider for the Lengow module.
 *
 * In PrestaShop 9 the static Context::getContext() helper is deprecated in
 * favour of dependency injection.  This class acts as the single bridge:
 *  - The module constructor calls setContext() immediately after parent::__construct()
 *    so the instance is available before any other class is instantiated.
 *  - Symfony controllers call setContext() via the @required setter injection in
 *    AbstractLengowAdminController, ensuring the context is always wired on PS9.
 *  - All other module classes call LengowContext::getContext() to retrieve it.
 */
class LengowContext
{
    /** @var Context|null Context instance set at module boot */
    private static ?Context $instance = null;

    /**
     * Register the Context instance (called from the module constructor).
     */
    public static function setContext(Context $context): void
    {
        self::$instance = $context;
    }

    /**
     * Retrieve the Context instance.
     *
     * @throws \RuntimeException if setContext() was never called
     */
    public static function getContext(): Context
    {
        if (self::$instance === null) {
            throw new \RuntimeException(
                'LengowContext has not been initialised. '
                . 'Ensure the Lengow module is loaded before calling LengowContext::getContext().'
            );
        }

        return self::$instance;
    }
}
