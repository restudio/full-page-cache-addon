<?php

namespace ride\library\log;

use ride\library\log\exception\LogException;

/**
 * Data container of a log item
 */
class LogMessage {

    /**
     * Error level
     * @var integer
     */
    const LEVEL_ERROR = 1;

    /**
     * Warning level
     * @var integer
     */
    const LEVEL_WARNING = 2;

    /**
     * Information level
     * @var integer
     */
    const LEVEL_INFORMATION = 4;

    /**
     * Debug level
     * @var integer
     */
    const LEVEL_DEBUG = 8;

    /**
     * Id of the log session
     * @var string
     */
    protected $id;

    /**
     * Level of this message
     * @var integer
     */
    protected $level;

    /**
     * Title of the log message
     * @var string
     */
    protected $title;

    /**
     * Full description of the log message
     * @var string
     */
    protected $description;

    /**
     * Timestamp when this message is added
     * @var integer
     */
    protected $date;

    /**
     * Microtime in the request
     * @var integer
     */
    protected $microtime;

    /**
     * Source of this message
     * @var string
     */
    protected $source;

    /**
     * Client of the request (IP address, cli, ...)
     * @var string
     */
    protected $client;

    /**
     * Constructs a new log message
     * @param integer $level
     * @param mixed $title
     * @param string $description
     * @param string $source
     * @return null
     */
    public function __construct($level, $title, $description = null, $source = null) {
        $this->setLevel($level);
        $this->setTitle($title);
        $this->setDescription($description);
        $this->setSource($source);

        $this->id = null;
        $this->date = time();
        $this->microtime = null;
        $this->client = null;
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
     * Sets the type of this log item
     * @param integer $type
     * @return null
     * @throws Exception when an invalid type has been provided
     */
    public function setLevel($level) {
        switch ($level) {
            case self::LEVEL_DEBUG:
            case self::LEVEL_ERROR:
            case self::LEVEL_INFORMATION:
            case self::LEVEL_WARNING:
                $this->level = $level;

                break;
            default:
                throw new LogException('Coult not set the log message level: provided type is invalid. Try ' . self::LEVEL_ERROR . ' for a error, ' . self::LEVEL_WARNING . ' for a warning, ' . self::LEVEL_INFORMATION . ' for a information message and ' . self::LEVEL_DEBUG . ' for a debug message.');
        }
    }

    /**
     * Gets the level of this log item
     * @return integer
     */
    public function getLevel() {
        return $this->level;
    }

    /**
     * Gets whether this item is a error item
     * @return boolean
     */
    public function isError() {
        return $this->level == self::LEVEL_ERROR;
    }

    /**
     * Gets whether this item is a warning item
     * @return boolean
     */
    public function isWarning() {
        return $this->level == self::LEVEL_WARNING;
    }

    /**
     * Gets whether this item is a information item
     * @return boolean
     */
    public function isInformation() {
        return $this->level == self::LEVEL_INFORMATION;
    }

    /**
     * Gets whether this item is a debug item
     * @return boolean
     */
    public function isDebug() {
        return $this->level == self::LEVEL_DEBUG;
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
     * Sets the description of this log item
     * @param string $description
     * @return null
     */
    public function setDescription($description) {
        if ($description === null) {
            $this->description = null;

            return;
        }

        if (is_object($description) && method_exists($description, '__toString')) {
            $description = (string) $description;
        }

        if (!is_scalar($description) || is_array($description)) {
               throw new LogException('Could not set the log message description: invalid description provided (' . gettype($description) . ')');
        }

        $this->description = (string) $description;
    }

    /**
     * Gets the description of this log item
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Sets the source of this log item
     * @param string $source
     * @return null
     */
    public function setSource($source) {
        $this->source = $source;
    }

    /**
     * Gets the source of this log item
     * @return string
     */
    public function getSource() {
        return $this->source;
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

}
