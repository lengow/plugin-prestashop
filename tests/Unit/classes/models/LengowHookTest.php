<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowHookTest extends TestCase
{
    /**
     * @var \LengowHook
     */
    protected $hook;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $module = new \Lengow();
        $this->hook = new \LengowHook($module);
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowHook::class,
            $this->hook,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
