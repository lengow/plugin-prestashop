<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use Currency;
use LengowStatistic;
use LengowConnector;
use Db;
use Module;
use Assert;
use Feature;
use Cache;
use Shop;
use Tools;

class StatisticTest extends ModuleTestCase
{
    /**
     * Test get
     *
     * @test
     * @covers LengowStatistic::get
     */
    public function get()
    {

        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Statistic/get_first.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Statistic/get_two.json'
        );
        $return = LengowStatistic::get(true);

        $this->assertEquals(array(
            'total_order' => '2 001,00 €',
            'nb_order' => 125.0,
            'average_order' => '15,43 €',
            'currency' => '€',
        ), $return);
    }

    /**
     * Test get
     *
     * @test
     * @covers LengowStatistic::get
     */
    public function getEmptyCurrency()
    {

        LengowConnector::$test_fixture_path = array(
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Statistic/get_empty_currency.json',
            _PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/Statistic/get_empty_currency.json'
        );
        $return = LengowStatistic::get(true);

        $this->assertEquals(array(
            'total_order' => '1 201,80',
            'nb_order' => 90.0,
            'average_order' => '13,35',
            'currency' => '¥',
        ), $return);
    }
}
