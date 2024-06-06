<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowTranslationTest extends TestCase
{
    /**
     * @var \LengowTranslation
     */
    protected $translate;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->translate = new \LengowTranslation('en');
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowTranslation::class,
            $this->translate,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
