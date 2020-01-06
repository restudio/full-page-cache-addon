<?php

namespace ride\library\log;

use ride\library\log\exception\LogException;

/**
 * A container for log messages of the same log session
 */
class LogSession {

    /**
     * Id of the session
     * @var string
     */
    protected $id;

    /**
     * Title for the log session
     * @var string
     */
    protected $title;

    /**
     * Timestamp when the first message is added
     * @var integer
     */
    protected $date;

    /**
     * Total microtime of the request
     * @var integer
     */
    protected $microtime;

    /**
     * Client of the request (IP address, cli, ...)
     * @var string
     */
    protected $client;

    /**
     * Messages of this request
     * @var array
     */
    protected $messages;

    /**
     * Constructs a new log request
     * @return null
     */
    public function __construct() {
        $this->id = null;
        $this->date = null;
        $this->microtime = null;
        $this->client = null;
        $this->messages = array();
    }

    /**
     * Sets the id of this log
     * @param mixed $id
     * @return null
     * @throws Exception when the provided id is not a string
     */
    public function setId($id) {
        if ($id === null) {
            $this->id = null;

            return;
        }

        if (!is_scalar($id) || (is_object($id) && !method_exists($id, '__toString'))) {
            throw new LogException('Could not set the log message id: invalid id provided');
        }

        $this->id = (string) $id;
    }

    /**
     * Gets the id of the request
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Sets the timestamp of this log item
     * @param integer $date Timestamp of this log item
     * @return null
     */
    public function setDate($date) {
        $this->date = $date;
    }

    /**
     * Gets the timestamp of this log item
     * @return integer
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * Sets the micro time in the request
     * @param double $microtime
     * @return null
     */
    public function setMicrotime($microtime) {
        $this->microtime = $microtime;
    }

    /**
     * Gets the microtime of this log in the request
     * @return double
     */
    public function getMicrotime() {
        return $this->microtime;
    }

    /**
     * Sets the title of this log item
     * @param mixed $title
     * @return null
     * @throws Exception when the provided title is not castable to string
     */
    public function setTitle($title) {
        if ($title === null || !is_scalar($title) || (is_object($title) && !method_exists($title, '__toString'))) {
            throw new LogException('Could not set the log message title: invalid title provided (' . gettype($title) . ')');
        }

        $this->title = (string) $title;
    }

    /**
     * Gets the title of this log item
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Sets the client
     * @param string $client Client of the request (IP address, cli, ...)
     * @return null
     */
    public function setClient($client) {
        $this->client = $client;
    }

    /**
     * Gets the client
     * @return string
     */
    public function getClient() {
        return $this->client;
    }

    /**
     * Adds a log message to this request
     * @param LogMessage $logMessage
     * @return null
     */
    public function addLogMessage(LogMessage $logMessage) {
        if (!$this->id) {
            $this->setId($logMessage->getId());
        }
        if (!$this->date) {
            $this->setDate($logMessage->getDate());
        }
        if (!$this->client) {
            $this->setClient($logMessage->getClient());
        }

        $microTime = $logMessage->getMicroTime();
        if (!$microTime || $microTime > $this->microtime) {
            $this->setMicroTime($microTime);
        }

        $this->messages[] = $logMessage;
    }

    /**
     * Gets the log messages of this request
     * @return array
     */
    public function getLogMessages() {
        return $this->messages;
    }

    /**
     * Gets log messages of this request filtered by source
     * @param string|array $sources
     * @return array
     */
    public function getLogMessagesBySource($sources) {
        if (!is_array($sources)) {
            $sources = array($sources);
        }

        $messages = array();
        foreach ($this->messages as $message) {
            foreach ($sources as $source) {
                if ($message->getSource() == $source) {
                    $messages[] = $message;

                    break;
                }
            }
        }

        return $messages;
    }

    /**
     * Gets log messages of this request filtered by query string
     * @param string|array $query
     * @return array
     */
    public function getLogMessagesByQuery($query) {
        if (!is_array($query)) {
            $query = array($query);
        }

        $messages = array();
        foreach ($this->messages as $message) {
            foreach ($query as $queryString) {
                if (strpos($message->getTitle(), $queryString) !== false || strpos($message->getDescription(), $queryString) !== false) {
                    $messages[] = $message;

                    break;
                }
            }
        }

        return $messages;
    }

}
