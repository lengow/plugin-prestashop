<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use Currency;
use Context;
use Db;
use Module;
use LengowConfiguration;
use Assert;
use Feature;
use Cache;
use LengowTool;
use Tools;

class TranslationTest extends ModuleTestCase
{

    /**
     * Test loadFile
     *
     * @test
     * @covers LengowTranslation::loadFile
     */
    public function loadFile()
    {
        $translation = new \LengowTranslation();
        $return = $translation->loadFile('en', _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Translation/en.csv');
        $this->assertTrue($return);

        $return = $translation->loadFile('en', _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Translation/empty.csv');
        $this->assertFalse($return);

        $return = $translation->loadFile('en', '/missing_files.csv');
        $this->assertFalse($return);
    }

    /**
     * Test t
     *
     * @test
     * @covers LengowTranslation::t
     */
    public function t()
    {
        $translation = new \LengowTranslation();
        $return = $translation->loadFile('en', _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Translation/en.csv');
        $this->assertTrue($return);
        $return = $translation->loadFile('fr', _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Translation/fr.csv');
        $this->assertTrue($return);

        $this->assertEquals('Un', $translation->t('order.screen.one'));
        $this->assertEquals('Two', $translation->t('order.screen.two'));
        $this->assertEquals('Missing Translation [order.screen.three]', $translation->t('order.screen.three'));
    }

    /**
     * Test translateFinal
     *
     * @test
     * @covers LengowTranslation::translateFinal
     */
    public function translateFinal()
    {
        $translation = new \LengowTranslation();
        $return = $translation->loadFile('en', _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Translation/en.csv');
        $this->assertTrue($return);
        $return = $translation->loadFile('fr', _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Translation/fr.csv');
        $this->assertTrue($return);

        $this->assertEquals('Question : %{first}/%{second}=%{third}', $translation->t('order.screen.question'));
        $this->assertEquals(
            'Question : 10/2=5',
            $translation->t(
                'order.screen.question',
                array(
                    'first' => '10',
                    'second' => '2',
                    'third' => '5',
                )
            )
        );
    }
}
