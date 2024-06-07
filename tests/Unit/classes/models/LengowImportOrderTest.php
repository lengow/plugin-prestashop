<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowImportOrderTest extends TestCase
{
    /**
     * @var \LengowImportOrder
     */
    protected $importOrder;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $context = \Context::getContext();
        $this->importOrder = new \LengowImportOrder(
            [
                \LengowImportOrder::PARAM_CONTEXT => $context,
                \LengowImportOrder::PARAM_SHOP_ID => $context->shop->id,
                \LengowImportOrder::PARAM_SHOP_GROUP_ID => $context->shop->id_shop_group,
                \LengowImportOrder::PARAM_LANG_ID => $context->language->id,
                \LengowImportOrder::PARAM_FORCE_SYNC => false,
                \LengowImportOrder::PARAM_FORCE_PRODUCT => false,
                \LengowImportOrder::PARAM_DEBUG_MODE => false,
                \LengowImportOrder::PARAM_LOG_OUTPUT => false,
                \LengowImportOrder::PARAM_MARKETPLACE_SKU => '',
                \LengowImportOrder::PARAM_DELIVERY_ADDRESS_ID => '',
                \LengowImportOrder::PARAM_ORDER_DATA => '',
                \LengowImportOrder::PARAM_PACKAGE_DATA => '',
                \LengowImportOrder::PARAM_FIRST_PACKAGE => '',
                \LengowImportOrder::PARAM_IMPORT_ONE_ORDER => false,
            ]
        );
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowImportOrder::class,
            $this->importOrder,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
