<?php

namespace TB\Toolbox\Tests;

use UtilityPHPTest as ParentCase;
use TB\Toolbox\Util;

/**
 * @author    Thomas Bondois
 * @see vendor/brandonwamboldt/utilphp/tests/README.md
 */
class UtilCase extends ParentCase
{
    const SRC_CLASS = "TB\\Toolbox\\Util";

    protected static function method($name)
    {
        $class = new \ReflectionClass(static::SRC_CLASS);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function test_bool()
    {
        $values = [
            true,
            "a",
            2,
            "true",
            "0.2",
        ];
        foreach ($values as $value) {
            $this->assertTrue(Util::bool($value));
        }

        $values = Util::BOOL_CAST_CONSIDERED_AS_FALSE;
        $values[] = "0.0";
        $values[] = -0.1;
        $values[] = "-0.1";
        $values[] = -2.0;
        $values[] = "-2.0";
        foreach ($values as $value) {
            $this->assertFalse(Util::bool($value));
        }

    }




} // end class
