<?php

namespace ride\library\decorator;

use ride\library\decorator\exception\DecoratorException;

use \DateTime;

/**
 * Decorate a UNIX timestamp into a human readable date
 */
class DateFormatDecorator implements Decorator {

    /**
     * Default date format
     * @var string
     */
    const DEFAULT_DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * Date format to apply
     * @var string
     */
    protected $dateFormat;

    /**
     * Sets the date format used to write the timestamp of the log item
     * @param string $dateFormat
     * @return null
     */
    public function setDateFormat($dateFormat) {
        if (!is_string($dateFormat) || $dateFormat == '') {
            throw new DecoratorException('Could not set the date format: provided is not a string or empty');
        }

        $this->dateFormat = $dateFormat;
    }

    /**
     * Gets the date format used to write the timestamp of the log item
     * @return string date format
     */
    public function getDateFormat() {
        return $this->dateFormat;
    }

    /**
     * Decorate a UNIX timestamp into a human readable date
     * @param mixed $value
     * @return mixed A human readable date if a valid value has been provided,
     * the original value otherwise
     */
    public function decorate($value) {
        if (!is_numeric($value) || $value < 0) {
	    	if ($value instanceof DateTime) {
				return $value->format($this->dateFormat);
	    	}

            return $value;
        }

        if ($this->dateFormat) {
            $dateFormat = $this->dateFormat;
        } else {
            $dateFormat = self::DEFAULT_DATE_FORMAT;
        }

        return date($dateFormat, $value);
    }

}