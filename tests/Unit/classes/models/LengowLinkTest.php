<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowLinkTest extends TestCase
{
    /**
     * @var \LengowLink
     */
    protected $link;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->link = new \LengowLink();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowLink::class,
            $this->link,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
