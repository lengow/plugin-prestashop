<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowExportTest extends TestCase
{
    /**
     * @var \LengowExport
     */
    protected $export;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->export = new \LengowExport([]);
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowExport::class,
            $this->export,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
