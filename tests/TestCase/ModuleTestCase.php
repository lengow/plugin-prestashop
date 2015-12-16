<?php

namespace PrestaShop\PrestaShop\Tests\TestCase;

use PHPUnit_Framework_TestCase;
use Db;
use Context;
use Employee;
use SplFileInfo;

class ModuleTestCase extends PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        require_once(_PS_CONFIG_DIR_ . '/config.inc.php');
    }

    public function setUp()
    {
        $employee = new Employee();
        $employee->getByEmail("pub@prestashop.com");

        $context = Context::getContext();
        $context->employee = $employee;
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
                    if ($data[$headers["ID"]] == $productId) {
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
