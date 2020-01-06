<?php

namespace ride\library\log;

use ride\library\log\listener\BrowseableLogListener;
use ride\library\log\listener\LogListener;
use ride\library\StringHelper;
use ride\library\Timer;

use \Exception;

/**
 * Log interface
 */
class Log {

    /**
     * Timer of this Log
     * @var \ride\library\Timer
     */
    protected $timer;

    /**
     * Id of this log session
     * @var string
     */
    protected $id;

    /**
     * Client of the request
     * @var string
     */
    protected $client;

    /**
     * Log listeners
     * @var array
     */
    protected $listeners;

    /**
     * Constructs a new instance
     * @param string $id Id of the log session
     * @param string $client Id of the application client
     * @param \ride\library\Timer $timer Timer of the application run
     * @return null
     */
    public function __construct($id = null, $client = null, Timer $timer = null) {
        if (!$timer) {
            $timer = new Timer();
        }

        $this->timer = $timer;
        $this->id = $id;
        $this->client = $client;

        $this->listeners = array();
    }

    /**
     * Gets the microtime of this Log
     * @return float
     */
    public function getTime() {
        return $this->timer->getTime();
    }

    /**
     * Sets the id of the log session
     * @param string $id
     * @return null
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Gets the id of the log session
     * @return string
     */
    public function getId() {
        if (!$this->id) {
            $this->id = StringHelper::generate();
        }

        return $this->id;
    }

    /**
     * Sets the client of this log
     * @param string $client
     * @return null
     */
    public function setClient($client) {
        $this->client = $client;
    }

    /**
     * Gets the client of this log
     * @return string
     */
    public function getClient() {
        return $this->client;
    }

    /**
     * Adds a listener
     * @param \ride\library\log\listener\LogListener $listener
     * @return null
     */
    public function addLogListener(LogListener $listener) {
        $this->listeners[] = $listener;
    }

    /**
     * Removes a listener
     * @param \ride\library\log\listener\LogListener $listener
     * @return null
     */
    public function removeLogListener(LogListener $listener) {
        foreach ($this->listeners as $index => $logListener) {
            if ($logListener === $listener) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * Gets all the listeners
     * @return array
     */
    public function getLogListeners() {
        return $this->listeners;
    }

    /**
     * Adds a debug message
     * @param string $title
     * @param string $description
     * @param string $source
     * @return null
     */
    public function logDebug($title, $description = null, $source = null) {
        $message = new LogMessage(LogMessage::LEVEL_DEBUG, $title, $description, $source);

        $this->logMessage($message);
    }

    /**
     * Adds a error message
     * @param string $title
     * @param string $description
     * @param string $source
     * @return null
     */
    public function logError($title, $description = null, $source = null) {
        $message = new LogMessage(LogMessage::LEVEL_ERROR, $title, $description, $source);

        $this->logMessage($message);
    }

    /**
     * Adds a exception
     * @param Exception $exception
     * @param string $source
     * @return null
     */
    public function logException(Exception $exception, $source = null) {
        $stack = array();

        do {
            $message = $exception->getMessage();

            $title = get_class($exception) . (!empty($message) ? ': ' . $message : '');
            $description = $exception->getTraceAsString();

            $stack[] = new LogMessage(LogMessage::LEVEL_ERROR, $title, $description, $source);

            $exception = $exception->getPrevious();
        } while ($exception);

        array_reverse($stack);

        foreach ($stack as $message) {
            $this->logMessage($message);
        }
    }

    /**
     * Adds a information message
     * @param string $title
     * @param string $description
     * @param string $source
     * @return null
     */
    public function logInformation($title, $description = null, $source = null) {
        $message = new LogMessage(LogMessage::LEVEL_INFORMATION, $title, $description, $source);

        $this->logMessage($message);
    }

    /**
     * Adds a warning message
     * @param string $title
     * @param string $description
     * @param string $source
     * @return null
     */
    public function logWarning($title, $description = null, $source = null) {
        $message = new LogMessage(LogMessage::LEVEL_WARNING, $title, $description, $source);

        $this->logMessage($message);
    }

    /**
     * Logs a message to the listeners
     * @param LogMessage $message
     * @return null
     */
    public function logMessage(LogMessage $message) {
        $message->setId($this->getId());
        $message->setClient($this->client);
        $message->setMicrotime($this->getTime());

        foreach ($this->listeners as $listener) {
            $listener->logMessage($message);
        }
    }

    /**
     * Gets a log session by id
     * @param string $id Id of the session
     * @return \ride\library\log\LogSession
     */
    public function getLogSession($id) {
        foreach ($this->listeners as $listener) {
            if (!$listener instanceof BrowseableLogListener) {
                continue;
            }

            $session = $listener->getLogSession($id);
            if ($session) {
                return $session;
            }
        }

        return null;
    }

    /**
     * Finds log sessions
     * @param array $options Options for the find
     * <ul>
     * <li>limit: number of entries to fetch</li>
     * <li>page: page number</li>
     * </li>
     * @param integer $pages Total number of pages will be set to this variable
     * @return array Array with LogSession instances
     */
    public function getLogSessions(array $options = null, &$pages = null) {
        foreach ($this->listeners as $listener) {
            if (!$listener instanceof BrowseableLogListener) {
                continue;
            }

            $sessions = $listener->getLogSessions($options, $pages);
            if ($sessions) {
                return $sessions;
            }
        }

        return array();
    }

}
