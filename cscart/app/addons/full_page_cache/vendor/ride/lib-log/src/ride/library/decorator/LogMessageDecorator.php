<?php

namespace ride\library\decorator;

use ride\library\log\LogMessage;

/**
 * Decorator for log messages into output format
 */
class LogMessageDecorator implements Decorator {

    /**
     * Separator between the fields
     * @var string
     */
    const FIELD_SEPARATOR = ' ~ ';

    /**
     * Array with the level translated in human readable form
     * @var array
     */
    protected $levels = array(
        LogMessage::LEVEL_ERROR => 'E',
        LogMessage::LEVEL_WARNING => 'W',
        LogMessage::LEVEL_INFORMATION => 'I',
        LogMessage::LEVEL_DEBUG => 'D',
    );

    /**
     * Decorator for the date value
     * @var \ride\library\decorator\Decorator
     */
    protected $dateDecorator;

    /**
     * Decorator for the memory value
     * @var \ride\library\decorator\Decorator
     */
    protected $memoryDecorator;

    /**
     * Sets the decorator for the date value
     * @param \ride\library\decorator\Decorator $dateDecorator
     * @return null
     */
    public function setDateDecorator(Decorator $dateDecorator) {
        $this->dateDecorator = $dateDecorator;
    }

    /**
     * Gets the decorator for the date value
     * @return \ride\library\decorator\Decorator
     */
    public function getDateDecorator() {
        return $this->dateDecorator;
    }

    /**
     * Sets the decorator for the memory value
     * @param \ride\library\decorator\Decorator $memoryDecorator
     * @return null
     */
    public function setMemoryDecorator(Decorator $memoryDecorator) {
        $this->memoryDecorator = $memoryDecorator;
    }

    /**
     * Gets the decorator for the memory value
     * @return \ride\library\decorator\Decorator
     */
    public function getMemoryDecorator() {
        return $this->memoryDecorator;
    }

    /**
     * Decorate a value for another context
     * @param mixed $value Value to decorate
     * @return mixed Decorated value if applicable, provided value otherwise
     */
    public function decorate($value) {
        if (!$value instanceof LogMessage) {
            return $value;
        }

        $date = $value->getDate();
        if ($this->dateDecorator) {
            $date = $this->dateDecorator->decorate($date);
        }

        $memory = memory_get_usage();
        if ($this->memoryDecorator) {
            $memory = $this->memoryDecorator->decorate($memory);
        }

        $output = $value->getId();
        $output .= self::FIELD_SEPARATOR . $date;
        $output .= self::FIELD_SEPARATOR . substr($value->getMicroTime(), 0, 5);
        $output .= self::FIELD_SEPARATOR . $value->getClient();
        $output .= self::FIELD_SEPARATOR . str_pad($value->getSource(), 8);
        $output .= self::FIELD_SEPARATOR . str_pad($memory, 9, ' ', STR_PAD_LEFT);
        $output .= self::FIELD_SEPARATOR . $this->levels[$value->getLevel()];
        $output .= self::FIELD_SEPARATOR . $value->getTitle();

        $description = $value->getDescription();
        if (!empty($description)) {
            $output .= self::FIELD_SEPARATOR . $description;
        }

        return $output . "\n";
    }

}
