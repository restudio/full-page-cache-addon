<?php

namespace ride\library\log\listener;

use ride\library\log\LogMessage;

/**
 * Interface for a log listener
 */
interface LogListener {

    /**
     * Logs a message to this listener
     * @param \ride\library\log\LogMessage $message
     * @return null
     */
    public function logMessage(LogMessage $message);

}