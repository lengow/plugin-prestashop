<?php

namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;

class LengowListTest extends TestCase
{
    /**
     * @var \LengowList
     */
    protected $list;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->list = new \LengowList(
            [
                'id' => 1,
                'fields_list' => '',
                'selection' => '',
                'identifier' => '',
                'controller' => '',
                'sql' => '',
            ]
        );
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            \LengowList::class,
            $this->list,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
