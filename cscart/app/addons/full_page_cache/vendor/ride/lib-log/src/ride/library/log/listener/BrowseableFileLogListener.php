<?php

namespace ride\library\log\listener;

use ride\library\decorator\LogMessageDecorator;
use ride\library\log\LogMessage;
use ride\library\log\LogSession;

/**
 * Browseable log listener to write log messages to file. In order to work
 * properly, the LogMessageDecorator should be used.
 */
class BrowseableFileLogListener extends FileLogListener implements BrowseableLogListener {

    /**
     * Gets a log session by id
     * @param string $id Id of the request
     * @return \ride\library\log\LogSession
     */
    public function getLogSession($id) {
        $logSessions = $this->getLogSessions(array('limit' => 9999));

        if (!isset($logSessions[$id])) {
            return null;
        }

        return $logSessions[$id];
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
        // parse the options
        $page = 1;
        $limit = 10;

        if ($options) {
            if (isset($options['page'])) {
                $page = $options['page'];
            }
            if (isset($options['limit'])) {
                $limit = $options['limit'];
            }
        }

        // fetch the sessions
        $logSessions = array();
        if ($this->useBackupFile) {
            $logSessions += $this->getLogSessionsFromFile($this->fileName . '.1');
        }
        $logSessions += $this->getLogSessionsFromFile($this->fileName);

        // apply pagination
        $pages = ceil(count($logSessions) / $limit);
        $logSessions = array_reverse($logSessions, true);
        $logSessions = array_slice($logSessions, ($page - 1) * $limit, $limit, true);

        // go back
        return $logSessions;
    }

    /**
     * Gets the log session from the provided file
     * @param string $fileName
     * @return array
     */
    protected function getLogSessionsFromFile($fileName) {
        $content = @file_get_contents($fileName);
        if (!$content) {
            return array();
        }

        return $this->parseLogSessionsFromContent($content);
    }

    /**
     * Parses the contents of the log file into log requests
     * @param string $content Content of the log file
     * @return array
     */
    protected function parseLogSessionsFromContent($content) {
        $logSessions = array();
        $lastLogMessage = null;

        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $logMessage = $this->parseLogLine($line);
            if (!$logMessage) {
                if ($lastLogMessage) {
                    $lastLogMessage->setDescription($lastLogMessage->getDescription() . "\n" . $line);
                }

                continue;
            } elseif (!isset($logSessions[$logMessage->getId()])) {
                $logSessions[$logMessage->getId()] = new LogSession();
            }

            $logSessions[$logMessage->getId()]->addLogMessage($logMessage);

            $lastLogMessage = $logMessage;
        }

        return $logSessions;
    }

    /**
     * Parses a log line into a LogMessage
     * @param string $content Content of the log file
     * @return \ride\library\log\LogMessage
     */
    protected function parseLogLine($line) {
        $tokens = explode(LogMessageDecorator::FIELD_SEPARATOR, $line);
        if (count($tokens) < 6) {
            return false;
        }

        $level = $tokens[6];
        switch ($level) {
            case 'D':
                $level = LogMessage::LEVEL_DEBUG;

                break;
            case 'E':
                $level = LogMessage::LEVEL_ERROR;

                break;
            case 'I':
                $level = LogMessage::LEVEL_INFORMATION;

                break;
            case 'W':
                $level = LogMessage::LEVEL_WARNING;

                break;
            default:
                return false;
        }

        $message = new LogMessage($level, $tokens[7], isset($tokens[8]) ? $tokens[8] : null, trim($tokens[4]));
        $message->setId($tokens[0]);
        $message->setDate($tokens[1]);
        $message->setMicrotime($tokens[2]);
        $message->setClient($tokens[3]);

        return $message;
    }

}
