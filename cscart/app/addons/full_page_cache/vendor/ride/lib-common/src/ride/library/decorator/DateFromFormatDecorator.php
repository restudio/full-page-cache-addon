<?php

namespace ride\library\decorator;

/**
 * Decorate a human readable date into a UNIX timestamp
 */
class DateFromFormatDecorator extends DateFormatDecorator {

    /**
     * Flag to see if null should be returned for a invalid date
     * @var boolean
     */
    protected $invalidToNull;

    /**
     * Sets the flag to see if null should be returned for a invalid date
     * @param boolean $invalidToNull
     * @return null
     */
    public function setInvalidToNull($invalidToNull) {
        $this->invalidToNull = $invalidToNull;
    }

    /**
     * Decorates a formatted date into a UNIX timestamp
     * @param mixed $value
     * @return mixed A UNIX timestamp if a valid formatted date has been
     * provided, the original value otherwise
     */
    public function decorate($value) {
        if (is_numeric($value)) {
            return $value;
        }

        if ($this->dateFormat) {
            $dateFormat = $this->dateFormat;
        } else {
            $dateFormat = self::DEFAULT_DATE_FORMAT;
        }

        $date = date_create_from_format($dateFormat, $value);
        if ($date === false) {
            if ($this->invalidToNull) {
                return null;
            }

            return $value;
        }

        return $date->format('U');
    }

}
