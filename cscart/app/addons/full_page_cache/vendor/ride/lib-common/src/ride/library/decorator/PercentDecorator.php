<?php

namespace ride\library\decorator;

use ride\library\decorator\exception\DecoratorException;

/**
 * Decorator for a percent value
 */
class PercentDecorator implements Decorator {

    /**
     * Precision of the percent value
     * @var integer
     */
    protected $precision = 0;

    /**
     * Sets the precision
     * @param integer $precision Number of decimal digits to round the value
     * @throws \ride\library\decorator\exception\DecoratorException when the
     * precision is not a integer or smaller then 0
     */
    public function setPrecision($precision = 0) {
        if (!is_integer($precision) || $precision < 0) {
            throw new DecoratorException('Provided precision cannot be smaller then 0');
        }

        $this->precision = $precision;
    }

    /**
     * Decorators the provided value into a formatted percent value
     * @param mixed $value Value to decorate
     * @return string Original value if no numeric value provided, a percent
     * string otherwise
     */
    public function decorate($value) {
        if (!is_numeric($value)) {
            return $value;
        }

        $value = round($value, $this->precision);

        return $value . ' %';
    }

}