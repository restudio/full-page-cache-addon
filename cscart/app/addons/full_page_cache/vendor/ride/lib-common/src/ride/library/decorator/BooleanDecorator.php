<?php

namespace ride\library\decorator;

/**
 * Decorator for a boolean value
 */
class BooleanDecorator implements Decorator {

    /**
     * Translation key for the default true value
     * @var string
     */
    const DEFAULT_TRUE = 'yes';

    /**
     * Translation key for the default false value
     * @var string
     */
    const DEFAULT_FALSE = 'no';

    /**
     * Label to use for true values
     * @var string
     */
    protected $labelTrue;

    /**
     * Label to use for false values
     * @var string
     */
    protected $labelFalse;

    /**
     * Sets the labels for true and false
     * @param string $labelTrue Label for true values
     * @param string $labelFalse Label for false values
     * @return null
     */
    public function setLabels($labelTrue = null, $labelFalse = null) {
        if ($labelTrue === null) {
            $labelTrue = self::DEFAULT_TRUE;
        }

        if ($labelFalse === null) {
            $labelFalse = self::DEFAULT_FALSE;
        }

        $this->labelTrue = $labelTrue;
        $this->labelFalse = $labelFalse;
    }

    /**
     * Performs the actual decorating on the provided value.
     * @param mixed $value Value to decorate
     * @return mixed Decorated value
     */
    public function decorate($value) {
        if ($value) {
            $value = $this->labelTrue;
        } else {
            $value = $this->labelFalse;
        }

        return $value;
    }

}
