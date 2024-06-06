<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowBackupTest extends TestCase
{
    /**
     * @var \LengowBackup
     */
    protected $backup;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->backup = new \LengowBackup();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowBackup::class,
            $this->backup,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
