<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowInstallTest extends TestCase
{
    /**
     * @var \LengowInstall
     */
    protected $install;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $module = new \Lengow();
        $this->install = new \LengowInstall($module);
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowInstall::class,
            $this->install,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
