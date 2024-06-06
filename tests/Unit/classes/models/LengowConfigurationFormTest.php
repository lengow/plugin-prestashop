<?php


namespace Lengow\Connector\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use LengowConfigurationForm;


class LengowConfigurationFormTest extends TestCase
{
    /**
     *
     * @var LengowConfigurationForm
     */
    protected $form;

    /**
     * setup
     *
     * @return void
     */
    public function setup(): void
    {
        $this->form = new LengowConfigurationForm([]);
    }

    /**
     * test class
     */
    public function testClassInstantiation()
    {
        $this->assertInstanceOf(
            LengowConfigurationForm::class,
            $this->form,
            '[Test Class Instantiation] Check class instantiation'
        );
    }
}
