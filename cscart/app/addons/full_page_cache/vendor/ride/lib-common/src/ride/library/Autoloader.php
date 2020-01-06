<?php

namespace ride\library;

use \Exception;

/**
 * Generic autoloader according to PSR.
 *
 * <p>Loads classes with the following types of naming:</p>
 * <ul>
 * <li>ride\library\Autoloader in <path>/ride/core/Autloader.php
 * <li>ride_library_Autoloader in <path>/ride/library/Autoloader.php
 * <li>ride_library_Autoloader in <path>/ride_library_Autoloader.php
 * </ul>
 */
class Autoloader {

    /**
     * Source include paths
     * @var array
     */
    protected $includePaths = array();

    /**
     * Adds a source directory where classes could be found.
     * @param string $path Path of a source directory
     * @param boolean $prepend Flag to see if this path should be prepended to
     * the include paths or not
     * @return null
     * @throws \Exception when the provided path is a invalid value
     */
    public function addIncludePath($path, $prepend = true) {
        if (!is_string($path) || !$path) {
            throw new Exception('Could not add include path: provided path is not a string or is empty');
        }

        $path = rtrim($path, '/');

        if ($prepend) {
            array_unshift($this->includePaths, $path);
        } else {
            array_pop($this->includePaths, $path);
        }
    }

    /**
     * Adds multiple source directories at once.
     * @param array $paths Paths to add, when null is& provided, the PHP include
     * paths will be added
     * @param boolean $prepend Flag to see if this path should be prepended to
     * the include paths or not
     * @return null
     */
    public function addIncludePaths(array $paths = null, $prepend = true) {
        if ($paths === null) {
            $paths = explode(PATH_SEPARATOR, get_include_path());

            if ($prepend) {
                // maintain path order when prepending
                $paths = array_reverse($paths);
            }
        }

        foreach ($paths as $path) {
            $this->addIncludePath($path, $prepend);
        }
    }

    /**
     * Gets the include paths
     * @return array
     */
    public function getIncludePaths() {
        return $this->includePaths;
    }

    /**
     * Removes a source directory from the include paths
     * @param string $path Path of a source directory
     * @return null
     */
    public function removeIncludePath($path) {
        foreach ($this->includePaths as $index => $includePath) {
            if ($includePath == $path) {
                unset($this->includePaths[$index]);
            }
        }
    }

    /**
     * Autoloads the provided class
     * @param string $class Full class name with namespace
     * @return boolean True if succeeded, false otherwise
     */
    public function autoload($className) {
        $classFile = $className . '.php';
        $namespacedClassFile = str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $classFile);

        foreach ($this->includePaths as $includePath) {
            if ($this->autoloadFile($includePath . DIRECTORY_SEPARATOR . $namespacedClassFile)) {
                return true;
            }

            if (strpos($classFile, '\\') === false) {
                if ($this->autoloadFile($includePath . DIRECTORY_SEPARATOR . $classFile)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Tries to include the provided source file
     * @param string $fileName File name of a PHP source script
     * @return boolean True if the file is included, false otherwise
     */
    protected function autoloadFile($fileName) {
        if (!$fileName || !is_readable($fileName)) {
            return false;
        }

        include_once($fileName);

        return true;
    }

    /**
     * Registers this autoload implementation to PHP
     * @return null
     * @throws \Exception when the autoloader could not be registered
     */
    public function registerAutoloader($prepend = false) {
        if (!spl_autoload_register(array($this, 'autoload'), false, $prepend)) {
            throw new Exception('Could not register this autoloader');
        }
    }

    /**
     * Unegisters this autoload implementation from PHP
     * @return null
     * @throws \Exception when the autoloader could not be unregistered
     */
    public function unregisterAutoloader() {
        if (!spl_autoload_unregister(array($this, 'autoload'))) {
            throw new Exception('Could not unregister this autoloader');
        }
    }

}
