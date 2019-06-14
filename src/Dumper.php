<?php

namespace TB;

use Kint\Kint;

/**
 * @author Thomas Bondois
 */
class Dumper
{

    /**
     * The output is in text html escaped text
     * with some minor visibility enhancements added.
     * If run in CLI mode, output is not escaped.
     *
     * @see s()
     * @param mixed ...$vars
     * @return int|mixed
     */
    public static function auto(...$vars)
    {
        if (!Kint::$enabled_mode) {
            return 0;
        }

        $stashedMode = Kint::$enabled_mode;

        if (Kint::MODE_TEXT !== Kint::$enabled_mode) {
            Kint::$enabled_mode = Kint::MODE_PLAIN;
            if (PHP_SAPI === 'cli' && true === Kint::$cli_detection) {
                Kint::$enabled_mode = Kint::$mode_default_cli;
            }
        }

        $args = \func_get_args();
        $out = \call_user_func_array(array('Kint', 'dump'), $args);

        Kint::$enabled_mode = $stashedMode;

        return $out;
    }
    /**
     * dump in rich text
     * @param mixed ...$vars
     * @return int|string
     */
    public static function text(...$vars)
    {
        $stashedMode = Kint::$enabled_mode;
        Kint::$enabled_mode = Kint::MODE_TEXT;
        $dump = Kint::dump(...$vars);
        Kint::$enabled_mode = $stashedMode;
        return $dump;
    }

    /**
     * dump in rich text
     * @param mixed ...$vars
     * @return int|string
     */
    public static function cli(...$vars)
    {
        $stashedMode = Kint::$enabled_mode;
        Kint::$enabled_mode = Kint::MODE_CLI;
        $dump = Kint::dump(...$vars);
        Kint::$enabled_mode = $stashedMode;
        return $dump;
    }

    /**
     * dump in rich text
     * @param mixed ...$vars
     * @return int|string
     */
    public static function html_rich(...$vars)
    {
        $stashedMode = Kint::$enabled_mode;
        Kint::$enabled_mode = Kint::MODE_RICH;
        $dump = Kint::dump(...$vars);
        Kint::$enabled_mode = $stashedMode;
        return $dump;
    }

    /**
     * dump in text text
     * @param mixed ...$vars
     * @return int|string
     */
    public static function html_plain(...$vars)
    {
        $stashedMode = Kint::$enabled_mode;
        Kint::$enabled_mode = Kint::MODE_PLAIN;
        $dump = Kint::dump(...$vars);
        Kint::$enabled_mode = $stashedMode;
        return $dump;
    }

    public static function disable()
    {
        Kint::$enabled_mode = false;
    }

    public static function enable()
    {
        Kint::$enabled_mode = true;
    }


} // end class
