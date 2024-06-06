<?php


namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use LengowCountry;


class LengowCountryTest extends TestCase
{
    /**
     *
     * @var LengowCountry
     */
    protected $country;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->country = new LengowCountry();
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            LengowCountry::class,
            $this->country,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
