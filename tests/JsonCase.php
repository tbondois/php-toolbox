<?php

namespace TB\Toolbox\Tests;

use PHPUnit\Framework\TestCase;
use TB\Toolbox\Json;

/**
 * @author    Thomas Bondois
 */
class JsonCase extends TestCase
{
    const SRC_CLASS = "TB\\Toolbox\\Json";

    protected static function method($name)
    {
        $class = new \ReflectionClass(static::SRC_CLASS);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }


} // end class
