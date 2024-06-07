<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowImportTest extends TestCase
{
    /**
     * @var \LengowImport
     */
    protected $import;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->import = new \LengowImport([]);
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowImport::class,
            $this->import,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
