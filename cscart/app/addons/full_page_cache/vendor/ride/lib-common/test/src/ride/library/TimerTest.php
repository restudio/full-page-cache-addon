<?php

namespace ride\library;

use \PHPUnit_Framework_TestCase;

class TimerTest extends PHPUnit_Framework_TestCase {

    public function testGetTime() {
        $timer = new Timer();

        time_nanosleep(0, 200000000);
        $result1 = $timer->getTime();
        time_nanosleep(0, 300000000);
        $result2 = $timer->getTime(true);
        time_nanosleep(0, 100000000);
        $result3 = $timer->getTime();

        $this->assertTrue(0.200 <= $result1 && $result1 <= 0.205, $result1);
        $this->assertTrue(0.500 <= $result2 && $result2 <= 0.505, $result2);
        $this->assertTrue(0.100 <= $result3 && $result3 <= 0.105, $result3);
    }

}