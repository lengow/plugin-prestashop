<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowActionTest extends TestCase
{
    /**
     * @var \LengowAction
     */
    protected $action;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->action = new \LengowAction();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowAction::class,
            $this->action,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
