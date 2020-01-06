<?php

namespace ride\library\decorator;

/**
 * Interface to decorate/format a value for another context
 */
interface Decorator {

    /**
     * Decorate a value for another context
     * @param mixed $value Value to decorate
     * @return mixed Decorated value if applicable, provided value otherwise
     */
    public function decorate($value);

}