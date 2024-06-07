<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowSyncTest extends TestCase
{
    /**
     * @var \LengowSync
     */
    protected $sync;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->sync = new \LengowSync();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowSync::class,
            $this->sync,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
