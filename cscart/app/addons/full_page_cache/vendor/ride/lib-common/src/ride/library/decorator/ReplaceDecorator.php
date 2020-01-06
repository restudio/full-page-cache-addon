<?php

namespace ride\library\decorator;

/**
 * Decorator to search and replace a static value in a string
 */
class ReplaceDecorator implements Decorator {

    /**
     * Search string or array of strings
     * @var string|array
     */
    protected $search;

    /**
     * Replace string
     * @var string
     */
    protected $replace;

    /**
     * Constructs a new replace decorator
     * @param string|array $search Search string or array of strings
     * @param string $replace Replace string
     * @return null
     */
    public function __construct($search, $replace) {
        $this->setSearch($search);
        $this->setReplace($replace);
    }

    /**
     * Sets the search string/array
     * @param string|array $search Search string or array of strings
     * @return null
     */
    public function setSearch($search) {
        $this->search = $search;
    }

    /**
     * Gets the search string/array
     * @return string|array Search string or array of strings
     */
    public function getSearch() {
        return $this->search;
    }

    /**
     * Sets the replace string
     * @param string $replace
     * @return null
     */
    public function setReplace($replace) {
        $this->replace = $replace;
    }

    /**
     * Gets the replace string
     * @return string
     */
    public function getReplace() {
        return $this->replace;
    }

    /**
     * Performs a replace if string provided
     * @param mixed $value
     * @return mixed Provided value with the search replaced if applicable
     */
    public function decorate($value) {
        if (is_string($value)) {
            $value = str_replace($this->search, $this->replace, $value);
        }

        return $value;
    }

}
