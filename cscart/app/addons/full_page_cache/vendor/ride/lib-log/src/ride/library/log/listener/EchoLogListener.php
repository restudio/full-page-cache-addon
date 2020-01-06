<?php

namespace ride\library\log\listener;

use ride\library\log\LogMessage;

/**
 * Log listener to echo log items
 */
class EchoLogListener extends AbstractLogListener {

    /**
     * Performs the actual logging
     * @param \ride\library\log\LogMessage $message
     * @return null
     */
    protected function log(LogMessage $message) {
        echo $this->getLogMessageAsString($message);
    }

}