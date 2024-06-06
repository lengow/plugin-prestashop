<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowFeedTest extends TestCase
{
    /**
     * @var \LengowFeed
     */
    protected $feed;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->feed = new \LengowFeed(false, \LengowFeed::FORMAT_CSV, false);
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowFeed::class,
            $this->feed,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
