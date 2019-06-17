<?php

namespace TB\Toolbox;

use utilphp\util as Base;
use Symfony\Polyfill\Php72\Php72;
use Symfony\Polyfill\Php73\Php73;

/**
 * @author Thomas Bondois
 */
class Util extends Base
{

    /**
     * @param int $size
     * @return string
     */
    public static function human_readable_octets($size)
    {
        $unit = ['o','Ko','Mo','Go','To'];
        $size = (int)$size;
        return round($size/pow(1024,($i=floor(log($size,1024)))),2).$unit[$i];
    }

    /**
     * @param int $size
     * @return string
     */
    public static function human_readable_bytes($size)
    {
        $unit = ['B','KB','MB','GB','TB'];
        $size = (int)$size;
        return round($size/pow(1024,($i=floor(log($size,1024)))),2).$unit[$i];
    }


    /**
     * @param int $index
     * @return string|null
     */
    public static function formatted_backtrace(int $index = 1)
    {
        $dbt = debug_backtrace();
        if (isset($dbt[$index])) {
            $backtrace = $dbt[$index];
        } else {
            $backtrace = end($dbt);
        }
        return sprintf("%s:%s"
            , $backtrace['file']  ?? '-'
            , $backtrace['line'] ?? '-'
        );

        return null;
    }

    /**
     * @param array $array
     * @return bool
     */
    public function is_sequential(array $array)
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * Polyfill for native function introduced in PHP 7.1
     * @param $var
     * @return bool
     */
    public static function is_iterable($var)
    {
        return is_array($var)
            || $var instanceof \Traversable
            || $var instanceof \iterable
        ;
    }

    /**
     * Polyfill for native function introduced in PHP 7.3
     * @see https://www.php.net/manual/en/function.is-countable.php
     * @see https://wiki.php.net/rfc/is-countable
     * @param mixed $var
     * @return bool
     */
    public static function is_countable($var) {
        return is_array($var)
            || $var instanceof \Countable
            || (class_exists("ResourceBundle") && $var instanceof \ResourceBundle)
            || (class_exists("SimpleXmlElement") && $var instanceof \SimpleXmlElement)
        ;
    }

    /**
     * @param mixed ...$vars
     * @return string
     */
    public static function dump(...$vars)
    {
        if (function_exists("dump")) {
            dump(...$vars);
        } else {
            foreach ($vars as $var) {
                Util::var_dump($var, false, 1);
            }
        }
    }

    /**
     * dump and stop script
     * @param mixed ...$vars
     */
    public static function die_dump(...$vars)
    {
        static::dump(...$vars);
        die(PHP_EOL."/die in ".static::formatted_backtrace().PHP_EOL);
    }

} // end class
