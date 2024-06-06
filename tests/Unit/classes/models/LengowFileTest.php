<?php


namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use LengowFile;
use LengowMain;

class LengowFileTest extends TestCase
{
    /**
     *
     * @var LengowFile
     */
    protected $file;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->file = new LengowFile(LengowMain::FOLDER_LOG,'unit-test-log.txt');
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            LengowFile::class,
            $this->file,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
