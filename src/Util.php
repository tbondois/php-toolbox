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
     * @param int $limit
     * @return array
     */
    public static function formatted_backtraces(int $limit = 1)
    {
        $backtraces = [];
        for ($index = 1; $index <= $limit; $index++) {
            $backtrace = static::formatted_backtrace($index);
            if ($backtrace) {
                $backtraces = $backtrace;
            }
        }
        return $backtraces;
    }

    /**
     * @param int $index
     * @return string|null
     */
    public static function formatted_backtrace(int $index = 1)
    {
        $dbt = debug_backtrace();
        if (isset($dbt[$index])) {
            return sprintf("%s::%s():%s"
                , $backtrace['class'] ?? $backtrace['file'] ?? '-'
                , $backtrace['function'] ?? '-'
                , $backtrace['line'] ?? '-'
            );
        }
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
            || (class_exists("ext-simplexml") && $var instanceof \SimpleXmlElement)
        ;
    }


    /**
     * ie : \TB\Toolbox::php72()::utf8_encode("xxx");
     * @return Php72
     */
    public static function php72()
    {
        return new Php72();
    }

    /**
     * @return Php73
     */
    public static function php73()
    {
        return new Php73();
    }


    /**
     * @param mixed ...$vars
     * @return string
     */
    public static function dump(...$vars)
    {
        foreach ($vars as $var) {
            Util::var_dump($var, false, 1);
        }
    }


} // end class
