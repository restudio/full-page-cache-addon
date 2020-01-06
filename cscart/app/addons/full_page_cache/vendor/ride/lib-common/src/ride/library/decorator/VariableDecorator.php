<?php

namespace ride\library\decorator;

/**
 * Decorate a PHP variable
 */
class VariableDecorator implements Decorator {

    /**
     * Decorate a value for another context
     * @param mixed $value Value to decorate
     * @return mixed Decorated value if applicable, provided value otherwise
     */
    public function decorate($value) {
        if (is_null($value)) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_numeric($value)) {
            return $value;
        }

        if (is_string($value)) {
            return '"' . str_replace('"', '\\"', $value) . '"';
        }

        if (is_object($value)) {
            return get_class($value);
        }

        if (is_array($value)) {
            $items = array();

            foreach ($value as $key => $item) {
                $items[] = $key . ' => ' . $this->decorate($item);
            }

            return '[' . implode(', ', $items) . ']';
        }

        return '#resource';
    }

}