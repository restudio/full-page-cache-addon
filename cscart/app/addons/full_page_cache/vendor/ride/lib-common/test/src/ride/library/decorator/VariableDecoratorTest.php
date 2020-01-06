<?php

namespace ride\library\decorator;

use \PHPUnit_Framework_TestCase;

class VariableDecoratorTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider providerDecorate
     */
    public function testDecorate($expected, $value) {
        $decorator = new VariableDecorator();

        $this->assertEquals($expected, $decorator->decorate($value));
    }

    public function providerDecorate() {
        return array(
            array("\"test\"", "test"),
            array("true", true),
            array("false", false),
            array("null", null),
            array("[0 => 1, 1 => 2]", array(1, 2)),
            array("ride\\library\\decorator\\VariableDecoratorTest", $this),
        );
    }

    public function testDecorateWithResource() {
        $decorator = new VariableDecorator();

        $handle = fopen(__FILE__, 'r');

        $this->assertEquals('#resource', $decorator->decorate($handle));

        fclose($handle);
    }

}