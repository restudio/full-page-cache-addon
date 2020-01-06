<?php

namespace statik\leucul\import\decorators;

use ride\library\decorator\Decorator;

/**
 * Decorator for a value which should be split up into multiple fields.
 */
class MultiFieldDecorator implements Decorator {

    /**
     * @var string
     */
    protected $seperator;

    /**
     * @var int
     */
    protected $index;

    /**
     * @param String $separator
     * @param int $index
     */
    public function __construct($separator, $index = 0) {
        $this->setSeperator($separator);
        $this->setIndex($index);
    }

    /**
     * @return string
     */
    public function getSeperator() {
        return $this->seperator;
    }

    /**
     * @param string $seperator
     */
    public function setSeperator($seperator) {
        $this->seperator = $seperator;
    }

    /**
     * @return int
     */
    public function getIndex() {
        return $this->index;
    }

    /**
     * @param int $index
     */
    public function setIndex($index) {
        $this->index = $index;
    }

    /**
     * This decorator splits the given value using the provided seperator.
     * The value obtained by using the provided index will be returned.
     * @param mixed $value Value to decorate
     * @return mixed Decorated value
     */
    public function decorate($value) {
        if (empty($value)) {
            return '';
        }
        else {
            $values = explode($this->getSeperator(), $value);
            if (count($values) <= $this->getIndex()) {
                return trim($values[0]);
            }

            return trim($values[$this->getIndex()]);
        }
    }

}
