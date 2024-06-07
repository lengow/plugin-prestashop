<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowToolboxElementTest extends TestCase
{
    /**
     * @var \LengowToolboxElement
     */
    protected $toolboxElement;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->toolboxElement = new \LengowToolboxElement();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowToolboxElement::class,
            $this->toolboxElement,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
