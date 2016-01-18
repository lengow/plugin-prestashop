<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use Currency;
use Context;
use Db;
use Module;
use Configuration;
use LengowMain;
use LengowExport;
use LengowExportException;
use LengowFeed;
use LengowProduct;
use Assert;
use Feature;

class ProductTest extends ModuleTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        parent::setUp();

        Configuration::updatevalue('LENGOW_CARRIER_DEFAULT', 1);
        Configuration::updatevalue('LENGOW_EXPORT_FORMAT', 'csv');
        Configuration::updatevalue('LENGOW_EXPORT_FULLNAME', 0);
        Configuration::updatevalue('LENGOW_EXPORT_FILE', 0);
        Configuration::updatevalue('LENGOW_EXPORT_SELECTION', 0);
        Context::getContext()->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

        //load module
        Module::getInstanceByName('lengow');

    }

    /**
     * Test getImageUrlCombination
     *
     * @test
     * @covers LengowProduct::getImageUrlCombination
     */
    public function getImageUrlCombination()
    {

        $fixture = new Fixture();
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/max_image.yml'
        );

        $productId = $productAttributeId = $langId = 1;

        $product = new \LengowProduct($productId, $langId);
        $this->assertEquals(10, count($product->getImageUrlCombination()[$productAttributeId]));
    }

    /**
     * Test getMaxImageType when table is empty
     *
     * @test
     * @expectedException        LengowExportException
     * @expectedExceptionMessage Cant find Image type size, check your table ps_image_type
     */
    public function getMaxImageTypeWhenEmpty()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Product/empty_image_type.yml'
        );
        LengowProduct::getMaxImageType();
    }

    /**
     * Test getMaxImageType
     *
     * @test
     * @covers LengowProduct::getMaxImageType
     */
    public function getMaxImageType()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Product/get_max_image_type.yml'
        );

        $name = LengowProduct::getMaxImageType();
        $this->assertEquals('thickbox_default', $name, 'Max size is thickbox_default');
    }

    /**
     * Test getMaxImageType
     *
     * @test
     * @covers LengowProduct::publish
     */
    public function publish()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Product/no_lengow_selection.yml'
        );
        $result = Db::getInstance()->executeS('SELECT COUNT(*) as total FROM '._DB_PREFIX_.'lengow_product');
        $this->assertEquals(0, $result[0]['total'], 'Product selection is empty');

        LengowProduct::publish(1, 1, 1);

        $result = Db::getInstance()->executeS('SELECT COUNT(*) as total FROM '._DB_PREFIX_.'lengow_product');
        $this->assertEquals(1, $result[0]['total'], 'One product is selected');

        LengowProduct::publish(1, 0, 1);
        $result = Db::getInstance()->executeS('SELECT COUNT(*) as total FROM '._DB_PREFIX_.'lengow_product');
        $this->assertEquals(0, $result[0]['total'], 'Product selection is empty');

        LengowProduct::publish(1, 1, 1);
        LengowProduct::publish(1, 1, 2);
        $result = Db::getInstance()->executeS('SELECT COUNT(*) as total FROM '._DB_PREFIX_.'lengow_product
        WHERE id_shop = 1 ');
        $this->assertEquals(1, $result[0]['total'], 'One product for shop 1');
        $result = Db::getInstance()->executeS('SELECT COUNT(*) as total FROM '._DB_PREFIX_.'lengow_product
        WHERE id_shop = 2 ');
        $this->assertEquals(1, $result[0]['total'], 'One product for shop 2');

        LengowProduct::publish(1, 0, 1);
        $result = Db::getInstance()->executeS('SELECT COUNT(*) as total FROM '._DB_PREFIX_.'lengow_product
        WHERE id_shop = 1 ');
        $this->assertEquals(0, $result[0]['total'], 'No product for shop 1');
        $result = Db::getInstance()->executeS('SELECT COUNT(*) as total FROM '._DB_PREFIX_.'lengow_product
        WHERE id_shop = 2 ');
        $this->assertEquals(1, $result[0]['total'], 'One product for shop 2');
    }
}
