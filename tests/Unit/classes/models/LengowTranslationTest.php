<?php


namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use LengowTranslation;
use Context;

class LengowTranslationTest extends TestCase
{
    /**
     *
     * @var LengowTranslation
     */
    protected $translate;

    //protected $translateClass = 'LengowTranslation';

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->translate = new LengowTranslation('fr');
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            LengowTranslation::class,
            $this->translate,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
