<?php


namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use LengowGender;


class LengowGenderTest extends TestCase
{
    /**
     *
     * @var LengowGender
     */
    protected $gender;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->gender = new LengowGender();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            LengowGender::class,
            $this->gender,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
