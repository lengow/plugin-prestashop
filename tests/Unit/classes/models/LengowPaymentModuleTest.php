<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowPaymentModuleTest extends TestCase
{
    /**
     * @var \LengowPaymentModule
     */
    protected $paymentModule;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->paymentModule = new \LengowPaymentModule();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowPaymentModule::class,
            $this->paymentModule,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
