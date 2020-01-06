<?php

namespace ride\library;

use \ErrorException;

/**
 * Error handler to convert handable errors into exceptions
 */
class ErrorHandler {

    /**
     * Array with error levels which cannot be converted to an ErrorException
     * @var array
     */
    private $unhandableErrorLevels = array(
        E_ERROR,
        E_STRICT,
        E_PARSE,
        E_CORE_ERROR,
        E_CORE_WARNING,
        E_COMPILE_ERROR,
        E_COMPILE_WARNING
    );

    /**
     * Throws an exception for all handable errors
     * @param int $errno Error number
     * @param string $errstr Error message
     * @param string $errfile File where the error occured
     * @param int $errline Line number in the file where the error occured
     * @return boolean
     * @throws ErrorException when error reporting is on and the error number is throwable
     */
    public function handleError($errno, $errstr, $errfile, $errline) {
        $errorLevel = error_reporting();

        if ($errorLevel != 0 && !in_array($errno, $this->unhandableErrorLevels)) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }

        return false;
    }

    /**
     * Registers this error handler to PHP
     * @return null
     */
    public function registerErrorHandler() {
        set_error_handler(array($this, 'handleError'));

        error_reporting(E_ALL);
    }

}