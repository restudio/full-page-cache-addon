<?php

namespace ride\library\log\listener;

use ride\library\decorator\Decorator;
use ride\library\decorator\LogMessageDecorator;
use ride\library\log\LogMessage;

/**
 * Log listener to echo log items to the screen
 */
abstract class AbstractLogListener implements LogListener {

    /**
     * Maximum level to log
     * @var integer
     */
    protected $level;

    /**
     * Decorator for log messages
     * @var \ride\library\decorator\Decorator
     */
    protected $logMessageDecorator;

    /**
     * Sets the log level
     * @param integer $level 0 for all levels, see LogMessage level constants
     * @return null
     * @see LogMessage
     */
    public function setLevel($level) {
        $this->level = $level;
    }

    /**
     * Gets the log level
     * @return integer
     */
    public function getLevel() {
        return $this->level;
    }

    /**
     * Sets the decorator for the log messages
     * @param Decorator $logMessageDecorator
     * @return null
     */
    public function setLogMessageDecorator(Decorator $logMessageDecorator) {
        $this->logMessageDecorator = $logMessageDecorator;
    }

    /**
     * Gets the decorator for the log messages
     * @return Decorator
     */
    public function getLogMessageDecorator() {
        if (!$this->logMessageDecorator) {
            $this->logMessageDecorator = new LogMessageDecorator();
        }

        return $this->logMessageDecorator;
    }

    /**
     * Logs a message to this listener
     * @param \ride\library\log\LogMessage $message
     * @return null
     */
    public function logMessage(LogMessage $message) {
        if (!$this->isLoggable($message)) {
            return;
        }

        $this->log($message);
    }

    /**
     * Checks if the log message should be logged
     * @param \ride\library\log\LogMessage $message
     * @return boolean True to log, false otherwise
     */
    protected function isLoggable(LogMessage $message) {
        $level = $message->getLevel();

        if (!$this->level || $this->level & $level) {
            return true;
        }

        return false;
    }

    /**
     * Performs the actual logging
     * @param \ride\library\log\LogMessage $message
     * @return null
     */
    abstract protected function log(LogMessage $message);

    /**
     * Get the output string of a log item
     * @param \ride\library\log\LogMessage $message
     * @return string
     */
    protected function getLogMessageAsString(LogMessage $message) {
        return $this->getLogMessageDecorator()->decorate($message);
    }

}