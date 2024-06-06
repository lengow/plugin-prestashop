<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowLogTest extends TestCase
{
    /**
     * @var \LengowLog
     */
    protected $log;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->log = new \LengowLog();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowLog::class,
            $this->log,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
