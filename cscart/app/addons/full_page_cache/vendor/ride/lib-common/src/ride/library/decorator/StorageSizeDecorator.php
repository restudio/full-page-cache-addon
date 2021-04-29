<?php

namespace ride\library\decorator;

/**
 * Decorate a value into a human readable file/memory size
 */
class StorageSizeDecorator implements Decorator {

    /**
     * Size units in ascending order
     * @var array
     */
    private $sizeUnits = array(
        ' bytes',
        ' Kb',
        ' Mb',
        ' Gb',
        ' Tb',
        ' Pb',
        ' Eb',
        ' Zb',
        ' Yb',
    );

    /**
     * Decorate a filesize in bytes into a human readable size
     * @param mixed $value
     * @return mixed A human readable filesize if a valid value has been
     * provided, the original value otherwise
     */
    public function decorate($size) {
        if (!is_numeric($size) || $size < 0) {
            return $size;
        }

        if ($size == 0) {
            return '0 bytes';
        }

        $i = floor(log($size, 1024));

        return round($size / pow(1024, $i), 2) . $this->sizeUnits[$i];
    }

}