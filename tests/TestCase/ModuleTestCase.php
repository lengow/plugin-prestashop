<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use PHPUnit_Framework_TestCase;
use Db;
use Context;
use Employee;
use DateTime;
use SplFileInfo;
use Configuration;
use Shop;

class ModuleTestCase extends PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        require_once(_PS_CONFIG_DIR_ . '/config.inc.php');
    }

    public static function tearDownAfterClass()
    {
        $fixture = new Fixture();
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/attribute_product.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_ .'lengow/tests/Module/Fixtures/features.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/before_feed.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/simple_product.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/variation_product.yml');
        $fixture->loadFixture(_PS_MODULE_DIR_.'lengow/tests/Module/Fixtures/pack_product.yml');

        Shop::setContext(Shop::CONTEXT_ALL);
        Configuration::updatevalue('LENGOW_CARRIER_DEFAULT', 1);
    }

    public function setUp()
    {
        $employee = new Employee();
        $employee->getByEmail("pub@prestashop.com");

        $context = Context::getContext();
        $context->employee = $employee;
    }

    /**
     * Assert that a date is valid
     *
     * @param $date string date in string
     * @param $format string date format ex : (Y-m-d)
     */
    public static function assertValidDatetime($date, $format)
    {
        $d = DateTime::createFromFormat($format, $date);
        self::assertTrue($d && $d->format($format) == $date);
    }

    /**
     * Read Last line of filename
     *
     * @param $file path of the filename
     * @return string last line of the file
     */
    public static function readLastLine($file)
    {
        $line = '';
        $f = fopen($file, 'r');
        $cursor = -1;
        fseek($f, $cursor, SEEK_END);
        $char = fgetc($f);

        /**
         * Trim trailing newline chars of the file
         */
        while ($char === "\n" || $char === "\r") {
            fseek($f, $cursor--, SEEK_END);
            $char = fgetc($f);
        }

        /**
         * Read until the start of file or first newline char
         */
        while ($char !== false && $char !== "\n" && $char !== "\r") {
            /**
             * Prepend the new char
             */
            $line = $char . $line;
            fseek($f, $cursor--, SEEK_END);
            $char = fgetc($f);
        }
        return $line;
    }

    /**
     * Asserts that a file contain a certain number of line
     *
     * @param  string filename
     * @param  string nbLine
     * @param  string newFilename
     * @param  string  $message
     * @throws PHPUnit_Framework_AssertionFailedError
     */
    public static function assertFileNbLine($filename, $nbLine, $newFilename, $message = '')
    {

        $info = new SplFileInfo($filename);
        $extension = $info->getExtension();

        //add one line for csv header
        if ($extension == "csv") {
            $nbLine++;
        }

        $newFilename = str_replace('flux-fr.csv', $newFilename.'.'.$extension, $filename);
        copy($filename, $newFilename);

        $findLine= exec("cat $filename | wc -l");
        self::assertEquals($nbLine, $findLine, $message);
    }


    /**
     * Asserts that a file contain certain column
     *
     * @param  string filename
     * @param  string columns
     * @param  string message
     * @throws PHPUnit_Framework_AssertionFailedError
     */
    public static function assertFileColumnEqual($filename, $columns, $message = '')
    {
        if (($handle = fopen($filename, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, "|")) !== false) {
                self::assertEquals($data, $columns, $message);
                break;
            }
            fclose($handle);
        }
    }

    /**
     * Asserts that a file contain certain values
     *
     * @param  string filename
     * @param  string nbLine
     * @param  string newFilename
     * @param  string  $message
     * @throws PHPUnit_Framework_AssertionFailedError
     */
    public static function assertFileValues($filename, $productId, $values, $message = '')
    {
        $headers = array();

        $i = 0;
        if (($handle = fopen($filename, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, "|")) !== false) {
                if ($i==0) {
                    $j=0;
                    foreach ($data as $column) {
                        $headers[str_replace('"', '', $column)] = $j;
                        $j++;
                    }
                } else {
                    if ($data[$headers["ID_PRODUCT"]] === (string)$productId) {
                        foreach ($values as $key => $value) {
                            self::assertEquals($data[$headers[$key]], $value, $message);
                        }
                    }
                }
                $i++;
            }
            fclose($handle);
        }
    }
}
