<?php

namespace ride\library\decorator;

use \DateTime;
use \PHPUnit_Framework_TestCase;

class DateFormatDecoratorTest extends PHPUnit_Framework_TestCase {

    public function testSetAndGetDateFormat() {
        $decorator = new DateFormatDecorator();

        $dateFormat = $decorator->getDateFormat();

        $this->assertNull($dateFormat);

        $dateFormat = 'Y-m-d';

        $decorator->setDateFormat($dateFormat);

        $dateFormat = $decorator->getDateFormat();

        $this->assertNotNull($dateFormat);
    }

    /**
     * @expectedException ride\library\decorator\exception\DecoratorException
     */
    public function testSetDateFormatThrowsExceptionOnInvalidDateFormat() {
        $decorator = new DateFormatDecorator();

        $decorator->setDateFormat($this);
    }

    /**
     * @dataProvider providerDecorate
     */
    public function testDecorate($expected, $value, $dateFormat = null) {
        date_default_timezone_set('UTC');

        $decorator = new DateFormatDecorator();

        if ($dateFormat) {
            $decorator->setDateFormat($dateFormat);
        }

        $this->assertEquals($expected, $decorator->decorate($value));
    }

    public function providerDecorate() {
        return array(
            array("test", "test"),
            array(-500, -500),
            array("1970-01-01 00:00:00", 0),
            array("2013-06-30 08:56:13", 1372582573),
            array("2013-06-30", 1372582573, 'Y-m-d'),
            array("2013-06-30", new DateTime('30 June 2013'), 'Y-m-d'),
        );
    }

}