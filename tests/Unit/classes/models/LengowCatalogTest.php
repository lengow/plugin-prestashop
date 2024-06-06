<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowCatalogTest extends TestCase
{
    /**
     * @var \LengowCatalog
     */
    protected $catalog;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->catalog = new \LengowCatalog();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowCatalog::class,
            $this->catalog,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
