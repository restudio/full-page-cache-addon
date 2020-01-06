<?php

namespace ride\library;

use \PHPUnit_Framework_TestCase;

class AutoloaderTest extends PHPUnit_Framework_TestCase {

    public function testAddIncludePathsAddsPhpIncludePathsWhenNullProvided() {
        $autoloader = new Autoloader();
        $autoloader->addIncludePaths();

        $this->assertEquals($autoloader->getIncludePaths(), explode(PATH_SEPARATOR, get_include_path()));
    }

}
