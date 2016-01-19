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
use Cache;

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

        $product = new \LengowProduct($productId, $langId, array(
            'carrier' => LengowMain::getExportCarrier()
        ));
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
     * Test publish
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


    /**
     * Test getData
     *
     * @test
     * @covers LengowProduct::publish
     */
    public function getData()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(
            _PS_MODULE_DIR_ . 'lengow/tests/Module/Fixtures/Product/get_data.yml'
        );

        $product = new LengowProduct(1, 1, array(
            'carrier' => LengowMain::getExportCarrier()
        ));

        $this->assertEquals(1, $product->getData('id'));
        $this->assertEquals('NAME001', $product->getData('name'));
        $this->assertEquals('SIMPLESKU001', $product->getData('reference'));
        $this->assertEquals('SUPPLIERSKU001', $product->getData('supplier_reference'));
        $this->assertEquals('MANUFACTURER1', $product->getData('manufacturer'));
        $this->assertEquals('Chemisiers', $product->getData('category'));
        $this->assertEquals('Accueil > Femmes > Tops > Chemisiers', $product->getData('breadcrumb'));
        $this->assertEquals('DESCRIPTION001', $product->getData('description'));
        $this->assertEquals('SHORTDESCRIPTION001', $product->getData('short_description'));
        $this->assertEquals('<strong>DESCRIPTION001</strong>', $product->getData('description_html'));
        $this->assertEquals(round((4.9*1.2)+2, 2), round($product->getData('price'), 2));
        $this->assertEquals(0, round($product->getData('wholesale_price'), 2));
        $this->assertEquals(round(4.9+2, 2), round($product->getData('price_duty_free'), 2));
        $this->assertEquals(round(4.9*1.2+2, 2), round($product->getData('price_sale'), 2));
        $this->assertEquals(0, round($product->getData('price_sale_percent'), 2));
        $this->assertEquals(10, $product->getData('quantity'));
        $this->assertEquals(4.5, $product->getData('weight'));
        $this->assertEquals('9805120221231', $product->getData('ean'));
        $this->assertEquals('156416510654', $product->getData('upc'));
        $this->assertEquals(2.0, $product->getData('ecotax'));
        $this->assertEquals(true, $product->getData('active'));
        //$this->assertEquals(true, $product->getData('available'));
        $this->assertEquals('http://prestashop.unit.test/1-URL001-9805120221231.html', $product->getData('url'));
        $this->assertEquals(0, $product->getData('price_shipping'));
        $this->assertEquals(1, $product->getData('id_parent'));
        $this->assertEquals('Retrait en magasin', $product->getData('delivery_time'));
        //$this->assertEquals(1, $product->getData('sale_from'));
        //$this->assertEquals(1, $product->getData('sale_to'));
        $this->assertEquals('METAKEYWORDS001', $product->getData('meta_keywords'));
        $this->assertEquals('METADESCRIPTION001', $product->getData('meta_description'));
        $this->assertEquals(
            'http://prestashop.unit.test/1-URL001-9805120221231.html',
            $product->getData('url_rewrite')
        );
        $this->assertEquals('simple', $product->getData('type'));
        $this->assertEquals(null, $product->getData('variation'));
        $this->assertEquals('EUR', $product->getData('currency'));
        $this->assertEquals('new', $product->getData('condition'));
        $this->assertEquals('SUPPLIER1', $product->getData('supplier'));
        $this->assertEquals(true, $product->getData('availability'));
        $this->assertEquals('http://prestashop.unit.test/1-thickbox_default/URL001.jpg', $product->getData('image_1'));
        $this->assertEquals('http://prestashop.unit.test/2-thickbox_default/URL001.jpg', $product->getData('image_2'));
        $this->assertEquals('http://prestashop.unit.test/3-thickbox_default/URL001.jpg', $product->getData('image_3'));
        $this->assertEquals('http://prestashop.unit.test/4-thickbox_default/URL001.jpg', $product->getData('image_4'));
        $this->assertEquals('http://prestashop.unit.test/5-thickbox_default/URL001.jpg', $product->getData('image_5'));
        $this->assertEquals('http://prestashop.unit.test/6-thickbox_default/URL001.jpg', $product->getData('image_6'));
        $this->assertEquals('http://prestashop.unit.test/7-thickbox_default/URL001.jpg', $product->getData('image_7'));
        $this->assertEquals('http://prestashop.unit.test/8-thickbox_default/URL001.jpg', $product->getData('image_8'));
        $this->assertEquals('http://prestashop.unit.test/9-thickbox_default/URL001.jpg', $product->getData('image_9'));
        $this->assertEquals(
            'http://prestashop.unit.test/10-thickbox_default/URL001.jpg',
            $product->getData('image_10')
        );

        $product = new LengowProduct(10, 1, array(
            'carrier' => LengowMain::getExportCarrier()
        ));

        $this->assertEquals(10, $product->getData('id'));
        $this->assertEquals('NAME010', $product->getData('name'));
        $this->assertEquals('CONFIGSKU001', $product->getData('reference'));
        $this->assertEquals('SUPPLIERSKU010', $product->getData('supplier_reference'));
        $this->assertEquals('MANUFACTURER1', $product->getData('manufacturer'));
        $this->assertEquals('Robes', $product->getData('category'));
        $this->assertEquals('Accueil > Femmes > Robes', $product->getData('breadcrumb'));
        $this->assertEquals('DESCRIPTION010', $product->getData('description'));
        $this->assertEquals('SHORTDESCRIPTION010', $product->getData('short_description'));
        $this->assertEquals('DESCRIPTION010', $product->getData('description_html'));
        $this->assertEquals(round(5.9*1.2+3, 2), round($product->getData('price'), 2));
        $this->assertEquals(0, round($product->getData('wholesale_price'), 2));
        $this->assertEquals(round(5.9+3, 2), round($product->getData('price_duty_free'), 2));
        $this->assertEquals(round(5.9*1.2+3, 2), round($product->getData('price_sale'), 2));
        $this->assertEquals(0, round($product->getData('price_sale_percent'), 2));
        $this->assertEquals(0, $product->getData('quantity'));
        $this->assertEquals(1.4, $product->getData('weight'));
        $this->assertEquals('9805145721231', $product->getData('ean'));
        $this->assertEquals('156441510123', $product->getData('upc'));
        $this->assertEquals(3.0, $product->getData('ecotax'));
        $this->assertEquals(true, $product->getData('active'));
        //$this->assertEquals(true, $product->getData('available'));
        $this->assertEquals('http://prestashop.unit.test/10-URL010-9805145721231.html', $product->getData('url'));
        $this->assertEquals(0, $product->getData('price_shipping'));
        $this->assertEquals(10, $product->getData('id_parent'));
        $this->assertEquals('Retrait en magasin', $product->getData('delivery_time'));
        //$this->assertEquals(1, $product->getData('sale_from'));
        //$this->assertEquals(1, $product->getData('sale_to'));
        $this->assertEquals('METAKEYWORDS010', $product->getData('meta_keywords'));
        $this->assertEquals('METADESCRIPTION010', $product->getData('meta_description'));
        $this->assertEquals(
            'http://prestashop.unit.test/10-URL010-9805145721231.html',
            $product->getData('url_rewrite')
        );
        $this->assertEquals('parent', $product->getData('type'));
        //$this->assertEquals(null, $product->getData('variation'));
        $this->assertEquals('EUR', $product->getData('currency'));
        $this->assertEquals('new', $product->getData('condition'));
        $this->assertEquals('SUPPLIER1', $product->getData('supplier'));
        $this->assertEquals(false, $product->getData('availability'));
        $this->assertEquals('http://prestashop.unit.test/11-thickbox_default/URL010.jpg', $product->getData('image_1'));
        $this->assertEquals('http://prestashop.unit.test/12-thickbox_default/URL010.jpg', $product->getData('image_2'));
        $this->assertEquals('http://prestashop.unit.test/13-thickbox_default/URL010.jpg', $product->getData('image_3'));
        $this->assertEquals('http://prestashop.unit.test/14-thickbox_default/URL010.jpg', $product->getData('image_4'));
        $this->assertEquals('http://prestashop.unit.test/15-thickbox_default/URL010.jpg', $product->getData('image_5'));
        $this->assertEquals('http://prestashop.unit.test/16-thickbox_default/URL010.jpg', $product->getData('image_6'));
        $this->assertEquals('http://prestashop.unit.test/17-thickbox_default/URL010.jpg', $product->getData('image_7'));
        $this->assertEquals('', $product->getData('image_8'));
        $this->assertEquals('', $product->getData('image_9'));
        $this->assertEquals('', $product->getData('image_10'));

        $this->assertEquals('10_11', $product->getData('id', 11));
        $this->assertEquals('NAME010', $product->getData('name', 11));
        $this->assertEquals('ATTRIBUTE011', $product->getData('reference', 11));
        $this->assertEquals('SUPPLIERSKU011', $product->getData('supplier_reference', 11));
        $this->assertEquals('MANUFACTURER1', $product->getData('manufacturer', 11));
        $this->assertEquals('Robes', $product->getData('category', 11));
        $this->assertEquals('Accueil > Femmes > Robes', $product->getData('breadcrumb', 11));
        $this->assertEquals('DESCRIPTION010', $product->getData('description', 11));
        $this->assertEquals('SHORTDESCRIPTION010', $product->getData('short_description', 11));
        $this->assertEquals('DESCRIPTION010', $product->getData('description_html', 11));
        //todo see if price is correct for attribute
        $this->assertEquals(round(5.9*1.2+3, 2), round($product->getData('price', 11), 2));
        $this->assertEquals(0, round($product->getData('wholesale_price', 11), 2));
        $this->assertEquals(round(5.9+3, 2), round($product->getData('price_duty_free', 11), 2));
        $this->assertEquals(round(5.9*1.2+3, 2), round($product->getData('price_sale', 11), 2));
        $this->assertEquals(0, round($product->getData('price_sale_percent'), 2));
        $this->assertEquals(10, $product->getData('quantity', 11));
        $this->assertEquals(6.6, $product->getData('weight', 11));
        $this->assertEquals('9805120228731', $product->getData('ean', 11));
        $this->assertEquals('156441510123', $product->getData('upc', 11));
        $this->assertEquals(1.1, $product->getData('ecotax', 11));
        $this->assertEquals(true, $product->getData('active', 11));
        //$this->assertEquals(true, $product->getData('available'));
        $this->assertEquals(
            'http://prestashop.unit.test/10-URL010-9805145721231.html#/pointure-35',
            $product->getData('url', 11)
        );
        $this->assertEquals(0, $product->getData('price_shipping'));
        $this->assertEquals(10, $product->getData('id_parent', 11));
        $this->assertEquals('Retrait en magasin', $product->getData('delivery_time'));
        //$this->assertEquals(1, $product->getData('sale_from'));
        //$this->assertEquals(1, $product->getData('sale_to'));
        $this->assertEquals('METAKEYWORDS010', $product->getData('meta_keywords', 11));
        $this->assertEquals('METADESCRIPTION010', $product->getData('meta_description', 11));
        $this->assertEquals(
            'http://prestashop.unit.test/10-URL010-9805145721231.html#/pointure-35',
            $product->getData('url_rewrite', 11)
        );
        $this->assertEquals('child', $product->getData('type', 11));
        //$this->assertEquals(null, $product->getData('variation'));
        $this->assertEquals('EUR', $product->getData('currency', 11));
        $this->assertEquals('new', $product->getData('condition', 11));
        $this->assertEquals('SUPPLIER1', $product->getData('supplier', 11));
        $this->assertEquals(true, $product->getData('availability', 11));
        $this->assertEquals(
            'http://prestashop.unit.test/11-thickbox_default/URL010.jpg',
            $product->getData('image_1', 11)
        );
        $this->assertEquals(
            'http://prestashop.unit.test/12-thickbox_default/URL010.jpg',
            $product->getData('image_2', 11)
        );
        $this->assertEquals(
            'http://prestashop.unit.test/13-thickbox_default/URL010.jpg',
            $product->getData('image_3', 11)
        );
        $this->assertEquals('', $product->getData('image_4', 11));
        $this->assertEquals('', $product->getData('image_5', 11));
        $this->assertEquals('', $product->getData('image_6', 11));
        $this->assertEquals('', $product->getData('image_7', 11));
        $this->assertEquals('', $product->getData('image_8', 11));
        $this->assertEquals('', $product->getData('image_9', 11));
        $this->assertEquals('', $product->getData('image_10', 11));
    }
}
