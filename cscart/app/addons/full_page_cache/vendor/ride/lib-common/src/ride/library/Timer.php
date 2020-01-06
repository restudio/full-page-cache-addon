<?php

namespace ride\library;

/**
 * Stopwatch with a microsecond detail
 */
class Timer {

    /**
     * Start time of the timer in seconds
     * @var float
     */
    protected $start;

    /**
     * Constructs a new timer
     * @return null
     */
    public function __construct() {
       $this->reset();
    }

    /**
     * Gets the spent time since construction or the last reset
     * @param boolean $reset Set to true to reset the timer after getting the
     * time
     * @return float Spent time in seconds
     */
    public function getTime($reset = false) {
        $result = $this->getMicrotimeDifferenceWithStart();

        if ($reset) {
            $this->reset();
        }

        return $result;
    }

    /**
     * Resets this timer
     * @return null
     */
    public function reset() {
        $this->start = microtime(true);
    }

    /**
     * Gets the difference between now and the start time
     * @return float
     */
    protected function getMicrotimeDifferenceWithStart() {
        return microtime(true) - $this->start;
    }

}