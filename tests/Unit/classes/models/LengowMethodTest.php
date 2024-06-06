<?php


namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use LengowMethod;

class LengowMethodTest extends TestCase
{
    /**
     *
     * @var LengowMethod
     */
    protected $method;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
       $this->method = new LengowMethod();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            LengowMethod::class,
            $this->method,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
