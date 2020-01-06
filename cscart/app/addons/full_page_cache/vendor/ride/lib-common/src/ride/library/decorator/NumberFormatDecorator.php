<?php

namespace ride\library\decorator;

/**
 * Decorator which formats a number with grouped thousands
 */
class NumberFormatDecorator implements Decorator {


    /**
     * Decorate the number
     * @param integer or float
     * @return integer
     */
    public function decorate($value) {
        if (empty($value)) {
            return null;
        }

        $value = trim($value);
        $value = str_replace(',', '', $value);
        $value = str_replace('.', '', $value);
        $value = str_replace(' ', '', $value);
        $value = (int) $value;

        return $value;
    }

}
