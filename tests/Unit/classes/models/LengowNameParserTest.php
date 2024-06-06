<?php


namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use LengowNameParser;

class LengowNameParserTest extends TestCase
{
    /**
     *
     * @var LengowNameParser
     */
    protected $nameParser;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
       $this->nameParser = new LengowNameParser();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            LengowNameParser::class,
            $this->nameParser,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
