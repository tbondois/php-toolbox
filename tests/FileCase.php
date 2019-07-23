<?php

namespace TB\Toolbox\Tests;

use PHPUnit_Framework_TestCase;
use TB\Toolbox\File;

/**
 * @author    Thomas Bondois
 */
class FileCase extends TestCase
{
    const SRC_CLASS = "TB\\Toolbox\\File";

    protected static function method($name)
    {
        $class = new \ReflectionClass(static::SRC_CLASS);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }


} // end class
