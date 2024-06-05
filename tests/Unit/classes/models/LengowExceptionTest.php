<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use LengowException;

class LengowExceptionTest extends TestCase
{
    /**
     * @var Exception
     */
    protected $exception;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     */
    public function setUp() : void
    {

        $this->exception = new LengowException('Hello world');
    }

    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            LengowException::class,
            $this->exception,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
