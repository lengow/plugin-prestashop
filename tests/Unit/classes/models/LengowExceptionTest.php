<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowExceptionTest extends TestCase
{
    /**
     * @var Exception
     */
    protected $exception;

    /**
     * setup
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->exception = new \LengowException('Hello world');
    }

    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowException::class,
            $this->exception,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
