<?php

namespace ride\library\log\listener;

/**
 * Interface for a log listener which can be browsed
 */
interface BrowseableLogListener extends LogListener {

    /**
     * Gets a log session by id
     * @param string $id Id of the log session
     * @return \ride\library\log\LogSession
     */
    public function getLogSession($id);

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
    public function getLogSessions(array $options = null, &$pages = null);

}
