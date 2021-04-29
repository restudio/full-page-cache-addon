<?php

namespace ride\library\decorator;

/**
 * Decorator which provides a default value for empty fields.
 */
class DefaultDecorator implements Decorator {

    /**
     * @var string
     */
    protected $defaultValue;

    public function __construct($defaultValue) {
        $this->setDefaultValue($defaultValue);
    }

    /**
     * @return string
     */
    public function getDefaultValue() {
        return $this->defaultValue;
    }

    /**
     * @param string $defaultValue
     */
    public function setDefaultValue($defaultValue) {
        $this->defaultValue = $defaultValue;
    }

    /**
     * Replaces an empty value with a default value
     * @param mixed $values Values to decorate
     * @return String The value if it is set, otherwise the default value,
     */
    public function decorate($value) {
        if (empty($value)) {
            return $this->getDefaultValue();
        }

        return $value;
    }

}
