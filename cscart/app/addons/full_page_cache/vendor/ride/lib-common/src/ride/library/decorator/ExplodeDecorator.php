<?php

namespace ride\library\decorator;

use ride\library\decorator\exception\DecoratorException;

/**
 * Decorator for to explode a value into an array
 */
class ExplodeDecorator implements Decorator {

    /**
     * Glue between the values
     * @var string
     */
    protected $glue;

    /**
     * Constructs a new explode decorator
     * @param string $glue Glue between the values
     * @return null
     */
    public function __construct($glue = ',') {
        $this->setGlue($glue);
    }

    /**
     * Sets the glue
     * @param string $glue Glue between the values
     * @return null
     */
    public function setGlue($glue) {
        $this->glue = $glue;
    }

    /**
     * Gets the glue
     * @return string
     */
    public function getGlue() {
        return $this->glue;
    }

    /**
     * Decorators the provided value into a an array
     * @param mixed $value Value to decorate
     * @return string Original value if no string value provided, an array
     * otherwise
     */
    public function decorate($value) {
        if (!is_scalar($value)) {
            return $value;
        }

        if ($value === '') {
            return array();
        }

        return explode($this->glue, $value);
    }

}
